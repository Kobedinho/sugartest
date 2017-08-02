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
 * RS-144
 * Prepare ForecastManagerWorksheetsFilter Api
 * @coversDefaultClass ForecastManagerWorksheetsFilterApi
 */
class ForecastManagerWorksheetsFilterApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var TimePeriod */
    protected $timeperiod = null;

    /** @var Quota */
    protected $quota = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        $this->quota = SugarTestQuotaUtilities::createQuota();
        $this->quota->user_id = $GLOBALS['current_user']->id;
        $this->quota->quota_type = 'Rollup';
        $this->quota->timeperiod_id = $this->timeperiod->id;
        $this->quota->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that createFilter method will receive right parameters after forecastManagerWorksheetsGet method call
     *
     * @dataProvider getDataForForecastManagerWorksheetsGet
     *
     * @param array $args
     * @param mixed $expectedUserId
     * @param mixed $expectedTimePeriodId
     * @param mixed $expectedType
     * @covers ::forecastManagerWorksheetsGet
     */
    public function testForecastManagerWorksheetsGet($args, $expectedUserId, $expectedTimePeriodId, $expectedType)
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', array('createFilter', 'filterList'));
        $api->expects($this->once())->method('createFilter')->with($this->equalTo($this->service), $this->equalTo($expectedUserId), $this->equalTo($expectedTimePeriodId));
        $api->forecastManagerWorksheetsGet($this->service, $args);
    }

    /**
     * Data Provider for testForecastManagerWorksheetsGet
     *
     * @return array
     */
    public function getDataForForecastManagerWorksheetsGet()
    {
        return array(
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                ),
                false,
                false,
                false,
            ),
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                    'user_id' => 1,
                    'timeperiod_id' => 2,
                ),
                1,
                2,
                3,
            ),
        );
    }

    /**
     * Test asserts that chart data has right structure if we pass user & time period
     * @covers ::forecastManagerWorksheetsChartGet
     */
    public function testForecastManagerWorksheetsChartGet()
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', array('ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers'));
        $api->expects($this->once())->method('ForecastManagerWorksheetsGet')->will($this->returnValue(array('records' => array())));
        $api->expects($this->once())->method('getDirectHierarchyUsers')->will($this->returnValue(array('records' => array(
                    array(
                        'id' => $GLOBALS['current_user']->id,
                        'full_name' => $GLOBALS['current_user']->full_name,
                    ),
                ))));
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, array(
                'timeperiod_id' => $this->timeperiod->id,
                'user_id' => $GLOBALS['current_user']->id,
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('data', $actual);
        $this->assertNotEmpty('data', $actual);
        $this->assertArrayHasKey('quota', $actual);
    }

    /**
     * Test asserts that chart data has right structure if we don't pass user & time period
     * @covers ::forecastManagerWorksheetsChartGet
     */
    public function testForecastManagerWorksheetsChartGetNoData()
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', array('ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers'));
        $api->expects($this->never())->method('ForecastManagerWorksheetsGet');
        $api->expects($this->never())->method('getDirectHierarchyUsers');
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, array(
                'no_data' => 1,
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('data', $actual);
        $this->assertArrayHasKey('quota', $actual);
    }

    /**
     * Test asserts that we have target_quota if we need that
     * @covers ::forecastManagerWorksheetsChartGet
     */
    public function testForecastManagerWorksheetsChartGetTargetQuota()
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', array('ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers'));
        $api->expects($this->never())->method('ForecastManagerWorksheetsGet');
        $api->expects($this->never())->method('getDirectHierarchyUsers');
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, array(
                'no_data' => 1,
                'target_quota' => 1,
                'timeperiod_id' => $this->timeperiod->id,
                'user_id' => $GLOBALS['current_user']->id,
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('target_quota', $actual);
        $this->assertEquals($this->quota->amount, $actual['target_quota']);
    }

    /**
     * We should get current_user if there are no parameters
     * @covers ::getDirectHierarchyUsers
     */
    public function testGetDirectHierarchyUsers()
    {
        $fields = array(
            'reports_to_id' => $GLOBALS['current_user']->id,
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);

        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', array(
                $this->service,
                array(),
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('records', $actual);
        $result = array();
        foreach ($actual['records'] as $value) {
            array_push($result, $value['id']);
        }
        $this->assertEquals(
            array($GLOBALS['current_user']->id, $user->id),
            $result,
            'Should contains 2 users',
            0,
            1,
            true
        );
    }

    /**
     * We should get exception if current_user isn't manager
     *
     * @expectedException SugarApiExceptionNotAuthorized
     * @covers ::getDirectHierarchyUsers
     */
    public function testGetDirectHierarchyUsersNotAuthorized()
    {
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', array(
                $this->service,
                array(),
            ));
    }

    /**
     * We should get custom user if current_user is manager and request him
     * @covers ::getDirectHierarchyUsers
     */
    public function testGetDirectHierarchyUsersCustomUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->reports_to_id = $GLOBALS['current_user']->id;
        $user->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', array(
                $this->service,
                array(
                    'user_id' => $user->id,
                ),
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('records', $actual);
        $record = reset($actual['records']);
        $this->assertNotEmpty($record);
        $this->assertEquals($user->id, $record['id']);
    }

    /**
     * We should get exception if custom user doesn't present in system
     *
     * @expectedException SugarApiExceptionInvalidParameter
     * @covers ::getDirectHierarchyUsers
     */
    public function testGetDirectHierarchyUsersCustomUserInvalidParameter()
    {
        $fields = array(
            'reports_to_id' => $GLOBALS['current_user']->id,
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', array(
                $this->service,
                array(
                    'user_id' => '-1',
                ),
            ));
    }

    /**
     * Test asserts that createFilter method will receive right parameters after filterList method call
     *
     * @dataProvider getDataForFilterList
     * @param array $args
     * @param mixed $expectedAssignedUser
     * @param mixed $expectedTimePeriod
     * @param mixed $expectedType
     * @covers ::filterList
     */
    public function testFilterList($args, $expectedAssignedUser, $expectedTimePeriod, $expectedType)
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', array('createFilter'));
        $api->expects($this->once())->method('createFilter')->with(
            $this->equalTo($this->service),
            $this->equalTo($expectedAssignedUser),
            $this->equalTo($expectedTimePeriod)
        );
        $api->filterList($this->service, $args);
    }

    /**
     * Data Provider for testFilterList
     *
     * @return array
     */
    public function getDataForFilterList()
    {
        return array(
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                ),
                false,
                false,
                false,
            ),
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                    'filter' => 15,
                ),
                false,
                false,
                false,
            ),
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                    'filter' => array(
                        array(
                            'assigned_user_id' => 1,
                        ),
                        array(
                            'timeperiod_id' => 2,
                        ),
                    ),
                ),
                1,
                2,
                3,
            ),
        );
    }

    /**
     * Test asserts that we have correct filter if there are no parameters
     * @covers::createFilter
     */
    public function testCreateFilter()
    {
        $fields = array(
            'reports_to_id' => $GLOBALS['current_user']->id,
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                false,
                false,
            ));
        $this->assertNotEmpty($actual);
        $result = array();
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('assigned_user_id', $result);
        $this->assertEquals($this->service->user->id, $result['assigned_user_id']);
        $this->assertArrayHasKey('draft', $result);
        $this->assertEquals(1, $result['draft']);
        $this->assertArrayHasKey('timeperiod_id', $result);
        $this->assertEquals(TimePeriod::getCurrentId(), $result['timeperiod_id']);
    }

    /**
     * We should get exception if current_user isn't manager
     *
     * @expectedException SugarApiExceptionNotAuthorized
     * @covers ::createFilter
     */
    public function testCreateFilterNotAuthorized()
    {
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                false,
                false,
            ));
    }

    /**
     * We should get customer user if current_user is manager
     * @covers ::createFilter
     */
    public function testCreateFilterCustomUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->reports_to_id = $GLOBALS['current_user']->id;
        $user->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                $user->id,
                false,
            ));
        $this->assertNotEmpty($actual);
        $result = array();
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('assigned_user_id', $result);
        $this->assertEquals($user->id, $result['assigned_user_id']);
    }

    /**
     * We should get exception if custom user isn't present in system
     *
     * @expectedException SugarApiExceptionInvalidParameter
     * @covers ::createFilter
     */
    public function testCreateFilterCustomUserInvalidParameter()
    {
        $fields = array(
            'reports_to_id' => $GLOBALS['current_user']->id,
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                '-1',
                false,
            ));
    }

    /**
     * We should get correct time period if it's passed
     * @covers ::createFilter
     */
    public function testCreateFilterTimePeriod()
    {
        $fields = array(
            'reports_to_id' => $GLOBALS['current_user']->id,
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                false,
                $this->timeperiod->id,
            ));
        $this->assertNotEmpty($actual);
        $result = array();
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('timeperiod_id', $result);
        $this->assertEquals($this->timeperiod->id, $result['timeperiod_id']);
    }

    /**
     * We should get exception if time period isn't present in system
     *
     * @expectedException SugarApiExceptionInvalidParameter
     * @covers ::createFilter
     */
    public function testCreateFilterTimePeriodInvalidParameter()
    {
        $fields = array(
            'reports_to_id' => $GLOBALS['current_user']->id,
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                false,
                create_guid(),
            ));
    }
}
