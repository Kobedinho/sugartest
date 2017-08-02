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
 * Bug #47722
 * 	Imports to Custom Relate Fields Do Not Work
 * @ticket 47722
 */
class Bug47722Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $contact;
    
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestContactUtilities::removeAllCreatedContacts();
    }
    
    /**
     * @group 47722
     */
    public function testImportSanitize()
    {
        $vardef = array('module' => 'Contacts', 
                        'id_name' => 'contact_id_c', 
                        'name' => 'test_rel_cont_c');
        $value = $this->contact->first_name .' '. $this->contact->last_name;
        $focus = new Lead();
        $settings = new ImportFieldSanitize();
        
        $sfr = new SugarFieldRelate('relate');
        $value = $sfr->importSanitize($value, $vardef, $focus, $settings);
        $this->assertEquals($focus->{$vardef['id_name']}, $this->contact->id);
    }
}
