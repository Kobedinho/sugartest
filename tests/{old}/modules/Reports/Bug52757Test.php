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
 * Bug #52757
 * DoNotMoveFrom645: Reports join issues?
 *
 * @author mgusev@sugarcrm.com
 * @ticked 52757
 */
class Bug52757Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    public $report = null;

    /**
     * Filling default report object
     */
    function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $this->report = new Report();
        $this->report->report_def['full_table_list'] = array(
            'self' => array(
                'module' => 'Accounts'
            ),
            'Accounts:calls' => array(
                'module' => 'Calls',
                'parent' => 'self'
            ),
            'Accounts:calls:assigned_user_link' => array(
                'module' => 'Users',
                'parent' => 'Accounts:calls'
            )
        );
    }

    /**
     * Removing default report object
     */
    function tearDown()
    {
        unset($this->report);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    /**
     * Test presents all information and asserts that all tables are present in full_table_list
     */
    function testReportValidationAllDataArePresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'self'
            ),
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls'
            ),
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls:assigned_user_link'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self'
                ),
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls'
                ),
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls:assigned_user_link'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self',
            'Accounts:calls',
            'Accounts:calls:assigned_user_link'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }

    /**
     * Test presents account and call only and asserts that Account:calls:assigned_user_link is removed from full_table_list
     */
    function testReportValidationAssignedUserIsNotPresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'self'
            ),
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self'
                ),
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self',
            'Accounts:calls'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }

    /**
     * Test presents account and assigned user only and asserts that all tables are present in full_table_list
     * because assigned user depends on call
     */
    function testReportValidationCallIsNotPresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'self'
            ),
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls:assigned_user_link'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self'
                ),
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls:assigned_user_link'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self',
            'Accounts:calls',
            'Accounts:calls:assigned_user_link'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }

    /**
     * Test presents assigned user only and asserts that all tables are present in full_table_list
     * because assigned user depends on call and call depends on account
     */
    function testReportValidationOnlyAssignedUserIsPresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls:assigned_user_link'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls:assigned_user_link'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self',
            'Accounts:calls',
            'Accounts:calls:assigned_user_link'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }

    /**
     * Test presents account only and asserts that only self table is present in full_table_list
     */
    function testReportValidationOnlyAccountIsPresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'self'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }
}
