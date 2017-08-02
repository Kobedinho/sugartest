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
 * RS-173: Prepare DashboardList Api
 */
class RS173Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DashboardListApi
     */
    protected $dashboardListApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->dashboardListApi = new DashboardListApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        for($i = 0; $i < 5; $i++) {
            SugarTestDashboardUtilities::createDashboard('', array('name' => 'SugarDashboardHome'));
            SugarTestDashboardUtilities::createDashboard('', array('dashboard_module' => 'Accounts', 'name' => 'SugarDashboardAccounts'));
        }
    }

    public function tearDown()
    {
        SugarTestDashboardUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }


    /**
     * Test asserts behavior of get dashboards for module
     */
    public function testGetDashboardsForModule()
    {

        $result = $this->dashboardListApi->getDashboards($this->serviceMock, array(
            'module' => 'Accounts',
            'max_num' => '3',
        ));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('records', $result);
        $this->assertArrayHasKey('next_offset', $result);
        $this->assertEquals(3, count($result['records']), 'Returned too many results');

        foreach($result['records'] as $record) {
            $this->assertEquals('SugarDashboardAccounts', $record['name']);
        }
    }

    /**
     * Test asserts behavior of get dashboards for Home
     */
    public function testGetDashboardsForHome()
    {
        $result = $this->dashboardListApi->getDashboards($this->serviceMock, array(
            'max_num' => '3',
        ));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('records', $result);
        $this->assertArrayHasKey('next_offset', $result);
        $this->assertEquals(3, count($result['records']), 'Returned too many results');

        foreach($result['records'] as $record) {
            $this->assertEquals('SugarDashboardHome', $record['name']);
        }
    }
}
