<?php

//TODO: fix this up for when expected opps is added back in 6.8 - https://sugarcrm.atlassian.net/browse/SFA-255
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

class CommonTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Common
     */
    protected static $common_obj;

    /**
     * The Time period we are working with
     * @var Timeperiod
     */
    protected $timeperiod;

    /**
     * Manager
     * @var User
     */
    protected $manager;

    /**
     * Sales Rep
     * @var User
     */
    protected $rep;

    public static function setUpBeforeClass()
    {
        // Needed for some of the cache refreshes that happen downstream
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$common_obj = new Common();
    }

    public static function tearDownAfterClass()
    {
        self::$common_obj = null;
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        $this->manager = SugarTestUserUtilities::createAnonymousUser();

        $this->rep = SugarTestUserUtilities::createAnonymousUser();
        $this->rep->reports_to_id = $this->manager->id;
        $this->rep->save();

        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep2->reports_to_id = $this->manager->id;
        $rep2->save();

        $this->timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        SugarTestForecastUtilities::createForecast($this->timeperiod, $this->manager);

        SugarTestForecastUtilities::createForecast($this->timeperiod, $this->rep);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
    }

    /**
     * Only one record should be returned since we only created the forecast for the first user and not the second user
     *
     * @group forecasts
     */
    public function testGetReporteesWithForecastsReturnsOneRecord()
    {
        $return = self::$common_obj->getReporteesWithForecasts($this->manager->id, $this->timeperiod->id);

        $this->assertSame(1, count($return));
    }

    /**
     * @group forecasts
     */
    public function testGetReporteesWithForecastsReturnsEmptyWithInvalidTimePeriod()
    {
        $return = self::$common_obj->getReporteesWithForecasts($this->manager->id, 'invalid time period');

        $this->assertEmpty($return);
    }

    /**
     * @group forecasts
     */
    public function testGetReporteesWithForecastsReturnsEmptyWithInvalidUserId()
    {
        $return = self::$common_obj->getReporteesWithForecasts('Invalid Manager Id', $this->timeperiod->id);

        $this->assertEmpty($return);
    }

}
