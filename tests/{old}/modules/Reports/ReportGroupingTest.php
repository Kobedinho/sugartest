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
 * @covers Report
 */
class ReportGroupingTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user', array(true, true));

        // create account before custom field is created
        SugarTestAccountUtilities::createAccount();

        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('custom_field', array(
            'Accounts',
            array(
                'name' => 'checkbox',
                'type' => 'bool',
            ),
        ));

        // create account after custom field is created
        SugarTestAccountUtilities::createAccount();
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testAllTasksAreDisplayed()
    {
        global $current_user;

        $definition = array(
            'display_columns' => array(),
            'module' => 'Accounts',
            'group_defs' => array(
                array(
                    'name' => 'checkbox_c',
                    'table_key' => 'self',
                ),
            ),
            'summary_columns' => array(),
            'report_type' => 'summary',
            'full_table_list' => array(
                'self' => array(
                    'module' => 'Accounts',
                ),
                'Accounts:created_by_link' => array(
                    'parent' => 'self',
                    'link_def' => array(
                        'name' => 'created_by_link',
                        'relationship_name' => 'accounts_created_by',
                        'bean_is_lhs' => false,
                        'link_type' => 'one',
                        'module' => 'Users',
                        'table_key' => 'Accounts:created_by_link',
                    ),
                    'module' => 'Users',
                ),
            ),
            'filters_def' => array(
                array(
                    'operator' => 'AND',
                    array(
                        'name' => 'id',
                        'table_key' => 'Accounts:created_by_link',
                        'qualifier_name' => 'is',
                        'input_name0' => $current_user->id,
                    ),
                ),
            ),
        );

        $report = new Report(json_encode($definition));
        $report->run_summary_query();

        $row1 = $report->get_summary_next_row();
        $this->assertInternalType('array', $row1);
        $this->assertEquals(2, $row1['count'], 'Summary row should contain 2 records');

        $row2 = $report->get_summary_next_row();
        $this->assertEmpty($row2, 'There should not be second row');
    }
}
