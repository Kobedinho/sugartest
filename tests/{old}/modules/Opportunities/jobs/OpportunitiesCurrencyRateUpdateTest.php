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

class OpportunitiesCurrencyRateUpdateTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarTestDatabaseMock
     */
    private $db;
    private $mock;

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        $this->db = SugarTestHelper::setUp('mock_db');
        $this->setupMockClass();
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        $this->tearDownMockClass();
        parent::tearDown();
    }

    /**
     * setup the mock class and override getClosedStages to return a static array for the test
     */
    public function setupMockClass()
    {
        $this->mock = $this->createPartialMock('OpportunitiesCurrencyRateUpdate', array('getClosedStages'));
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));
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
        // setup the query strings we are expecting and what they should return
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
}
