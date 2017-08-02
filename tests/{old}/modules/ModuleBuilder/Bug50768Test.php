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
 * Bug #50768
 * in Studio once set the visibility function for currency field will miss the currency_Id
 *
 * @author asokol@sugarcrm.com
 * @ticket 50768
 */

class Bug50768Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $targetModule = "Accounts";
    protected $currencyFieldDef1 = array(
        "action" => "saveField",
        "comments" => "",
        "default" => "",
        "dependency" => "",
        "dependency_display" => "",
        "duplicate_merge" => "0",
        "enforced" => "false",
        "formula" => "",
        "formula_display" => "",
        "help" => "",
        "importable" => "true",
        "is_update" => "true",
        "labelValue" => "test_cur_c1",
        "label" => "LBL_TEST_CUR_1",
        "new_dropdown" => "",
        "reportableCheckbox" => "1",
        "reportable" => "1",
        "to_pdf" => "true",
        "type" => "currency",
        "name" => "c1",
        "module" => "ModuleBuilder",
        "view_module" => "Accounts",
    );

    protected $currencyFieldDef2 = array(
        "action" => "saveField",
        "comments" => "",
        "default" => "",
        "dependency" => "",
        "dependency_display" => "",
        "duplicate_merge" => "0",
        "enforced" => "false",
        "formula" => "",
        "formula_display" => "",
        "help" => "",
        "importable" => "true",
        "is_update" => "true",
        "labelValue" => "test_cur_c2",
        "label" => "LBL_TEST_CUR_2",
        "new_dropdown" => "",
        "reportableCheckbox" => "1",
        "reportable" => "1",
        "to_pdf" => "true",
        "type" => "currency",
        "name" => "c2",
        "module" => "ModuleBuilder",
        "view_module" => "Accounts",
    );

    public function setUp()
    {
        $this->markTestIncomplete("This test breaks others tests on 644 on CI.  Disabling for sanity check");
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

     /*   unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_list_strings']);
        unset($_REQUEST);*/

    }

    /**
     * Test checks if currency_id field was deleted with the lasr currency field
     * @group 50768
     */
    public function testSettedCurrencyIdField()
    {
        $mbc = new ModuleBuilderController();
        //Create the new Fields
        $_REQUEST = $this->currencyFieldDef1;
        $mbc->action_SaveField();
        $_REQUEST = $this->currencyFieldDef2;
        $mbc->action_SaveField();

        $this->currencyFieldDef1['name'] = 'c1_c';
        $_REQUEST = $this->currencyFieldDef1;
      //  $mbc->action_DeleteField();
        $this->currencyFieldDef2['name'] = 'c2_c';
        $_REQUEST = $this->currencyFieldDef2;
       // $mbc->action_DeleteField();

        $count = 0;
        $query = "SELECT * FROM fields_meta_data WHERE custom_module='Accounts' AND type='currency_id' AND deleted = 0";
        $result = $GLOBALS['db']->query ( $query );
        while ( $row = $GLOBALS['db']->fetchByAssoc ( $result ) ) {
            $count++;
        }
        $this->assertEquals($count, 0);
    }
}
