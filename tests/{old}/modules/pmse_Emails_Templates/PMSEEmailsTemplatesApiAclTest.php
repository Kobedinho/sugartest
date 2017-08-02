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
class PMSEEmailsTemplatesApiAclTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEEmailsTemplates = ProcessManager\Factory::getPMSEObject('PMSEEmailsTemplates');
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
    public function testEmailTemplateDownload()
    {
        $this->PMSEEmailsTemplates->emailTemplateDownload(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
    }

   /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testFindVariables()
    {
        $this->PMSEEmailsTemplates->findVariables(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testRetrieveRelatedBeans()
    {
        $this->PMSEEmailsTemplates->retrieveRelatedBeans(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testEmailTemplatesImport()
    {
        $this->PMSEEmailsTemplates->emailTemplatesImport(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
    }

    /*
     * Check if valid user is allowed to pass ACL access
     */
    public function testFindVariablesValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;
        $ret = $this->PMSEEmailsTemplates->findVariables(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
        $this->assertTrue(is_array($ret), "ACL access test failed for findVariables");
    }

}
