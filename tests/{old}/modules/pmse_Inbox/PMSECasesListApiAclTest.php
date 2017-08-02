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

require_once 'tests/{old}/SugarTestACLUtilities.php';

use Sugarcrm\Sugarcrm\ProcessManager;

/**
 * Unit test class to cover ACL testing for Advanced Workflow Apis
 */
class PMSECasesListApiActTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSECasesListApi = ProcessManager\Factory::getPMSEObject('PMSECasesListApi');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(array('acl' => 'adminOrDev'));
    }

    public function tearDown()
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testSelectCasesList()
    {
        $this->PMSECasesListApi->selectCasesList($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testSelectLogLoad()
    {
        $this->PMSECasesListApi->selectLogLoad($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testClearLog()
    {
        $this->PMSECasesListApi->clearLog($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testConfigLogLoad()
    {
        $this->PMSECasesListApi->configLogLoad($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testConfigLogPut()
    {
        $this->PMSECasesListApi->configLogPut($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testReturnProcessUsersChart()
    {
        $this->PMSECasesListApi->returnProcessUsersChart($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testReturnProcessStatusChart()
    {
        $this->PMSECasesListApi->returnProcessStatusChart($this->api, array('module' => 'pmse_Inbox'));
    }

    /*
     * Check if valid user is allowed to pass ACL access
     */
    public function testReturnProcessUsersChartValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;

        $pmseCasesListApi = $this->getMockBuilder('PMSECasesListApi')
            ->setMethods(array('createProcessUsersChartData'))
            ->getMock();
        $pmseCasesListApi
            ->expects($this->any())
            ->method('createProcessUsersChartData')
            ->will($this->returnValue('testPassed'));

        $ret = $pmseCasesListApi->returnProcessUsersChart(
            $this->api,
            array('module' => 'pmse_Inbox', 'record' => 'dummy')
        );

        $this->assertEquals($ret, "testPassed", "ACL access test failed for returnProcessUsersChart");

    }
}
