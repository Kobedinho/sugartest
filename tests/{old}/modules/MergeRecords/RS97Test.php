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
 *  RS97: Prepare MergeRecord.
 */
class RS97Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testMerge()
    {
        $acc1 = SugarTestAccountUtilities::createAccount();
        $acc2 = SugarTestAccountUtilities::createAccount();
        $bean = new MergeRecord('Contacts');

        $this->assertEquals('Contacts', $bean->merge_module);

        $bean->load_merge_bean('Accounts', false, $acc1->id);
        $this->assertEquals($acc1->id, $bean->merge_bean->id);
        $this->assertEquals('Accounts', $bean->merge_module);

        $bean->load_merge_bean2('Accounts', false, $acc2->id);
        $this->assertEmpty($bean->merge_bean2);

        $bean->merge_module2 = 'Accounts';
        $bean->load_merge_bean2('Accounts', false, $acc2->id);
        $this->assertEquals($acc2->id, $bean->merge_bean2->id);

        $where = $bean->create_where_statement();
        $need = array("{$acc1->table_name}.id !=" . DBManagerFactory::getInstance()->quoted($acc1->id));
        $this->assertEquals($need, $where);

        $where = $bean->generate_where_statement(array('id = 1', 'name = 2'));
        $need = "id = 1 AND name = 2";
        $this->assertEquals($need, $where);

        $result = $bean->get_inputs_for_search_params(array());
        $this->assertEmpty($result);

        $bean->populate_search_params(array('nameSearchField' => 'value', 'nameSearchType' => 'RS97Test'));
        $this->assertArrayHasKey('name', $bean->field_search_params);

        $where = $bean->build_generic_where_clause('');
        $need = $acc1->build_generic_where_clause('');
        $this->assertEquals($need, $where);


        $where = $bean->fill_in_additional_list_fields();
        $need = $acc1->fill_in_additional_list_fields();
        $this->assertEquals($need, $where);

        $where = $bean->fill_in_additional_detail_fields();
        $need = $acc1->fill_in_additional_detail_fields();
        $this->assertEquals($need, $where);

        $where = $bean->get_summary_text();
        $need = $acc1->get_summary_text();
        $this->assertEquals($need, $where);

        $where = $bean->get_list_view_data();
        $need = $acc1->get_list_view_data();
        $this->assertEquals($need, $where);
    }
}
