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
/**
 * @ticket 41296
 */
require_once('vendor/nusoap//nusoap.php');
require_once 'tests/{old}/service/SOAPTestCase.php';


class Bug41296Test extends SOAPTestCase
{
	public $_soapClient = null;
    var $_sessionId;
    var $c = null;
    var $c2 = null;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();

		$unid = uniqid();
		$time = date('Y-m-d H:i:s');

        $contact = new Contact();
		$contact->id = 'c_'.$unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->email1 = 'one@example.com';
        $contact->email2 = 'one_other@example.com';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $GLOBALS['db']->commit();
		$this->c = $contact;

    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->_resultId}'");
        unset($this->c);
        parent::tearDown();
    }

    public function testCreateNewContactWithNoEmail()
    {
        $this->_login();

        $contacts_list=array( 'session'=>$this->_sessionId, 'module_name' => 'Contacts',
				   'name_value_lists' => array(
                                        array(array('name'=>'assigned_user_id' , 'value'=>$GLOBALS['current_user']->id),array('name'=>'first_name' , 'value'=>'testfirst'),array('name'=>'last_name' , 'value'=>'testlast'))
                                        ));

        $result = $this->_soapClient->call('set_entries',$contacts_list);
        $this->_resultId = $result['ids'][0];
        $this->assertNotEquals($this->c->id, $result['ids'][0], "Contacts should not match");
    }

}