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
 * Bug #58360
 * Lead Report Inconsistencies
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58360
 */
class Bug58360Test extends Sugar_PHPUnit_Framework_TestCase
{
    function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $lead = SugarTestLeadUtilities::createLead();
        $GLOBALS['db']->query("UPDATE {$lead->table_name} SET date_modified = " . $GLOBALS['db']->convert("'1999-12-31 00:30:00'", 'datetime') . " WHERE id = " . $GLOBALS['db']->quoted($lead->id));

        $lead = SugarTestLeadUtilities::createLead();
        $GLOBALS['db']->query("UPDATE {$lead->table_name} SET date_modified = " . $GLOBALS['db']->convert("'1999-12-31 23:30:00'", 'datetime') . " WHERE id = " . $GLOBALS['db']->quoted($lead->id));

        $lead = SugarTestLeadUtilities::createLead();
        $GLOBALS['db']->query("UPDATE {$lead->table_name} SET date_modified = " . $GLOBALS['db']->convert("'2000-01-01 00:30:00'", 'datetime') . " WHERE id = " . $GLOBALS['db']->quoted($lead->id));

        $lead = SugarTestLeadUtilities::createLead();
        $GLOBALS['db']->query("UPDATE {$lead->table_name} SET date_modified = " . $GLOBALS['db']->convert("'2000-01-01 23:30:00'", 'datetime') . " WHERE id = " . $GLOBALS['db']->quoted($lead->id));
    }

    function tearDown()
    {
        $GLOBALS['timedate']->setUser();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider dataProvider
     */
    function testDayMonthYear($timezone, $qualifier, $expected)
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $GLOBALS['current_user']->setPreference('timezone', $timezone);
        $GLOBALS['timedate']->setUser($GLOBALS['current_user']);

        $layout_def = array(
            'column_function' => $qualifier,
            'column_key' => 'self:date_modified',
            'force_label' => 'Day: Date Modified',
            'name' => 'date_modified',
            'qualifier' => $qualifier,
            'table_alias' => 'leads',
            'table_key' => 'self',
            'type' => 'datetime'
        );

        $layoutManager = new LayoutManager();
        $layoutManager->default_widget_name = 'ReportField';
        $layoutManager->setAttributePtr('reporter', new Report());

        $layoutManager->setAttribute('context', 'GroupBy');
        $group_by = $layoutManager->widgetQuery($layout_def);
        $layoutManager->setAttribute('context', 'Select');
        $select = $layoutManager->widgetQuery($layout_def);

        $actual = array();
        $result = $GLOBALS['db']->query("SELECT {$select}, COUNT(*) count FROM leads WHERE id IN ('" . implode("', '", SugarTestLeadUtilities::getCreatedLeadIds()) . "') GROUP BY " . $group_by);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $date = reset($row);
            $count = next($row);
            $this->assertArrayNotHasKey($date, $actual, $date . ' already present in result');
            $actual[$date] = $count;
        }

        $this->assertEquals($expected, $actual, 'Group by sent incorrect query');
    }

    function dataProvider()
    {
        return array(
            array('GMT', 'day', array(
                '1999-12-31' => '2',
                '2000-01-01' => '2'
            )),
            array('Africa/Algiers', 'day', array(
                '1999-12-31' => '1',
                '2000-01-01' => '2',
                '2000-01-02' => '1'
            )),
            array('Atlantic/Azores', 'day', array(
                '1999-12-30' => '1',
                '1999-12-31' => '2',
                '2000-01-01' => '1'
            )),

            array('GMT', 'month', array(
                '1999-12' => '2',
                '2000-01' => '2'
            )),
            array('Africa/Algiers', 'month', array(
                '1999-12' => '1',
                '2000-01' => '3'
            )),
            array('Atlantic/Azores', 'month', array(
                '1999-12' => '3',
                '2000-01' => '1'
            )),

            array('GMT', 'year', array(
                '1999' => '2',
                '2000' => '2'
            )),
            array('Africa/Algiers', 'year', array(
                '1999' => '1',
                '2000' => '3'
            )),
            array('Atlantic/Azores', 'year', array(
                '1999' => '3',
                '2000' => '1'
            ))
        );
    }
}
