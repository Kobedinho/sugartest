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
class PMSEProjectApiAclTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEProjectApi = ProcessManager\Factory::getPMSEObject('PMSEProjectApi');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(array('acl' => array()));
    }

    public function tearDown()
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testRetrieveCustomProject()
    {
        $this->PMSEProjectApi->retrieveCustomProject($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testUpdateCustomProject()
    {
        $this->PMSEProjectApi->updateCustomProject($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetCrmData()
    {
        $this->PMSEProjectApi->getCrmData($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testPutCrmData()
    {
        $this->PMSEProjectApi->putCrmData($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetActivityDefinition()
    {
        $this->PMSEProjectApi->getActivityDefinition($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testPutActivityDefinition()
    {
        $this->PMSEProjectApi->putActivityDefinition($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetEventDefinition()
    {
        $this->PMSEProjectApi->getEventDefinition($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testPutEventDefinition()
    {
        $this->PMSEProjectApi->putEventDefinition($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetGatewayDefinition()
    {
        $this->PMSEProjectApi->getGatewayDefinition($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testPutGatewayDefinition()
    {
        $this->PMSEProjectApi->putGatewayDefinition($this->api, array('module' => 'pmse_Project'));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testVerifyRunningProcess()
    {
        $this->PMSEProjectApi->verifyRunningProcess($this->api, array('module' => 'pmse_Project'));
    }
}
