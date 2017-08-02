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

require_once 'tests/{old}/service/SOAPTestCase.php';

/**
 * @group bug43696
 */
class Bug31003Test extends SOAPTestCase
{
	private $prospect;
    private $contact;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->prospect = new Prospect();
        $this->prospect->email1 = $this->contact->email1;
        $this->prospect->save();
        $GLOBALS['db']->commit();
    }

    public function testContactByEmail()
    {
    	$result = $this->_soapClient->call('contact_by_email', array('user_name' => $GLOBALS['current_user']->user_name, 'password' => $GLOBALS['current_user']->user_hash, 'email_address' => $this->contact->email1));
        $this->assertTrue(!empty($result) && count($result) > 0, 'Incorrect number of results returned. HTTP Response: '.$this->_soapClient->response);
    	$this->assertEquals($result[0]['name1'], $this->contact->first_name, 'Incorrect result found');
    }

    public function tearDown()
    {
        parent::tearDown();
        $GLOBALS['db']->query("DELETE FROM prospects WHERE id = '{$this->contact->id}'");
    }

}
