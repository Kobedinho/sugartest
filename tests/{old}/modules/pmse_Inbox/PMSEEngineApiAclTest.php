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
class PMSEEngineApiActTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEEngineApi = ProcessManager\Factory::getPMSEObject('PMSEEngineApi');
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
    public function testReactivateFlows()
    {
        $this->PMSEEngineApi->reactivateFlows($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testReassignFlows()
    {
        $this->PMSEEngineApi->reassignFlows($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetReassignFlows()
    {
        $this->PMSEEngineApi->getReassignFlows($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testSelectCase()
    {
        $this->PMSEEngineApi->selectCase($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testCancelCase()
    {
        $this->PMSEEngineApi->cancelCase($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetUnattendedCases()
    {
        $this->PMSEEngineApi->getUnattendedCases($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetSettingsEngine()
    {
        $this->PMSEEngineApi->getSettingsEngine($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testPutSettingsEngine()
    {
        $this->PMSEEngineApi->putSettingsEngine($this->api, array('module' => 'pmse_Inbox'));
    }

    /*
     * Check if valid user is allowed to pass ACL access
     */
    public function testGetSettingsEngineValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;
        $ret = $this->PMSEEngineApi->getSettingsEngine($this->api, array('module' => 'pmse_Inbox'));
        $this->assertTrue(is_array($ret), "ACL access test failed for getSettingsEngine");
    }
}
