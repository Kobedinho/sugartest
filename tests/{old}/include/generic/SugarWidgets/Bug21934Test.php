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
 * Bug 21934:
 *  Report filters are applying time offsets to date fields
 * @ticket 21934
 * @author arymarchik@sugarcrm.com
 **/
class Bug21934Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('timedate');
        global $timedate;
        $timedate->allow_cache = false;
        $timedate->clearCache();
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        global $timedate;
        $timedate->allow_cache = true;
    }

    /**
     * Testing correct time offset in month queries
     *
     * @group 21934
     * @dataProvider queryMonthProvider
     */
    public function testQueryMonth($timezone)
    {
        global $current_user;

        $now = new SugarDateTime();
        $now->setTimezone(new DateTimeZone('UTC'));

        $start = clone($now);
        $start->modify('first day of last month');

        $end = clone($now);
        $end->modify('last day of last month');

        /** @var SugarWidgetFieldDate|PHPUnit_Framework_MockObject_MockObject $widget */
        $widget = $this->getMockBuilder('SugarWidgetFieldDate')
            ->setMethods(array('now', 'get_start_end_date_filter'))
            ->disableOriginalConstructor()
            ->getMock();
        $widget->expects($this->any())
            ->method('now')
            ->will($this->returnValue($now));
        $widget->expects($this->once())
            ->method('get_start_end_date_filter')
            ->with($this->anything(), $start, $end);

        $current_user->setPreference('timezone', $timezone);
        $widget->queryFilterTP_last_month(array());
    }

    public static function queryMonthProvider()
    {
        return array(
            array('Pacific/Tongatapu'),
            array('Pacific/Midway'),
        );
    }
}
