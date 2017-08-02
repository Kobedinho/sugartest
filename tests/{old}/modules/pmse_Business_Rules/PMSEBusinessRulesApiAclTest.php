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
class PMSEBusinessRulesApiAclTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEBusinessRules = ProcessManager\Factory::getPMSEObject('PMSEBusinessRules');
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
    public function testBusinessRuleDownload()
    {
        $this->PMSEBusinessRules->businessRuleDownload(
            $this->api,
            array('module' => 'pmse_Business_Rules')
        );
    }


    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testBusinessRulesImport()
    {
        $this->PMSEBusinessRules->businessRulesImport($this->api, array('module' => 'pmse_Business_Rules'));
    }

    /*
     * Check if valid user is allowed to pass ACL access
     */
    public function testBusinessRuleDownloadValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;

        $pmseBusinessRuleExporter = $this->getMockBuilder('PMSEBusinessRuleExporter')
                                         ->setMethods(array('exportProject'))
                                         ->getMock();
        $pmseBusinessRuleExporter
            ->expects($this->any())
            ->method('exportProject')
            ->will($this->returnValue('testPassed'));

        $pmseBusinessRulesApi = $this->getMockBuilder('PMSEBusinessRules')
                                     ->setMethods(array('getPMSEBusinessRuleExporter'))
                                     ->getMock();
        $pmseBusinessRulesApi
            ->expects($this->any())
            ->method('getPMSEBusinessRuleExporter')
            ->will($this->returnValue($pmseBusinessRuleExporter));

        $ret = $pmseBusinessRulesApi->businessRuleDownload(
            $this->api,
            array('module' => 'pmse_Business_Rules', 'record' => 'dummy')
        );

        $this->assertEquals($ret, "testPassed", "ACL access test failed for businessRuleDownload");


    }

}
