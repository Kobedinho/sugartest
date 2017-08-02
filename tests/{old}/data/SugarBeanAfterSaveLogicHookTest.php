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

class SugarBeanAfterSaveLogicHookTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $hook;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        LogicHook::refreshHooks();
        $this->hook =  array('Accounts', 'after_save', Array(1, 'Accounts::after_save', __FILE__, 'SugarBeanAfterSaveTestHook', 'afterSave'));
        call_user_func_array('check_logic_hook_file', $this->hook);
    }

    public function tearDown()
    {
        call_user_func_array('remove_logic_hook', $this->hook);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }
    
    public function testCallAfterSave()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->website = 'old website'; // non-audit field
        $account->phone_office = 'old phone'; // audit field
        $account->save();
        // make sure $this->fetched_row is populated with old data
        $account->retrieve($account->id);
        $fetched_row = $account->fetched_row;
        // clear cache
        SugarBeanAfterSaveTestHook::$fetched_row = array();
        $account->website = 'new website';
        $account->phone_office = 'new phone';
        $account->save();
        $restored_fetched_row = SugarBeanAfterSaveTestHook::$fetched_row;
        $this->assertEquals($fetched_row['website'], $restored_fetched_row['website'], 'Failed to restore $fetched_row["website"] in after_save logic hook');
        $this->assertEquals($fetched_row['phone_office'], $restored_fetched_row['phone_office'], 'Failed to restore $fetched_row["phone_office"] in after_save logic hook');
    }
}
 
class SugarBeanAfterSaveTestHook
{
    static public $fetched_row = array();

    public function afterSave($bean, $event, $arguments)
    {
        self::$fetched_row = $bean->fetched_row;
        // restore fetched_row 
        foreach ($arguments['dataChanges'] as $field) {
            self::$fetched_row[$field['field_name']] = $field['before'];
        }
    }
}
