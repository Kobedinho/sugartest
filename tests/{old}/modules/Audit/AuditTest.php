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

class AuditTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected $bean = null;

    /**
     * @var SugarTestDatabaseMock
     */
    public static $db;

    /**
     * Beans registered through BeanFactory
     * @var array
     */
    private $registeredBeans = [];

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $GLOBALS['current_user'] = BeanFactory::newBean('Users');
        self::$db = SugarTestHelper::setUp('mock_db');
    }

    public static function tearDownAfterClass()
    {
        $GLOBALS['current_user'] = null;
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');

        $this->bean = BeanFactory::newBean('Leads');
        $this->bean->name = 'Test';
        $this->bean->id = '1';
    }

    private function registerBean(\SugarBean $bean)
    {
        BeanFactory::registerBean($bean);
        $this->registeredBeans[] = $bean;
    }

    public function tearDown()
    {
        unset($this->bean);
        foreach ($this->registeredBeans as $bean) {
            BeanFactory::unregisterBean($bean);
        }
        parent::tearDown();
    }

    public function testGetAuditLog()
    {
        global $timedate;
        $auditTable = $this->bean->get_audit_table_name();
        $dateCreated = date('Y-m-d H:i:s');
        self::$db->addQuerySpy(
            'auditQuery',
            '/' . $auditTable . '/',
            array(
                array(
                    'field_name' => 'name',
                    'date_created' => $dateCreated,
                    'before_value_string' => 'Test',
                    'after_value_string' => 'Awesome',
                    'before_value_text' => '',
                    'after_value_text' => '',
                ),
            )
        );
        $audit = BeanFactory::newBean('Audit');
        $data = $audit->getAuditLog($this->bean);
        $dateCreated = $timedate->fromDbType($dateCreated, "datetime");
        $expectedDateCreated = $timedate->asIso($dateCreated);
        $expected = array(
                0 => array(
                    'field_name' => 'name',
                    'date_created' => $expectedDateCreated,
                    'after' => 'Awesome',
                    'before' => 'Test',
                ),
            );

        $this->assertEquals($expected, $data, "Expected Result was incorrect");
    }

    public function testGetAuditLogTranslation()
    {
        global $timedate;
        $auditTable = $this->bean->get_audit_table_name();
        $dateCreated = date('Y-m-d H:i:s');
        self::$db->addQuerySpy(
            'auditQuery',
            '/' . $auditTable . '/',
            array(
                array(
                    'field_name' => 'assigned_user_id',
                    'date_created' => $dateCreated,
                    'before_value_string' => '012345678',
                    'after_value_string' => '876543210',
                    'before_value_text' => '',
                    'after_value_text' => '',
                ),
            )
        );

        self::$db->addQuerySpy(
            'translateQuery',
            '/012345678/',
            array(
                array(
                    'user_name' => 'Jim'
                ),
            )
        );
        self::$db->addQuerySpy(
            'translateQuery2',
            '/876543210/',
            array(
                array(
                    'user_name' => 'Sally'
                ),
            )
        );
        $audit = BeanFactory::newBean('Audit');
        $data = $audit->getAuditLog($this->bean);
        $dateCreated = $timedate->fromDbType($dateCreated, "datetime");
        $expectedDateCreated = $timedate->asIso($dateCreated);
        $expected = array(
            0 => array(
                'field_name' => 'assigned_user_id',
                'date_created' => $expectedDateCreated,
                'after' => 'Sally',
                'before' => 'Jim',
            ),
        );

        $this->assertEquals($expected, $data, "Expected Result was incorrect");
    }

    public function testHandleRelateField()
    {
        $userBeforeMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array('get_summary_text'))
            ->getMock();
        $userBeforeMock->module_name = 'Users';
        $userBeforeMock->id = create_guid();
        $userBeforeMock->expects($this->once())
            ->method('get_summary_text')
            ->will($this->returnValue('Jim Brennan'));
        $this->registerBean($userBeforeMock);
        $userAfterMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array('get_summary_text'))
            ->getMock();
        $userAfterMock->module_name = 'Users';
        $userAfterMock->id = create_guid();
        $userAfterMock->expects($this->once())
            ->method('get_summary_text')
            ->will($this->returnValue('Sally Bronsen'));
        $this->registerBean($userAfterMock);
        $row = array(
            'field_name' => 'user_id_c',
            'before_value_string' => $userBeforeMock->id,
            'after_value_string' => $userAfterMock->id,
        );
        $expected = array(
            'field_name' => 'user_c',
            'after' => 'Sally Bronsen',
            'before' => 'Jim Brennan'
        );
        $bean = new SugarBean();
        $bean->audit_enabled_fields = array(
            'user_c' => array(
                'name' => 'user_c',
                'type' => 'relate',
                'id_name' => 'user_id_c',
                'module' => 'Users')
        );
        $bean->auditEnabledRelateFields = array(
            'user_id_c' => array(
                'name' => 'user_c',
                'type' => 'relate',
                'id_name' => 'user_id_c',
                'module' => 'Users')
        );
        $audit = BeanFactory::newBean('Audit');
        SugarTestReflection::callProtectedMethod($audit, 'handleRelateField', array($bean, &$row));
        $this->assertEquals($expected, $row, "Expected Result was incorrect for relate field");
    }
}
