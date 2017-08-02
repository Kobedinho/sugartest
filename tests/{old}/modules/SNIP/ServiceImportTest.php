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

require_once('tests/{old}/service/APIv3Helper.php');

class ServiceImportTest extends Sugar_PHPUnit_Framework_TestCase
{
	private $email_id = '';

	public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
		// get configured date format
		$timedate = new TimeDate();
		$this->date_time_format = $timedate->get_date_time_format($this->_user);
    }

    public function tearDown()
    {
		// delete emails that were imported
    	$GLOBALS['db']->query("DELETE FROM emails WHERE id = '{$this->email_id}'");
    	$GLOBALS['db']->query("DELETE FROM emails_text WHERE email_id = '{$this->email_id}'");
	    unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function _login($user = null)
    {
        $GLOBALS['db']->commit(); // Making sure we commit any changes before logging in
        if($user == null)
            $user = $this->_user;
        return $this->_makeRESTCall('login',
            array(
                'user_auth' =>
                    array(
                        'user_name' => $user->user_name,
                        'password' => $user->user_hash,
                        'version' => '.01',
                        ),
                'application_name' => 'mobile',
                'name_value_list' => array(),
                )
            );
    }

    protected function _makeRESTCall($method,$parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v4/rest.php';
        // Open a curl session for making the call
        $curl = curl_init($url);
        // set URL and other appropriate options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        // build the request URL
        $json = urlencode(json_encode($parameters));
        $postArgs = "method=$method&input_type=JSON&response_type=JSON&rest_data=$json";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        // Make the REST call, returning the result
        $response = curl_exec($curl);
        // Close the connection
        curl_close($curl);

        $this->_lastRawResponse = $response;

        // Convert the result from JSON format to a PHP array
        return json_decode($response,true);
    }

    public function testImportHTMLEmail()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $result = $this->_login();
        $session = $result['id'];

		// import email through snip
		$email['message']['message_id'] = 'html12345';
		$email['message']['from_name'] = 'Test Emailer <temailer@sugarcrm.com>';
		$email['message']['description'] = 'This is a test email';
		$email['message']['description_html'] = 'This is a <b>&quot;test&quot;</b> <u>&quot;email&quot;</u> from Test Emailer &lt;temailer@sugarcrm.com&gt;';
		$email['message']['to_addrs'] = 'sugar.phone@example.name';
		$email['message']['cc_addrs'] = 'sugar.section.dev@example.net';
		$email['message']['bcc_addrs'] = 'qa.sugar@example.net';
		$email['message']['date_sent'] = 'Tue, 6 Dec 2011 12:46:21 -0600';
		$email['message']['subject'] = 'PHPUnit Test Email';
		$email['user'] = $GLOBALS['current_user']->user_name;

		$email_data=array( 'session'=>$session, "email" => $email);

        $result = $this->_makeRESTCall('snip_import_emails',$email_data);
        $GLOBALS['db']->commit();

        $e = new Email();
		$e->retrieve_by_string_fields(array("message_id" => $email['message']['message_id']));
		$this->assertAttributeNotEmpty("id", $e, "ID is empty!");
		$this->email_id = $e->id;
		// populate the whole bean
		$e->retrieve($e->id);

        // validate if everything was saved correctly
		$this->assertEquals($email['message']['message_id'], $e->message_id);
		$this->assertEquals($email['message']['from_name'], $e->from_addr_name);
		$this->assertEquals($email['message']['description'], $e->description);
		// This is disabled because &quot; is mangled by HTML conversion
		//$this->assertEquals($email['message']['description_html'], $e->description_html);
		$this->assertEquals($email['message']['to_addrs'], $e->to_addrs);
		$this->assertEquals($email['message']['cc_addrs'], $e->cc_addrs);
		$this->assertEquals($email['message']['bcc_addrs'], $e->bcc_addrs);
		$this->assertEquals($email['message']['subject'], $e->name);
		$this->assertEquals(gmdate($this->date_time_format,strtotime($email['message']['date_sent'])), $e->date_sent);
    }

}
