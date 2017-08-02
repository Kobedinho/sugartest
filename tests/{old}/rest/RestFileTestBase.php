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

require_once('tests/{old}/rest/RestTestBase.php');

class RestFileTestBase extends RestTestBase {
    protected $_note;
    protected $_note_id;
    protected $_contact;
    protected $_contact_id;
    protected $_testfile1 = 'Bug55655-01.txt';
    protected $_testfile2 = 'Bug55655-02.txt';

    public function setUp()
    {
        parent::setUp();

        // Create two sample text files for uploading
        sugar_file_put_contents($this->_testfile1, create_guid());
        sugar_file_put_contents($this->_testfile2, create_guid());

        // Create a test contact and a test note
        $contact = new Contact();
        $contact->first_name = 'UNIT TEST';
        $contact->last_name = 'TESTY TEST';
        $contact->save();
        $this->_contact_id = $contact->id;
        $this->_contact = $contact;

        $note = new Note();
        $note->name = 'UNIT TEST';
        $note->description = 'UNIT TEST';
        $note->save();
        $this->_note_id = $note->id;
        $this->_note = $note;
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        unlink($this->_testfile1);
        unlink($this->_testfile2);

        parent::tearDown();

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->_contact_id}'");
        $GLOBALS['db']->query("DELETE FROM notes WHERE id = '{$this->_note_id}'");

        unset($this->_contact, $this->_note);
        $GLOBALS['db']->commit();
    }

    protected function _restCallNoAuthHeader($urlPart,$postBody='',$httpAction='', $addedOpts = array(), $addedHeaders = array())
    {
        $urlBase = $GLOBALS['sugar_config']['site_url'].'/api/rest.php/v6/';
        $ch = curl_init($urlBase.$urlPart);
        if (!empty($postBody)) {
            if (empty($httpAction)) {
                $httpAction = 'POST';
                curl_setopt($ch, CURLOPT_POST, 1); // This sets the POST array
                $requestMethodSet = true;
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
        } else {
            if (empty($httpAction)) {
                $httpAction = 'GET';
            }
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (is_array($addedOpts) && !empty($addedOpts)) {
            // I know curl_setopt_array() exists, just wasn't sure if it was hurting stuff
            foreach ($addedOpts as $opt => $val) {
                curl_setopt($ch, $opt, $val);
            }
        }

        $httpReply = curl_exec($ch);
        $httpInfo = curl_getinfo($ch);
        $httpError = $httpReply === false ? curl_error($ch) : null;

        return array('info' => $httpInfo, 'reply' => json_decode($httpReply,true), 'replyRaw' => $httpReply, 'error' => $httpError);
    }
}

