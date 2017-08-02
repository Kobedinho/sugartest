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

require_once 'tests/{old}/SugarTestDatabaseMock.php';

class RevenueLineItemsCurrencyRateUpdateTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarTestDatabaseMock
     */
    private $db;

    /**
     * @var RevenueLineItemsCurrencyRateUpdate
     */
    private $mock;

    public function setUp()
    {
        $this->db = SugarTestHelper::setUp('mock_db');
        $this->setupMockClass();
        SugarTestHelper::setUp('app_strings');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->tearDownMockClass();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * setup the mock class and override getClosedStages to return a static array for the test
     */
    public function setupMockClass()
    {
        $this->mock = $this->createPartialMock('RevenueLineItemsCurrencyRateUpdate', array(
            'getClosedStages',
            'getOpportunityConfig',
        ));
        // we want to use our mock database for these tests, so replace it
        SugarTestReflection::setProtectedValue($this->mock, 'db', $this->db);
    }

    /**
     * tear down mock class
     */
    public function tearDownMockClass()
    {
        unset($this->mock);
    }

    /**
     * @group opportunities
     */
    public function testDoCustomUpdateRate()
    {
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));

        $this->db->addQuerySpy(
            'get_rate',
            "/SELECT conversion_rate FROM currencies WHERE id = 'abc'/",
            array(array('1.234'))
        );

        $this->db->addQuerySpy(
            'rate_update',
            "/UPDATE mytable SET mycolumn = '1\.234'/",
            array(array(1))
        );

        // run our tests with mockup data
        $result = $this->mock->doCustomUpdateRate('mytable', 'mycolumn', 'abc');
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->getQuerySpyRunCount('get_rate'));
        $this->assertEquals(1, $this->db->getQuerySpyRunCount('rate_update'));
    }

    /**
     * @group opportunities
     */
    public function testDoCustomUpdateUsDollarRate()
    {
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));

        // setup the query strings we are expecting and what they should return
        $this->db->addQuerySpy(
            'rate_update',
            "/UPDATE mytable SET amount_usdollar = 1\.234 \/ base_rate/",
            array(array(1))
        );

        // run our tests with mockup data
        $result = $this->mock->doCustomUpdateUsDollarRate('mytable', 'amount_usdollar', '1.234', 'abc');
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->getQuerySpyRunCount('rate_update'));
    }

    /**
     * @group opportunities
     */
    public function testDoPostUpdateAction()
    {
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));

        $this->mock->expects($this->any())
            ->method('getOpportunityConfig')
            ->will($this->returnValue(array(
                'opps_view_by' => 'RevenueLineItems',
            )));

        // setup the query strings we are expecting and what they should return
        $this->db->addQuerySpy(
            'post_select',
            "/SELECT opportunity_id/",
            array(
                array('likely'=>'1000', 'best'=>'1000', 'worst'=>'1000', 'opp_id'=>'abc123'),
                array('likely'=>'2000', 'best'=>'2000', 'worst'=>'2000', 'opp_id'=>'abc123'),
            )
        );

        $this->db->addQuerySpy(
            'post_update',
            "/UPDATE opportunities/"
        );
        // run our tests with mockup data
        $result = $this->mock->doPostUpdateAction();
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->getQuerySpyRunCount('post_select'));
        $this->assertGreaterThan(0, $this->db->getQuerySpyRunCount('post_update'));
    }
}
