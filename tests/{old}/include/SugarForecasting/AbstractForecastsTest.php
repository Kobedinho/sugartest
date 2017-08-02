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
class SugarForecasting_AbstractForecastTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var MockSugarForecasting_Abstract
     */
    protected static $obj;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('timedate');
        SugarTestHelper::setUp('current_user');
        self::$obj = new MockSugarForecasting_Abstract(array());

        global $current_user;
        $reportee = SugarTestUserUtilities::createAnonymousUser();
        $reportee->reports_to_id = $current_user->id;
        $reportee->save();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group forecasts
     * @dataProvider dataTimeDataProvider
     */
    public function testConvertDateTimeToISO($dt_string, $expected)
    {
        $actual = self::$obj->convertDateTimeToISO($dt_string);
        $this->assertEquals($expected, $actual);
    }

    public static function dataTimeDataProvider()
    {
        global $current_user;
        $current_user->setPreference('timezone', 'America/Indiana/Indianapolis');
        $current_user->setPreference('datef', 'm/d/Y');
        $current_user->setPreference('timef', 'h:iA');
        $current_user->savePreferencesToDB();
        $timedate = TimeDate::getInstance();

        return array(
            array('2012-10-15 14:38:42', $timedate->asIso($timedate->fromDb('2012-10-15 14:38:42'), $current_user)), // db format
            //array('10/15/2012 10:38', $timedate->asIso($timedate->fromDb('2012-10-15 10:38:00'), $current_user)), // from user format
        );
    }

    /**
     * This is a test to ensure that the current_user is the first element in the array from the getUserReportees function
     * @group forecasts
     */
    public function testGetUserReportees() {
        global $current_user;
        $reportees = self::$obj->getUserReportees($current_user->id);
        $keys = array_keys($reportees);
        $this->assertEquals($current_user->id, $keys[0]);
    }
}

class MockSugarForecasting_Abstract extends SugarForecasting_AbstractForecast
{
    /**
     * Needed for the interface, but not uses
     *
     * @return array|string|void
     */
    public function process()
    {
    }

    public function getUserReportees($user_id) {
        return parent::getUserReportees($user_id);
    }

    public function convertDateTimeToISO($dt_string)
    {
        return parent::convertDateTimeToISO($dt_string);
    }
}
