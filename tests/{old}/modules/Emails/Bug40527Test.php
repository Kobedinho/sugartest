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
 

class Bug40527Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $contact;
    private $account;
    private $email;
    
	public function setUp()
    {
        global $app_strings, $app_list_strings;
        $app_strings = return_application_language($GLOBALS['current_language']);
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->account = SugarTestAccountUtilities::createAccount();
        
        $override_data = array(
            'parent_type' => 'Accounts',
            'parent_id' => $this->account->id,
        );
        $this->email   = SugarTestEmailUtilities::createEmail('', $override_data);
	}

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testContactRelationship()
    {
        $this->assertTrue($this->email->parent_type == 'Accounts', "The email parent_type should be Accounts");
        $this->assertTrue($this->email->parent_id == $this->account->id, "The email parent_id should be SDizzle");
        
        $this->email->fill_in_additional_detail_fields();
        $this->assertTrue(empty($this->email->contact_id), "There should be no contact associated with the Email");
    }
}
