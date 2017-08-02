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
 * @ticket 24095
 */
class Bug40311Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_tablename;
    private $_old_installing;

    /**
     * @var SugarTestDatabaseMock
     */
    private $db;

    public function setUp()
    {
        $this->db = SugarTestHelper::setUp('mock_db');

        $this->accountMockBean = $this->getMockBuilder('Account')->setMethods(array('hasCustomFields'))->getMock();
        $this->_tablename = 'test' . date("YmdHis");
        if ( isset($GLOBALS['installing']) )
            $this->_old_installing = $GLOBALS['installing'];
        $GLOBALS['installing'] = true;

    }

    public function tearDown()
    {
        if ( isset($this->_old_installing) ) {
            $GLOBALS['installing'] = $this->_old_installing;
        }
        else {
            unset($GLOBALS['installing']);
        }
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testDynamicFieldsNullWorks()
    {

        $this->db->addQuerySpy(
            'dynamic_field',
            '/' . $this->_tablename . '_cstm\.\*/',
            array(
                array(
                    'id_c' => '12345',
                    'foo_c' => NULL
                )
            )
        );


        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);
        $bean->expects($this->any())
             ->method('hasCustomFields')
             ->will($this->returnValue(true));
        $bean->table_name = $this->_tablename;
        $bean->id = '12345';
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->foo_c, NULL);
    }
}
