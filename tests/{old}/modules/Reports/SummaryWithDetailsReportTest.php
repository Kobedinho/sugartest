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

require_once('modules/Reports/templates/templates_list_view.php');

/**
 * Summary With Details Report tests
 */
class SummaryWithDetailsReportTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $rowsAndColumnsData;
    private $report;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->report = new Report();
        $this->report->report_def = array('group_defs' => $this->getDummyGroupDefs());
        $this->report->group_defs_Info = $this->getDummyGroupDefsInfo();
        $this->rowsAndColumnsData = $this->getData();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Check start row for zero count
     */
    public function testGroupByFunctionZeroCount()
    {
        $ret = whereToStartGroupByRowSummaryCombo($this->report, 0, $this->rowsAndColumnsData[0], null);
        $this->assertEquals(0, $ret, 'Should return 0 when count = 0');
    }

    /**
     * Check start index for a single group by row
     */
    public function testGroupByFunctionUniqueRecord()
    {
        $ret = whereToStartGroupByRowSummaryCombo(
            $this->report,
            2,
            $this->rowsAndColumnsData[1],
            $this->rowsAndColumnsData[2]
        );

        $this->assertEquals(1, $ret, 'Should start at index 1 for "sarah"');
    }

    /**
     * Check start indexes for multiple rows with grouped data
     */
    public function testGroupByFunctionNonUniqueRecord()
    {
        $ret = whereToStartGroupByRowSummaryCombo(
            $this->report,
            1,
            $this->rowsAndColumnsData[0],
            $this->rowsAndColumnsData[1]
        );
        $this->assertEquals(0, $ret, 'Should start at index 0 for "chris"');

        $ret = whereToStartGroupByRowSummaryCombo(
            $this->report,
            2,
            $this->rowsAndColumnsData[1],
            $this->rowsAndColumnsData[2]
        );
        $this->assertEquals(1, $ret, 'Should start at index 1 for "sarah"');
    }

    private function getDummyGroupDefs()
    {
        return array(
            0 => array(
                'name' => 'user_name',
                'label' => 'User Name',
                'table_key' => 'Opportunities:assigned_user_link',
                'type' => 'username'
            ),
            1 => array(
                'name' => 'sales_stage',
                'label' => 'Sales Stage',
                'table_key' => 'self',
                'type' => 'enum'
            ),
        );
    }

    private function getDummyGroupDefsInfo()
    {
        $grpDefs =  array(
            array(
                'name' => 'user_name',
                'label' => 'User Name',
                'table_key' => 'Opportunities:assigned_user_link',
                'type' => 'user_name',
                'index' => 0
            ),
            array(
                'name' => 'sales_stage',
                'label' => 'Sales Stage',
                'table_key' => 'self',
                'type' => 'enum',
                'index' => 1
            ),
        );
        $grpKeys = array_map('getGroupByKey', $grpDefs);
        return array_combine($grpKeys, $grpDefs);
    }

    private function getData()
    {
        return array(
            array(
                'cells' => array(
                    "chris",
                    "Value Proposition",
                    "$10,000.00",
                    "$10,000.00",
                    "1"
                ),
                'count' => 1,
                'User Name' => 'chris'
            ),
            array(
                'cells' => array(
                    "sarah",
                    "Value Proposition",
                    "$10,000.00",
                    "$20,000.00",
                    "2"
                ),
                'count' => 2,
                'User Name' => 'sarah'
            ),
            array(
                'cells' => array(
                    "sarah",
                    "Needs Analysis",
                    "$10,000.00",
                    "$10,000.00",
                    "1"
                ),
                'count' => 1,
                'User Name' => 'sarah'
            ),
        );
    }
}
