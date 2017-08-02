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



class RenameModulesTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $language = 'en_us';
    private $language_contents;
    private $global_language_contents;

    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $mods = array('Accounts', 'Contacts', 'Campaigns');
        foreach($mods as $mod)
        {
            if(file_exists("custom/modules/{$mod}/language/en_us.lang.php"))
            {
                $this->language_contents[$mod] = file_get_contents("custom/modules/{$mod}/language/en_us.lang.php");
                SugarAutoLoader::unlink("custom/modules/{$mod}/language/en_us.lang.php", true);
            }
        }

        // check the global lang file
        if (file_exists("custom/include/language/" . $this->language . ".lang.php")) {
            $this->global_language_contents = file_get_contents("custom/include/language/" . $this->language . ".lang.php");
        }
    }

    public function tearDown()
    {
        $this->removeCustomAppStrings();
        $this->removeModuleStrings(array('Accounts'));
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarCache::$isCacheReset = false;

        if(!empty($this->language_contents))
        {
            foreach($this->language_contents as $key=>$contents)
            {
                SugarAutoLoader::put("custom/modules/{$key}/language/en_us.lang.php", $contents, true);
            }
        }

        if(!empty($this->global_language_contents)) {
            SugarAutoLoader::put("custom/include/language/" . $this->language . ".lang.php", $this->global_language_contents, true);
        }
        SugarTestHelper::tearDown();
    }


    public function testGetRenamedModules()
    {
        $rm = new RenameModules();
        $this->assertEquals(0, count($rm->getRenamedModules()) );
    }


    public function testRenameContactsModule()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $module = 'Accounts';
        $newSingular = 'Company';
        $newPlural = 'Companies';

        $rm = new RenameModules();

        $_REQUEST['slot_0'] = 0;
        $_REQUEST['key_0'] = $module;
        $_REQUEST['svalue_0'] = $newSingular;
        $_REQUEST['value_0'] = $newPlural;
        $_REQUEST['delete_0'] = '';
        $_REQUEST['dropdown_lang'] = $this->language;
        $_REQUEST['dropdown_name'] = 'moduleList';

        global $app_list_strings;
        
        foreach(getTypeDisplayList() as $typeDisplay) 
        {
            if (!isset($app_list_strings[$typeDisplay][$module])) 
            {
                $app_list_strings[$typeDisplay][$module] = 'Account';
            }
        }
        
        $rm->save(FALSE);
        SugarAutoLoader::buildCache();

        //Test app list strings
        $app_list_string = return_app_list_strings_language('en_us');
        $this->assertEquals($newSingular, $app_list_string['moduleListSingular'][$module] );
        $this->assertEquals($newPlural, $app_list_string['moduleList'][$module] );
        foreach(getTypeDisplayList() as $typeDisplay) 
        {
            $this->assertEquals($newSingular, $app_list_string[$typeDisplay][$module] );
        }

        //Test module strings for account
        $accountStrings = return_module_language('en_us','Accounts', TRUE);
        $this->assertEquals('Create Company', $accountStrings['LNK_NEW_ACCOUNT'], "Rename module failed for modules modStrings.");
        $this->assertEquals('View Companies', $accountStrings['LNK_ACCOUNT_LIST'], "Rename module failed for modules modStrings.");
        $this->assertEquals('Import Companies', $accountStrings['LNK_IMPORT_ACCOUNTS'], "Rename module failed for modules modStrings.");
        $this->assertEquals('Company Search', $accountStrings['LBL_SEARCH_FORM_TITLE'], "Rename module failed for modules modStrings.");
        $this->assertEquals('Company', $accountStrings['LBL_MODULE_NAME_SINGULAR'], "Rename module failed for modules modstrings.");

        //Test related link renames
        $contactStrings = return_module_language('en_us','Contacts', TRUE);
        $this->assertEquals('Company Name:', $contactStrings['LBL_ACCOUNT_NAME'], "Rename related links failed for module.");

        //The next test is invalidated by the vardef change made in b2ed73ffbfc6cb912a0befffb5d9691526993240 which changes the account_id field to be of type id instead of relate
        //$this->assertEquals('Company ID:', $contactStrings['LBL_ACCOUNT_ID'], "Rename related links failed for module.");

        //Test subpanel renames
        $campaignStrings = return_module_language('en_us','Campaigns', TRUE);
        $this->assertEquals('Companies', $campaignStrings['LBL_CAMPAIGN_ACCOUNTS_SUBPANEL_TITLE'], "Renaming subpanels failed for module.");
        // bug 45554: ensure labels are changed
        $this->assertEquals('Companies', $campaignStrings['LBL_ACCOUNTS'], 'Renaming labels failed for module.');

        //Ensure we recorded which modules were modified.
        $renamedModules = $rm->getRenamedModules();
        $this->assertTrue( count($renamedModules) > 0 );
        $this->removeCustomAppStrings();
        $this->removeModuleStrings( $renamedModules );
    }

    public function testRenameNonExistantModule()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $module = 'UnitTestDNEModule';
        $newSingular = 'UnitTest';
        $newPlural = 'UnitTests';

        $rm = new RenameModules();

        $_REQUEST['slot_0'] = 0;
        $_REQUEST['key_0'] = $module;
        $_REQUEST['svalue_0'] = $newSingular;
        $_REQUEST['value_0'] = $newPlural;
        $_REQUEST['delete_0'] = '';
        $_REQUEST['dropdown_lang'] = $this->language;
        $_REQUEST['dropdown_name'] = 'moduleList';
        $_REQUEST['use_push'] = TRUE;

        $rm->save(FALSE);

        //Ensure no modules were modified
        $renamedModules = $rm->getRenamedModules();
        $this->assertTrue( count($renamedModules) == 0 );

        //Ensure none of the app list strings were modified.
        $app_list_string = return_app_list_strings_language('en_us');
        if(isset( $app_list_string['moduleListSingular'][$module])) {
            $this->assertNotEquals($newSingular, $app_list_string['moduleListSingular'][$module] );
        }
        if(isset($app_list_string['moduleList'][$module])) {
            $this->assertNotEquals($newPlural, $app_list_string['moduleList'][$module] );
        }

    }


    private function removeCustomAppStrings()
    {
        $fileName = 'custom/include/language/' . $this->language . '.lang.php';
        if( file_exists($fileName) )
        {
            @SugarAutoLoader::unlink($fileName, true);
        } else {
                SugarAutoLoader::delFromMap($fileName, true);
        }
    }

    private function removeModuleStrings($modules)
    {
        foreach($modules as $module => $v)
        {
            $fileName = 'custom/modules/' . $module . '/language/' . $this->language . '.lang.php';
            if( file_exists($fileName) )
            {
                @SugarAutoLoader::unlink($fileName, true);
            } else {
                SugarAutoLoader::delFromMap($fileName, true);
            }

        }

    }

    /**
     * @group bug46880
     * making sure subpanel is not renamed twice by both plural name and singular name
     */
    public function testSubpanelRenaming()
    {
        $this->markTestIncomplete('Because of bug 47239,  Skipping test.');

        $module = 'Accounts';
        $newSingular = 'Account1';
        $newPlural = 'Accounts2';

        $rm = new RenameModules();

        $_REQUEST['slot_0'] = 0;
        $_REQUEST['key_0'] = $module;
        $_REQUEST['svalue_0'] = $newSingular;
        $_REQUEST['value_0'] = $newPlural;
        $_REQUEST['delete_0'] = '';
        $_REQUEST['dropdown_lang'] = $this->language;
        $_REQUEST['dropdown_name'] = 'moduleList';

        global $app_list_strings;

        foreach(getTypeDisplayList() as $typeDisplay) 
        {
            if (!isset($app_list_strings[$typeDisplay][$module])) 
            {
                $app_list_strings[$typeDisplay][$module] = 'Account';
            }
        }
        $rm->save(FALSE);

        //Test subpanel renames
        $bugStrings = return_module_language('en_us','Bugs', TRUE);
        $this->assertEquals('Accounts2', $bugStrings['LBL_ACCOUNTS_SUBPANEL_TITLE'], "Renaming subpanels failed for module.");

        //Ensure we recorded which modules were modified.
        $renamedModules = $rm->getRenamedModules();
        $this->assertTrue( count($renamedModules) > 0 );

        //cleanup
        $this->removeCustomAppStrings();
        $this->removeModuleStrings( $renamedModules );
    }

    /**
     * @group bug45804
     */
    public function testDashletsRenaming()
    {
        $this->markTestSkipped('Because of bug 47239,  Skipping test.');

        $module = 'Accounts';
        $newSingular = 'Account1';
        $newPlural = 'Accounts2';

        $rm = new RenameModules();

        $_REQUEST['slot_0'] = 0;
        $_REQUEST['key_0'] = $module;
        $_REQUEST['svalue_0'] = $newSingular;
        $_REQUEST['value_0'] = $newPlural;
        $_REQUEST['delete_0'] = '';
        $_REQUEST['dropdown_lang'] = $this->language;
        $_REQUEST['dropdown_name'] = 'moduleList';

        global $app_list_strings;
        
        foreach(getTypeDisplayList() as $typeDisplay) 
        {
            if (!isset($app_list_strings[$typeDisplay][$module])) 
            {
                $app_list_strings[$typeDisplay][$module] = 'Account';
            }
        }
        $rm->save(FALSE);

        //Test dashlets renames
        $callStrings = return_module_language('en_us', 'Accounts', TRUE);
        $this->assertEquals('My Accounts2', $callStrings['LBL_HOMEPAGE_TITLE'], "Renaming dashlets failed for module.");

        //Ensure we recorded which modules were modified.
        $renamedModules = $rm->getRenamedModules();
        $this->assertTrue( count($renamedModules) > 0 );

        //cleanup
        $this->removeCustomAppStrings();
        $this->removeModuleStrings( $renamedModules );
    }

    /**
     * Provide test data for renaming module-related strings.
     *
     * @return array
     */
    public function fieldNameProvider()
    {
        return array(
            // Test empty label.
            array('', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), '', ''),
            // Test whole words.
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Client', 'Client'),
            array('Accounts', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Clients', 'Clients'),
            // Test empty field values.
            array('Contacts', array('prev_singular' => '', 'prev_plural' => '', 'singular' => '', 'plural' => ''), 'Contacts', 'Contacts'),
            array('Contact', array('prev_singular' => 'Contact', 'prev_plural' => '', 'singular' => 'Client', 'plural' => 'Clients'), 'Contact', 'Contact'),
            array('Contacts', array('prev_singular' => '', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => 'Clients'), 'Contacts', 'Contacts'),
            array('Contact', array('prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => '', 'plural' => 'Clients'), 'Contact', 'Contact'),
            array('Contacts', array('prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => ''), 'Contacts', 'Contacts'),
            array('Contacts', array('prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => '', 'plural' => 'Clients'), 'Contacts', 'Clients'),
            array('Contact', array('prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => ''), 'Client', 'Contact'),
            // Test multiple words in labels.
            array('My Account:', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'My Client:', 'My Client:'),
            array('View Accounts Module', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'View Clients Module', 'View Clients Module'),
            // Test labels without previous values.
            array('View Module', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'View Module', 'View Module'),
            array('Settings', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Settings', 'Settings'),
            // Test multiple replacements.
            array('Account Accounts', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Client Clients', 'Client Clients'),
            array('Account Accounts Account', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Client Clients Client', 'Client Clients Client'),
            // Test labels with same previous values.
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => 'Client', 'plural' => 'Clients'), 'Client', 'Clients'),
            array('Account Accounts', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => 'Client', 'plural' => 'Clients'), 'Client Accounts', 'Clients Accounts'),
            // Test fields with special characters.
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => '<script>alert("hello");</script>', 'plural' => ''), 'alert(&quot;hello&quot;);', 'Account'),
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => '', 'plural' => '<script>alert("hello");</script>'), 'Account', 'alert(&quot;hello&quot;);'),
            // Test fields with only spaces.
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => ' ', 'plural' => ' '), 'Account', 'Account')
        );
    }

    /**
     * Test renaming module-related string functionality.
     *
     * @dataProvider fieldNameProvider
     */
    public function testModuleRelatedStringRenaming($label, $renameFields, $newLabel, $newLabelPluralFirst)
    {
        $rm = new RenameModules();

        // Perform the same sanitization checks done during the actual request.
        $renameFields['singular'] = SugarCleaner::stripTags($renameFields['singular']);
        $renameFields['plural'] = SugarCleaner::stripTags($renameFields['plural']);
        $renameFields['singular'] = trim($renameFields['singular']);
        $renameFields['plural'] = trim($renameFields['plural']);

        $renamedLabelSingularFirst = $rm->renameModuleRelatedStrings($label, $renameFields, false);
        $renamedLabelDefault = $rm->renameModuleRelatedStrings($label, $renameFields);

        $this->assertEquals($newLabel, $renamedLabelSingularFirst);
        $this->assertEquals($newLabelPluralFirst, $renamedLabelDefault);
    }
}
