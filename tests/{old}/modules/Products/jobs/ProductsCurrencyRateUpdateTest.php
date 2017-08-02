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

class ProductsCurrencyRateUpdateTest extends Sugar_PHPUnit_Framework_TestCase
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
        $this->mock = $this->createPartialMock('ProductsCurrencyRateUpdate', array('getProductsWithNonClosedQuote'));
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

    public function testDoCustomUpdateRate()
    {
        $this->markTestIncomplete('[BR-3362] Query Spy doesn\'t capture parameters in prepared statements');

        $this->mock->expects($this->once())
            ->method('getProductsWithNonClosedQuote')
            ->will($this->returnValue(array('id1', 'id2')));

        $this->db->addQuerySpy(
            'get_rate',
            array("/SELECT currencies.conversion_rate/", "/currencies.deleted = 0/", "/currencies.id = 'abc'/"),
            array(array('1.234'))
        );

        $this->db->addQuerySpy(
            'rate_update',
            "/UPDATE mytable SET mycolumn = 1\.234/",
            array(array(1))
        );

        // run our tests with mockup data
        $result = $this->mock->doCustomUpdateRate('mytable', 'mycolumn', 'abc');
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->getQuerySpyRunCount('get_rate'));
        $this->assertEquals(1, $this->db->getQuerySpyRunCount('rate_update'));
    }

    public function testDoCustomUpdateUsDollarRate()
    {
        $this->mock->expects($this->once())
            ->method('getProductsWithNonClosedQuote')
            ->will($this->returnValue(array('id1', 'id2')));

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
