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

require_once('modules/TimePeriods/TimePeriod.php');

class TimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $preTestIds = array();

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->preTestIds = TimePeriod::get_timeperiods_dom();

        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();

        $db->query("UPDATE timeperiods set deleted = 1");

        //Clean up anything else left in timeperiods table that was not deleted
        $db->query("UPDATE timeperiods SET deleted = 0 WHERE id IN ('" . implode("', '", array_keys($this->preTestIds))  . "')");

        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        parent::tearDown();
    }

    /**
     * @group timeperiods
     */
    public function testGetTimePeriodFromDbDateWithValidDate()
    {
        $this->markTestIncomplete("Marking incomplete as it fails in strict mode. SFA team.");
        // get time period within 2009-02-15
        $expected = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        $result = TimePeriod::retrieveFromDate('2009-02-15');
        $this->assertEquals($expected->id, $result->id);

        $result = TimePeriod::retrieveFromDate('2009-01-01');
        $this->assertEquals($expected->id, $result->id);

        $result = TimePeriod::retrieveFromDate('2009-03-31');
        $this->assertEquals($expected->id, $result->id);

    }

    /**
     * check that the timestamps are generated correctly
     * @group timeperiods
     */
    public function testTimePeriodTimeStamps()
    {
        // create a time period
        $tp = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        $timedate = TimeDate::getInstance();

        $start_date_timestamp = $timedate->fromDbDate('2009-01-01');
        $start_date_timestamp->setTime(0,0,0);
        $start_date_timestamp = $start_date_timestamp->getTimestamp();

        $end_date_timestamp = $timedate->fromDbDate('2009-03-31');
        $end_date_timestamp->setTime(23,59,59);
        $end_date_timestamp = $end_date_timestamp->getTimestamp();

        $this->assertEquals($start_date_timestamp, $tp->start_date_timestamp, "start time stamps do not match");
        $this->assertEquals($end_date_timestamp, $tp->end_date_timestamp, "end time stamps do not match");
    }

    /**
     * @group timeperiods
     */
    public function testUpgradeLegacyTimePeriodsUpgradesTimePeriodsWithOutDateStamps()
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2009-04-01', '2009-06-30');

        // create a third just to make sure that only two are really updated
        SugarTestTimePeriodUtilities::createTimePeriod('2009-07-01', '2009-09-30');

        $sql = "UPDATE timeperiods
                SET start_date_timestamp = null, end_date_timestamp = null
                WHERE id in ('".$tp1->id."','".$tp2->id."')";
        $db = DBManagerFactory::getInstance();
        $db->query($sql);

        $updated = $tp1->upgradeLegacyTimePeriods();

        $this->assertEquals(2, $updated);
    }
    
    /**
     * @group timeperiods
     */
     public function testRetrieveFromDate()
     {
        $this->markTestIncomplete("Marking incomplete as it fails in strict mode.  SFA team.");
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2013-04-01', '2013-06-30');
        
        //check to see if dates are in a timeperiod
        $tp3 = TimePeriod::retrieveFromDate('2013-01-30');
        $tp4 = TimePeriod::retrieveFromDate('2013-05-14');
        $tp5 = TimePeriod::retrieveFromDate('2013-07-01');
        
        $this->assertEquals($tp1->id, $tp3->id);
        $this->assertEquals($tp2->id, $tp4->id);
        $this->assertEquals(false, $tp5);
         
     }

    /**
     * @dataProvider dataProviderGetGenericStartEndByDuration
     *
     * @param $duration
     * @param $expected_start
     * @param $expected_end
     */
    public function testGetGenericStartEndByDuration($duration, $expected_start, $expected_end)
    {
        $tp = new TimePeriod();

        // set the start date since this is a unit test.
        $dates = $tp->getGenericStartEndByDuration($duration, '2014-08-21');

        $this->assertEquals($expected_start, $dates['start_date']);
        $this->assertEquals($expected_end, $dates['end_date']);
    }

    public function dataProviderGetGenericStartEndByDuration()
    {
        return array(
            array(0, '2014-07-01', '2014-09-30'),
            array(3, '2014-10-01', '2014-12-31'),
            array(12, '2014-01-01', '2014-12-31'),
            array('0', '2014-07-01', '2014-09-30'),
            array('3', '2014-10-01', '2014-12-31'),
            array('12', '2014-01-01', '2014-12-31'),
            array('current', '2014-07-01', '2014-09-30'),
            array('next', '2014-10-01', '2014-12-31'),
            array('year', '2014-01-01', '2014-12-31')
        );
    }
}
