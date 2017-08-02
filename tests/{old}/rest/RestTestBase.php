<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


abstract class RestTestBase extends Sugar_PHPUnit_Framework_TestCase
{
    protected $authToken;
    protected $refreshToken;
    protected $_user;
    protected $consumerId = "sugar";
    protected $version = '10';
    protected $_platform = 'base';
    protected $accounts = array();
    protected $contacts = array();
    protected $opps = array();
    protected $cases = array();
    protected $bugs = array();
    protected $notes = array();

    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        // call a commit for transactional dbs
        $GLOBALS['db']->commit();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('files');
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE id LIKE 'UNIT%'");
        $GLOBALS['db']->query("DELETE FROM oauth_tokens WHERE consumer LIKE 'UNIT%'");
        $GLOBALS['db']->commit();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    protected function _cleanUpRecords()
    {
        // Cleaning up after ourselves, but only if there is cleanup to do
        // Accounts clean up
        if (count($this->accounts)) {
            $accountIds = array();
            foreach ($this->accounts as $account) {
                $accountIds[] = $account->id;
            }
            $accountIds = "('".implode("','",$accountIds)."')";
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c IN {$accountIds}");
            }
        }

        // Opportunities clean up
        if (count($this->opps)) {
            $oppIds = array();
            foreach ($this->opps as $opp) {
                $oppIds[] = $opp->id;
            }
            $oppIds = "('".implode("','",$oppIds)."')";
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
            $GLOBALS['db']->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN {$oppIds}");
            if ($GLOBALS['db']->tableExists('opportunities_cstm')) {
                $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id_c IN {$oppIds}");
            }
        }

        // Contacts cleanup
        if (count($this->contacts)) {
            $contactIds = array();
            foreach ($this->contacts as $contact) {
                $contactIds[] = $contact->id;
            }
            $contactIds = "('".implode("','",$contactIds)."')";

            $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN {$contactIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id IN {$contactIds}");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c IN {$contactIds}");
            }
        }

        // Cases cleanup
        if (count($this->cases)) {
            $caseIds = array();
            foreach ($this->cases as $aCase) {
                $caseIds[] = $aCase->id;
            }
            $caseIds = "('".implode("','",$caseIds)."')";

            $GLOBALS['db']->query("DELETE FROM cases WHERE id IN {$caseIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_cases WHERE case_id IN {$caseIds}");
            if ($GLOBALS['db']->tableExists('cases_cstm')) {
                $GLOBALS['db']->query("DELETE FROM cases_cstm WHERE id_c IN {$caseIds}");
            }
        }

        // Bugs cleanup
        if (count($this->bugs)) {
            $bugIds = array();
            foreach ($this->bugs AS $bug) {
                $bugIds[] = $bug->id;
            }
            $bugIds = "('" . implode( "','", $bugIds) . "')";
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id IN {$bugIds}");
            if ($GLOBALS['db']->tableExists('bugs_cstm')) {
                $GLOBALS['db']->query("DELETE FROM bugs_cstm WHERE id_c IN {$bugIds}");
            }
        }

        // Notes cleanup
        if (count($this->notes)) {
            $noteIds = array();
            foreach ($this->notes as $note) {
                $noteIds[] = $note->id;
            }
            $noteIds = "('".implode("','",$noteIds)."')";

            $GLOBALS['db']->query("DELETE FROM notes WHERE id IN {$noteIds}");
            if ($GLOBALS['db']->tableExists('notes_cstm')) {
                $GLOBALS['db']->query("DELETE FROM notes_cstm WHERE id_c IN {$noteIds}");
            }
        }
    }

    protected function _restLogin($username = '', $password = '', $platform = 'base')
    {
        if ( empty($username) && empty($password) ) {
            $username = $this->_user->user_name;
            // Let's assume test users have a password the same as their username
            $password = $this->_user->user_name;
        }

        // Save the platform for reauth
        $this->_platform = $platform;

        $args = array(
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
            'client_id' => $this->consumerId,
            'client_secret' => '',
            'platform' => $platform,
        );

        // Prevent an infinite loop, put a fake authtoken in here.
        $this->authToken = 'LOGGING_IN';

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        if ( empty($reply['reply']['access_token']) ) {
            self::fail("Rest authentication failed, message looked like: ".$reply['replyRaw']);
        }
        $this->authToken = $reply['reply']['access_token'];
        $this->refreshToken = $reply['reply']['refresh_token'];
    }

    protected function _restReauth()
    {
        if ($this->refreshToken) {
            $args = array(
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->consumerId,
                'client_secret' => '',
                'platform' => $this->_platform,
            );

            // Prevents _restCall from automatically logging in
            $this->authToken = 'LOGGING_IN';
            $reply = $this->_restCall('oauth2/token',json_encode($args));
            if (empty($reply['reply']['access_token'])) {
                self::fail("Rest re-authentication failed, message looked like: ".$reply['replyRaw']);
            }
            $this->authToken = $reply['reply']['access_token'];
            $this->refreshToken = $reply['reply']['refresh_token'];
        } else {
            self::fail("Attempt to reauth without a refresh token");
        }
    }

    protected function _restCall($urlPart,$postBody='',$httpAction='', $addedOpts = array(), $addedHeaders = array())
    {
        // Hold state in case we need to reauth
        $funcArgs = array(
            $urlPart,
            $postBody,
            $httpAction,
            $addedOpts,
            $addedHeaders
        );

        // Since this is going in to a new DB connection, we have to commit anything we have
        // lying around in an open transaction.
        $GLOBALS['db']->commit();

        $urlBase = $GLOBALS['sugar_config']['site_url'].'/api/rest.php/v' . $this->version . '/';

        if ( empty($this->authToken) ) {
            $this->_restLogin();
        }

        $ch = curl_init($urlBase.$urlPart);
        if (!empty($postBody)) {
            if (empty($httpAction)) {
                $httpAction = 'POST';
                curl_setopt($ch, CURLOPT_POST, 1); // This sets the POST array
                $requestMethodSet = true;
            }
            // For Mothership, uploads need special hack because of stream support
            if (is_array($postBody)) {
                foreach ($postBody as $k => $v) {
                    // Since there are some tests with empty values in POST, let's
                    // make sure there is a value to get the 1st char of
                    if (!empty($v) && $v[0] == '@') {
                        $name = substr($v, 1);
                        $postBody[$k] = "@".UploadFile::realpath($name);
                    }
                }
            } else {
                // see https://bugs.php.net/bug.php?id=69982
                $hasContentType = false;
                foreach ($addedHeaders as $header) {
                    if (stripos($header, 'Content-Type') !== false) {
                        $hasContentType = true;
                        break;
                    }
                }
                if (!$hasContentType) {
                    $addedHeaders[] = 'Content-Type: application/json';
                }
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
        } else {
            if (empty($httpAction)) {
                $httpAction = 'GET';
            }
        }

        if ( !empty($this->authToken) && $this->authToken != 'LOGGING_IN' ) {
            $addedHeaders[] = 'OAuth-Token: ' . $this->authToken;
        }

        // Only set a custom request for not POST with a body
        // This affects the server and how it sets its superglobals
        if (empty($requestMethodSet)) {
            if ($httpAction == 'PUT' && empty($postBody) ) {
                curl_setopt($ch, CURLOPT_PUT, 1);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpAction);
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $addedHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


        if (is_array($addedOpts) && !empty($addedOpts)) {
            // I know curl_setopt_array() exists, just wasn't sure if it was hurting stuff
            foreach ($addedOpts as $opt => $val) {
                curl_setopt($ch, $opt, $val);
            }
        }

        $httpReply = curl_exec($ch);
        $httpInfo = curl_getinfo($ch);

        // Handle reauth if need be.
        if (isset($httpInfo['http_code']) && $httpInfo['http_code'] == 401) {
            $this->_restReauth();
        }

        $httpError = $httpReply === false ? curl_error($ch) : null;
        $GLOBALS['db']->commit();

        // Handle the headers from the reply
        $headerLen = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpHeaders = substr($httpReply, 0, $headerLen);
        $httpHeaders = $this->_parseHeaderString($httpHeaders);

        // Get just the body for parsing the reply
        $httpReply = substr($httpReply, $headerLen);

        return array('info' => $httpInfo, 'reply' => json_decode($httpReply,true), 'replyRaw' => $httpReply, 'error' => $httpError, 'headers' => $httpHeaders);
    }

    /**
     * Use for FileApi call tests using a PUT method. This varies enough from
     * the _restCall method to warrant it's own setup. It is also added to the
     * base class because more than one unit test is using it now.
     *
     * @param  string $urlPart           The endpoint to hit in the api
     * @param  array  $args              Arguments to pass to this call (filename and type)
     * @param  bool   $passInQueryString Whether to add the filename to the querystring
     * @return array
     */
    protected function _restCallFilePut($urlPart, $args, $passInQueryString = true)
    {
        // Set this to capture our own errors, which is needed in case of non-200
        // response codes from the file_get_contents call
        PHPUnit_Framework_Error_Warning::$enabled = FALSE;

        // Auth check early to prevent work when not needed
        if ( empty($this->authToken) ) {
            $this->_restLogin();
        }

        $urlBase = $GLOBALS['sugar_config']['site_url'].'/api/rest.php/v' . $this->version . '/';
        $filename = basename($args['filename']);
        $url = $urlBase . $urlPart;
        if ($passInQueryString) {
            $conn = strpos('?', $url) === false ? '?' : '&';
            $url .= $conn . 'filename=' . urlencode($filename);
        }

        $filedata = file_get_contents($args['filename']);

        $auth = "oauth_token: $this->authToken\r\n";
        $options = array(
            'http' => array(
                'method' => 'PUT',
                'header' => "{$auth}Content-Type: $args[type]\r\nfilename: $filename\r\n",
                'content' => $filedata,
            ),
        );

        $context = stream_context_create($options);

        // Because non-200 HTTP responses causes PHP warnings and because PHPUnit
        // throws exceptions for those warnings, we use both error suppression
        // and turning off PHPUnit error warnings to allow the script to continue
        // to run when encountering a "error".
        $response = @file_get_contents($url, false, $context);
        if (empty($response) && !empty($http_response_header)) {
            // There was a response that was NOT a 200. These are mapped to API
            // exception codes where possible
            $responses = array(
                400 => array('label' => 'unknown_exception', 'description' => "An unknown exception happened."),
                401 => array('label' => 'need_login', 'description' => "The user needs to be logged in to perform this action"),
                403 => array('label' => 'not_authorized', 'description' => "This action is not authorized for the current user."),
                404 => array('label' => 'no_method_or_not_found', 'description' => "Could not find a method or handler for this path."),
                412 => array('label' => 'missing_or_invalid_parameter', 'description' => "A required parameter for this request is missing or invalid."),
                413 => array('label' => 'request_too_large', 'description' => "The request is too large to process."),
                500 => array('label' => 'fatal_error', 'description' => "A fatal error happened."),
            );

            // Set a reasonable default response code
            $code = 400;

            // See if we can get the actual HTTP response code
            foreach ($http_response_header as $header) {
                if (substr($header, 0, 5) == 'HTTP/') {
                    preg_match('#HTTP/\d\.\d\s+(\d+)\s+.*#', $header, $m);
                    if (isset($m[1])) {
                        $code = intval($m[1]);
                        break;
                    }
                }
            }

            // Fallback to the default if we got something we didn't expect
            if (!isset($responses[$code])) {
                $code = 400;
            }

            // Mock an exception response from the API
            $reply = array('error' => $responses[$code]['label'], 'error_description' => $responses[$code]['description']);
        } else {
            $reply = json_decode($response, true);
        }

        // Set back the error handler setting
        PHPUnit_Framework_Error_Warning::$enabled = TRUE;

        return array('info' => array(), 'reply' => $reply, 'replyRaw' => $response, 'error' => null);
    }

    protected function _clearMetadataCache()
    {
        MetaDataFiles::clearModuleClientCache();

        $metadataFiles = glob(sugar_cached('api/metadata/').'*');
        if ( is_array($metadataFiles) ) {
            foreach ($metadataFiles as $metadataFile) {
                @unlink($metadataFile);
            }
        }
    }

    /**
     * Parses response headers from a curl request. Acts similar to get_headers()
     *
     * @param  string $header
     * @return array
     */
    protected function _parseHeaderString($header)
    {
        $lines = explode("\n", rtrim($header));
        $headers = array();
        foreach ($lines as $line) {
            $parts = explode(": ", rtrim($line));
            if (count($parts) == 1) {
                $headers[] = $parts[0];
            } else {
                $headers[$parts[0]] = $parts[1];
            }
        }

        return $headers;
    }

    /**
     * Asserts that the response included the expected HTTP status code.
     *
     * @param  array $response
     * @param  int   $expectedCode
     * @return void
     */
    protected function assertHttpStatus($response, $expectedCode = 200)
    {
        $httpStatus = $response["info"]["http_code"];
        $this->assertEquals($expectedCode, $httpStatus, "Unexpected HTTP Status: {$httpStatus}\n");
    }
}
