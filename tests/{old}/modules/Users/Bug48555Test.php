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

class Bug48555Test extends Sugar_PHPUnit_Framework_TestCase
{
	protected $_user = null;

	public function setUp() 
    {
    	$this->_user = SugarTestUserUtilities::createAnonymousUser();
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$GLOBALS['current_user']->setPreference('default_locale_name_format', 'l f s');
	}
	
	public function tearDown()
	{
	    unset($GLOBALS['current_user']);
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}
        
    public function testgetListViewData()
    {
        $this->_user->first_name = "FIRST-NAME";
        $this->_user->last_name = "LAST-NAME";
        $test_array =  $this->_user->get_list_view_data();
        
        $this->assertEquals('LAST-NAME FIRST-NAME',$test_array['NAME']);
        $this->assertEquals('LAST-NAME FIRST-NAME',$test_array['FULL_NAME']);

    }
    
	public function testgetUsersNameAndEmail() 
    {
        $this->_user->first_name = "FIRST-NAME";
        $this->_user->last_name = "LAST-NAME";
        $this->_user->emailAddress->addAddress("test@test.test", $primary=true);
        $this->_user->emailAddress->save($this->_user->id, $this->_user->module_dir);;
        $test_array = $this->_user->getUsersNameAndEmail();
        
        $this->assertEquals('LAST-NAME FIRST-NAME',$test_array['name']);
        $this->assertEquals('test@test.test',$test_array['email']);
    }
    
    public function testgetEmailLink2()
    {
        $GLOBALS['sugar_config']['email_default_client'] = "sugar";
        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->id = 'abcdefg';
        $this->_contact->first_name = "FIRST-NAME";
        $this->_contact->last_name = "LAST-NAME";
        $this->_contact->object_name = 'Contact';
        $this->_contact->module_dir = 'module_dir';
        $this->_contact->createLocaleFormattedName = true;
        $test = $this->_user->getEmailLink2('test@test.test',$this->_contact);
        
        $pattern = "/.*\"to_email_addrs\":\"LAST-NAME FIRST-NAME \\\\u003Ctest@test.test\\\\u003E\".*/";
        
        $this->assertRegExp($pattern,$test);

    }
    
    public function testgetEmailLink()
    {
        $GLOBALS['sugar_config']['email_default_client'] = "sugar";
        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->id = 'abcdefg';
        $this->_contact->first_name = "FIRST-NAME";
        $this->_contact->last_name = "LAST-NAME";
        $this->_contact->email1 = "test@test.test";
        $this->_contact->object_name = 'Contact';
        $this->_contact->module_dir = 'module_dir';
        $this->_contact->createLocaleFormattedName = true;
        
        $test = $this->_user->getEmailLink("email1",$this->_contact);
        
        $pattern = "/.*\"to_email_addrs\":\"LAST-NAME FIRST-NAME \\\\u003Ctest@test.test\\\\u003E\".*/";
        
        $this->assertRegExp($pattern,$test);
    }
}

