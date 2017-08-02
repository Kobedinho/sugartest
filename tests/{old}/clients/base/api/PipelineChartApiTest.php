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



class PipelineChartApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $reportee;

    /**
     * @var array
     */
    protected static $manager;

    /**
     * @var array
     */
    protected static $manager2;

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var array
     */
    protected static $managerData;

    /**
     * @var Administration
     */
    protected static $admin;

    /**
     * @var ServiceBase
     */
    protected $service;


    public function setUp()
    {
        $this->service = $this->createPartialMock(
            'ServiceBase',
            array('execute', 'handleException')
        );
    }

    /**
     * Utility Method to setup a mock Pipeline API
     *
     * @param Array $methods
     * @return PipelineChartApi
     */
    protected function getMockPipelineApi(array $methods = array('loadBean'))
    {
        $api = $this->getMockBuilder('PipelineChartApi')
            ->setMethods($methods)
            ->getMock();

        return $api;
    }

    /**
     * @expectedException SugarApiExceptionNotFound
     */
    public function testNotFoundExceptionThrownWithInvalidModule()
    {
        $api = $this->getMockPipelineApi();

        $api->pipeline($this->service, array('module' => 'MyInvalidModule'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testNotAuthorizedThrownWhenACLAccessDenied()
    {
        $api = $this->getMockPipelineApi(array('loadBean'));

        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save', 'ACLAccess'))
            ->getMock();

        $rli->expects($this->once())
            ->method('ACLAccess')
            ->with('view')
            ->will($this->returnValue(false));

        $api->expects($this->once())
            ->method('loadBean')
            ->will($this->returnValue($rli));

        $api->pipeline($this->service, array('module' => 'RevenueLineItems'));
    }

    public function testBuildQueryContainsAmountField()
    {
        $api = $this->getMockPipelineApi();
        $tp = $this->getMockBuilder('TimePeriod')
            ->setMethods(array('save'))
            ->getMock();
        $tp->start_date_timestamp = 1;
        $tp->end_date_timestamp = 2;

        $seed = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $user = $this->getMockBuilder('User')
            ->setMethods(array('save'))
            ->getMock();
        $user->id = 'test';

        $this->service->user = $user;

        $sq = SugarTestReflection::callProtectedMethod(
            $api,
            'buildQuery',
            array($this->service, $seed, $tp, 'likely_case', 'user')
        );
        /* @var $sq SugarQuery */
        $sql = $sq->compile()->getSQL();

        $this->assertContains('likely_case', $sql);
    }

    public function testBuildQueryContainsAllReportingUsers()
    {
        global $db;

        $this->markTestIncomplete('[BR-3362] Testing SQL doesn\'t work with prepared statements');

        $api = $this->getMockPipelineApi(array('getReportingUsers'));
        $api->expects($this->once())
            ->method('getReportingUsers')
            ->with('test')
            ->will($this->returnValue(array('1', '2')));

        $tp = $this->getMockBuilder('TimePeriod')
            ->setMethods(array('save'))
            ->getMock();
        $tp->start_date_timestamp = 1;
        $tp->end_date_timestamp = 2;

        $seed = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $user = $this->getMockBuilder('User')
            ->setMethods(array('save'))
            ->getMock();
        $user->id = 'test';

        $this->service->user = $user;

        $sq = SugarTestReflection::callProtectedMethod(
            $api,
            'buildQuery',
            array($this->service, $seed, $tp, 'likely_case', 'group')
        );
        /* @var $sq SugarQuery */
        $sql = $sq->compile()->getSQL();

        $this->assertContains("('test','1','2')", $sql);
    }

    public function testPipelineReturnsCorrectData()
    {
        $api = $this->getMockPipelineApi(array('getForecastSettings', 'buildQuery', 'loadBean', 'getTimeperiod'));
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save', 'ACLAccess'))
            ->getMock();

        $rli->expects($this->once())
            ->method('ACLAccess')
            ->with('view')
            ->will($this->returnValue(true));

        $GLOBALS['current_language'] = 'en_us';
        $rli->module_name = 'RevenueLineItems';

        $api->expects($this->once())
            ->method('loadBean')
            ->will($this->returnValue($rli));

        $api->expects($this->once())
            ->method('getForecastSettings')
            ->will(
                $this->returnValue(
                    array(
                        'sales_stage_won' => array('Closed Won'),
                        'sales_stage_lost' => array('Closed Lost'),
                        'is_setup' => 0
                    )
                )
            );

        /**
         * 'Prospecting' => 'Prospecting',
         * 'Qualification' => 'Qualification',
         */

        $data = array(
            array(
                'id' => 'test1',
                'sales_stage' => 'Prospecting',
                'likely_case' => '100.00',
                'base_rate' => '1.0'
            ),
            array(
                'id' => 'test2',
                'sales_stage' => 'Prospecting',
                'likely_case' => '150.00',
                'base_rate' => '1.0'
            ),
            array(
                'id' => 'test3',
                'sales_stage' => 'Qualification',
                'likely_case' => '100.00',
                'base_rate' => '1.0'
            ),
            array(
                'id' => 'test4',
                'sales_stage' => 'Qualification',
                'likely_case' => '150.00',
                'base_rate' => '1.0'
            )
        );


        $sq = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('execute'))
            ->getMock();
        $sq->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($data));

        $api->expects($this->once())
            ->method('buildQuery')
            ->will($this->returnValue($sq));

        $api->expects($this->once())
            ->method('getTimeperiod')
            ->will($this->returnValue(''));

        $data = $api->pipeline(
            $this->service,
            array(
                'module' => 'RevenueLineItems',
                'timeperiod_id' => '',
            )
        );

        // check the properties
        $this->assertEquals('500', $data['properties']['total']);

        // lets check the data, there should be two
        $this->assertEquals(2, count($data['data']));

        // each item should be 250 and have 2 items
        foreach ($data['data'] as $item) {
            $this->assertEquals(2, $item['count']);
            $this->assertEquals(250, $item['values'][0]['value']);
        }
    }
}
