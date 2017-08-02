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


/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 */
class ForecastsCommittedApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private static $reportee;

    /**
     * @var User
     */
    protected static $manager;

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var ForecastsFilterApi
     */
    protected $api;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$manager = SugarTestForecastUtilities::createForecastUser();
  
        self::$reportee = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee->reports_to_id = self::$manager['user']->id;
        self::$reportee->save();

    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        $this->api = new ForecastsFilterApi();
    }

    public function tearDown()
    {
        unset($this->api);
    }

    protected function _getMockApi($class_name)
    {
        return $this->createMock($class_name);
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilter()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $response = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            array('module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id)
        );

        $this->assertNotEmpty($response["records"], "Rest reply is empty. Rep data should have been returned.");
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAManagerTryingToViewAnotherUser()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'user_id' => self::$manager['user']->id)
        );

    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterDoesNotThrowExceptionWhenRepViewingHisOwnSheet()
    {
        $GLOBALS["current_user"] = self::$reportee;
        $return = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'user_id' => self::$reportee->id, 'timeperiod_id' => self::$timeperiod->id)
        );

        $this->assertInternalType('array', $return);
    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidUserId()
    {
        $GLOBALS["current_user"] = self::$manager['user'];

        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            array('module' => 'Forecasts', 'user_id' => 'im_not_valid')
        );

    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidTimeperiodId()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'timeperiod_id' => 'im_not_valid')
        );

    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidForecastType()
    {
        $GLOBALS["current_user"] = self::$reportee;

        $stub = $this->_getMockApi('ForecastsFilterApi');
        $stub->expects($this->any())
             ->method('filterList')
             ->will($this->returnValue(
                    array("next_offset" => -1,"records" => array())
                ));


        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id, 'forecast_type' => 'invalid_type')
        );
    }

    /**
     * @dataProvider forecastTypesDataProvider
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterDoesNotThrowsAnExceptionWithAValidForecastType($forecast_type)
    {
        $GLOBALS["current_user"] = self::$reportee;

        $stub = $this->_getMockApi('ForecastsFilterApi');
        $stub->expects($this->any())
             ->method('filterList')
             ->will($this->returnValue(
                    array("next_offset" => -1,"records" => array())
                ));


        $return = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            array('module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id, 'forecast_type' => $forecast_type)
        );

        $this->assertSame(array("next_offset" => -1,"records" => array()), $return);
    }

    public static function forecastTypesDataProvider()
    {
        return array(
            array('direct'),
            array('Direct'),
            array('rollup'),
            array('Rollup')
        );
    }

}

class ForecastFilterApiServiceMock extends RestService
{
    public function execute()
    {
    }

    protected function handleException(Exception $exception)
    {
    }
}
