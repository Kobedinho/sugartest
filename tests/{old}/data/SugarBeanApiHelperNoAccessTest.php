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
 * @group ApiTests
 */
class SugarBeanApiHelperNoAccessTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $bean;
    public $beanApiHelper;
    public $apiMock;

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
        // Mocking out SugarBean to avoid having to deal with any dependencies other than those that we need for this test
        $mock = $this->createMock('SugarBean');
        $mock->id = 'SugarBeanApiHelperMockBean-1';
        $mock->favorite = false;
        $mock->module_name = 'Test';
        $mock->module_dir = 'Test';
        $mock->field_defs = array(
                'testInt' => array(
                    'type' => 'int',
                ),
                'testDecimal' => array(
                    'type' => 'decimal'
                ),
                'testBool' => array(
                    'type' => 'bool'
                ),
            );
        $mock->expects($this->any())
             ->method('ACLFieldAccess')
             ->will($this->returnValue(false));
        $this->bean = $mock;

        $this->apiMock = new SugarBeanApiHelperNoAccessTest_ServiceMockup();
        $this->apiMock->user = $GLOBALS['current_user'];
        $this->beanApiHelper = new SugarBeanApiHelper($this->apiMock);
    }

    protected function tearDown()
    {
        unset($_SESSION['ACL']);
        SugarTestHelper::tearDown();
    }

    public function testNoEmail1FieldAccess()
    {
        $this->bean->field_defs['email'] = array('type' => 'email');
        $this->bean->field_defs['email1'] = array('type' => 'varchar');
        $this->bean->emailAddress = array();
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Test']['fields']['email1'] = SugarACL::ACL_NO_ACCESS;
        $this->beanApiHelper->formatForApi($this->bean, array('email', 'email1'));
        $this->assertTrue(!isset($data['email']));
    }

    public function testNoEmail1FieldAccessSave()
    {
        $this->setExpectedException(
          'SugarApiExceptionNotAuthorized', 'Not allowed to edit field email in module: Test'
        );
        $this->bean->field_defs['email'] = array('type' => 'email');
        $this->bean->field_defs['email1'] = array('type' => 'varchar');
        $this->bean->emailAddress = array();
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Test']['fields']['email1'] = SugarACL::ACL_NO_ACCESS;
        $data['email'] = 'test@test.com';
        $data['module'] = 'Test';
        $this->beanApiHelper->populateFromApi($this->bean, $data);

    }

}

class SugarBeanApiHelperNoAccessTest_ServiceMockup extends ServiceBase
{
    public function execute() 
    {

    }

    protected function handleException(Exception $exception) 
    {

    }
}
