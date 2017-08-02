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
 * @group forecastapi
 * @group forecasts
 * @covers ForecastsChartApi
 */
class ForecastsChartApiTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected static $users = [
        ['id' => '6d01426a-99d1-11e6-9836-9801a7ade577', 'createReportUser' => false],
        ['id' => '6d014904-99d1-11e6-a9d3-9801a7ade577', 'createReportUser' => true],
    ];

    public static function setUpBeforeClass()
    {
        foreach (self::$users as $userData) {
            if ($userData['createReportUser']) {
                SugarTestUserUtilities::createAnonymousUser(true, 0, [
                    'status' => 'Active',
                    'deleted' => 0,
                    'reports_to_id' => $userData['id'],
                ]);
            }
        }
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @param string $_file
     * @param string $_klass
     * @covers ForecastsChartApi::getClass
     * @dataProvider dataProviderGetClass
     */
    public function testGetClass($_file, $_klass)
    {
        $api = new ForecastsChartApi();
        $klass = SugarTestReflection::callProtectedMethod($api, 'getClass', array($_file, $_klass, array()));
        $this->assertInstanceOf($_klass, $klass);
    }
    /**
     * @param string $_file
     * @param string $_klass
     * @covers ForecastsChartApi::getClass
     * @dataProvider dataProviderGetClass
     */
    public function testGetClassReturnsCustomClass($_file, $_klass)
    {
        SugarAutoLoader::load($_file);
        $file = <<<FILE
<?php
class Custom$_klass {}
FILE;
        sugar_file_put_contents('custom/' . $_file, $file);
        $api = new ForecastsChartApi();
        $klass = SugarTestReflection::callProtectedMethod($api, 'getClass', array($_file, $_klass, array()));
        $this->assertInstanceOf('Custom' . $_klass, $klass);
        SugarAutoLoader::unlink('custom/' . $_file);
    }

    /**
     * @param string $userId
     * @param string $_file
     * @param string $_klass
     * @param int $display_manager
     * @covers ForecastsChartApi::chart
     * @dataProvider dataProviderChart
     */
    public function testChart($userId, $_file, $_klass, $display_manager)
    {
        $api = $this->getMockBuilder('ForecastsChartApi')
            ->setMethods(array('getClass'))
            ->getMock();

        $user = $this->getMockBuilder('User')
            ->setMethods(array('save'))
            ->disableOriginalConstructor()
            ->getMock();

        $user->id = $userId;

        $args = array(
            'timeperiod_id' => 'test-timeperiod-id',
            'user_id' => 'test-user-id',
            'display_manager' => $display_manager
        );

        SugarAutoLoader::load($_file);
        $klass = $this->getMockBuilder($_klass)
            ->setMethods(array('process'))
            ->disableOriginalConstructor()
            ->getMock();

        $klass->expects($this->once())
            ->method('process')
            ->willReturn(true);

        $api->expects($this->once())
            ->method('getClass')
            ->with($_file, $_klass)
            ->willReturn($klass);


        $this->assertTrue($api->chart(SugarTestRestUtilities::getRestServiceMock($user), $args));
    }

    public static function dataProviderChart()
    {
        return array(
            array(
                self::$users[0]['id'],
                'include/SugarForecasting/Chart/Individual.php',
                'SugarForecasting_Chart_Individual',
                0,
            ),
            array(
                self::$users[1]['id'],
                'include/SugarForecasting/Chart/Manager.php',
                'SugarForecasting_Chart_Manager',
                1,
            ),
        );
    }

    public static function dataProviderGetClass()
    {
        return array(
            array(
                'include/SugarForecasting/Chart/Individual.php',
                'SugarForecasting_Chart_Individual'
            ),
            array(
                'include/SugarForecasting/Chart/Manager.php',
                'SugarForecasting_Chart_Manager'
            ),
        );
    }

}
