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
 * Test ReportsDashlets Api
 */
class ReportsDashletsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var ReportsDashletsApi */
    protected $api = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('app_list_strings');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new ReportsDashletsApi();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test assserts that getSavedReports returns data
     */
    public function testGetSavedReports()
    {
        $args = array();
        $actual = $this->api->getSavedReports($this->service, $args);
        $this->assertNotEmpty($actual);
    }

    /**
     * Test assserts that getSavedReports with has_charts flag returns data
     */
    public function testGetSavedReportsHasChart()
    {
        $args = array(
            'has_charts' => 'true',
        );
        $actual = $this->api->getSavedReports($this->service, $args);
        $this->assertNotEmpty($actual);

        return reset($actual);
    }

    /**
     * Test asserts that testGetSavedReportChartById returns data for report with chart
     *
     * @depends testGetSavedReportsHasChart
     */
    public function testGetSavedReportChartById($report)
    {
        $args = array(
            'reportId' => $report['id'],
        );
        $actual = $this->api->getSavedReportChartById($this->service, $args);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('chartData', $actual);
        $this->assertArrayHasKey('reportData', $actual);
    }

    /**
     * Test asserts that GetSavedReports returns empty array if no access
     */
    public function testGetSavedReports_NoContent_ReturnsEmptyArray()
    {
        $mockSavedReport = $this->createPartialMock('SavedReport', array("ACLAccess"));
        $mockSavedReport->method("ACLAccess")->will($this->returnValue(false));

        $mockApiClass = $this->createPartialMock('ReportsDashletsApi', array("getSavedReportFromData"));
        $mockApiClass->method("getSavedReportFromData")->will($this->returnValue($mockSavedReport));

        $this->assertEmpty($mockApiClass->getSavedReports($this->service, array()), "No reports should be returned");
    }

    /**
     * Test asserts that testGetSavedReportChartById throws exception if no access
     */
    public function testGetSavedReportChartById_NoAccess_ThrowsException()
    {
        $mockSavedReport = $this->createPartialMock('SavedReport', array("ACLAccess"));
        $mockSavedReport->method("ACLAccess")->will($this->returnValue(false));

        $mockApiClass =  $this->createPartialMock('ReportsDashletsApi', array("getSavedReportById"));
        $mockApiClass->method("getSavedReportById")->will($this->returnValue($mockSavedReport));

        $this->setExpectedException("SugarApiExceptionNotAuthorized");
        $mockApiClass->getSavedReportChartById($this->service, array('reportId'=>'1234-4567-8888-9999'));
    }
}
