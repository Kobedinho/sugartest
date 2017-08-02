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
 * Class ForecastsApiTest
 *
 * @coversDefaultClass ForecastsApi
 */
class ForecastsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $api;

    public function setUp()
    {
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        unset($this->api);
        SugarTestHelper::tearDown();
    }


    /**
     * @expectedException SugarApiExceptionInvalidHash
     * @covers ::compareSettingsToDefaults
     */
    public function testCompareConfigsThrowsException()
    {
        $adm = $this->getMockBuilder('Administration')->setMethods(array('save'))->getMock();
        $adm->expects($this->any())
            ->method('save');

        $obj = new ForecastsApi();

        SugarTestReflection::callProtectedMethod(
            $obj,
            'compareSettingsToDefaults',
            array($adm, array('two' => 'one'), $this->api)
        );
    }

    /**
     * @covers ::compareSettingsToDefaults
     */
    public function testCompareConfigsDoestThrowsException()
    {
        $adm = $this->getMockBuilder('Administration')->setMethods(array('save'))->getMock();
        $adm->expects($this->any())
            ->method('save');

        $obj = new ForecastsApi();

        SugarTestReflection::callProtectedMethod(
            $obj,
            'compareSettingsToDefaults',
            array($adm, ForecastsDefaults::getDefaults(), $this->api)
        );

        $this->assertTrue(true);
    }

    /**
     * @covers ::returnEmptySet
     */
    public function testReturnEmptySet()
    {
        $obj = new ForecastsApi();

        $return = $obj->returnEmptySet($this->api, array());

        $expected = array('next_offset' => -1, 'records' => array());

        $this->assertSame($expected, $return);
    }

    /**
     * @covers ::retrieveSelectedUser
     */
    public function testRetrieveSelectedUser()
    {
        $user = $this->getMockBuilder('User')
            ->setMethods(array('save'))
            ->getMock();

        $user->id = 'test-id';
        $user->user_name = 'testuser';
        $user->full_name = 'Test User';
        $user->first_name = 'Test';
        $user->last_name = 'User';
        $user->reports_to_id = 'test-manager-user';
        $user->reports_to_name = ' Manager User';

        BeanFactory::registerBean($user);

        $obj = new ForecastsApi();

        $return = $obj->retrieveSelectedUser($this->api, array('user_id' => 'test-id'));

        $expected = Array(
            'id' => 'test-id',
            'user_name' => 'testuser',
            'full_name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'reports_to_id' => 'test-manager-user',
            'reports_to_name' => ' Manager User',
            'is_manager' => false,
            'is_top_level_manager' => false
        );

        $this->assertSame($expected, $return);

        BeanFactory::unregisterBean($user);
    }

    /**
     * @covers ::getTimeperiodFilterClass
     */
    public function testGetTimeperiodFilterClass()
    {
        $api = new ForecastsApi();
        $klass = SugarTestReflection::callProtectedMethod($api, 'getTimeperiodFilterClass', array(array()));
        $this->assertInstanceOf('SugarForecasting_Filter_TimePeriodFilter', $klass);
    }

    /**
     * @covers ::getTimeperiodFilterClass
     */
    public function testGetTimeperiodFilterClassReturnsCustomClass()
    {
        SugarAutoLoader::load('include/SugarForecasting/Filter/TimePeriodFilter.php');
        $file = <<<FILE
<?php
class CustomSugarForecasting_Filter_TimePeriodFilter {}
FILE;
        sugar_file_put_contents('custom/include/SugarForecasting/Filter/TimePeriodFilter.php', $file);
        $api = new ForecastsApi();
        $klass = SugarTestReflection::callProtectedMethod($api, 'getTimeperiodFilterClass', array(array()));
        $this->assertInstanceOf('CustomSugarForecasting_Filter_TimePeriodFilter', $klass);
        SugarAutoLoader::unlink('custom/include/SugarForecasting/Filter/TimePeriodFilter.php');
    }

    /**
     * @covers ::timeperiod
     */
    public function testTimeperiod()
    {
        SugarAutoLoader::load('include/SugarForecasting/Filter/TimePeriodFilter.php');
        $class = $this->getMockBuilder('SugarForecasting_Filter_TimePeriodFilter')
            ->disableOriginalConstructor()
            ->setMethods(array('process'))
            ->getMock();
        $class->expects($this->once())
            ->method('process');
        $fw_api = $this->getMockBuilder('ForecastsApi')
            ->setMethods(array('getTimeperiodFilterClass'))
            ->getMock();
        $fw_api->expects($this->once())
            ->method('getTimeperiodFilterClass')
            ->willReturn($class);
        $fw_api->timeperiod($this->api, array());
    }

    /**
     * @covers ::getQuota
     */
    public function testGetQuota()
    {
        $quota = $this->getMockBuilder('Quota')
            ->setMethods(array('getRollupQuota'))
            ->getMock();

        $quota->expects($this->once())
            ->method('getRollupQuota')
            ->with('test-timeperiod', 'test-user-id', false)
            ->willReturn(array('quota' => '500'));

        $fw = $this->getMockBuilder('ForecastsApi')
            ->setMethods(array('getBean'))
            ->getMock();
        $fw->expects($this->once())
            ->method('getBean')
            ->with('Quotas')
            ->willReturn($quota);

        $return = $fw->getQuota(
            $this->api,
            array(
                'quota_type' => 'direct',
                'timeperiod_id' => 'test-timeperiod',
                'user_id' => 'test-user-id'
            )
        );

        $this->assertSame(
            array(
                'quota' => '500',
                'is_top_level_manager' => false
            ),
            $return
        );
    }

    /**
     * @covers ForecastsApi::forecastsInitialization
     */
    public function testForecastsInitialization()
    {
        SugarTestHelper::setUp('current_user');
        $admin = $this->getMockBuilder('Administration')
            ->setMethods(array('getConfigForModule'))
            ->getMock();

        $admin->expects($this->once())
            ->method('getConfigForModule')
            ->with('Forecasts', 'base', true)
            ->willReturn(
                array(
                    'is_setup' => 1,
                    'commit_stages_included' => array('include'),
                    'timeperiod_leaf_interval' => 'Quarter'
                )
            );

        $fw = $this->getMockBuilder('ForecastsApi')
            ->setMethods(array('getBean', 'compareSettingsToDefaults'))
            ->getMock();
        $fw->expects($this->once())
            ->method('getBean')
            ->with('Administration')
            ->willReturn($admin);

        $GLOBALS['current_user']->last_name = 'Unit';

        $ret = $fw->forecastsInitialization($this->api, array());

        $expected = Array(
            'initData' => Array(
                'userData' => Array(
                    'showOpps' => false,
                    'first_name' => 'SugarUser',
                    'last_name' => 'Unit',
                ),
                'forecasts_setup' => 1
            ),
            'defaultSelections' => Array(
                'timeperiod_id' => Array(
                    'id' => '',
                    'label' => '',
                    'start' => '',
                    'end' => '',
                ),
                'ranges' => Array(
                    0 => 'include'
                ),
                'group_by' => 'forecast',
                'dataset' => 'likely',
            )
        );

        $this->assertSame($expected, $ret);

        Forecast::$settings = array();
    }
}
