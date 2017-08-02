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
 * @ticket 43471
 */
class Bug43471Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_tablename;
    private $_old_installing;

    public function setUp()
    {
        $this->accountMockBean = $this->createMock('TestBean');
        $this->_tablename = 'test' . date("YmdHis");
    }

    public function tearDown()
    {
    }

    public function testDynamicFieldsRepairCustomFields()
    {
        $bean = $this->accountMockBean;

        /** @var $df DynamicField */
        $df = $this->createPartialMock('DynamicField', array('createCustomTable'));
        $bean->table_name = $this->_tablename;
        $bean->field_defs = array (
              'id' =>
              array (
                'name' => 'id',
                'vname' => 'LBL_ID',
                'type' => 'id',
                'required' => true,
                'reportable' => true,
                'comment' => 'Unique identifier',
              ),
              'name' =>
              array (
                'name' => 'name',
                'type' => 'name',
                'dbType' => 'varchar',
                'vname' => 'LBL_NAME',
                'len' => 150,
                'comment' => 'Name of the Company',
                'unified_search' => true,
                'audited' => true,
                'required' => true,
                'importable' => 'required',
                'merge_filter' => 'selected',
              ),
              'FooBar_c' =>
              array (
                'required' => false,
                'source' => 'custom_fields',
                'name' => 'FooBar_c',
                'vname' => 'LBL_FOOBAR',
                'type' => 'varchar',
                'massupdate' => '0',
                'default' => NULL,
                'comments' => 'LBL_FOOBAR_COMMENT',
                'help' => 'LBL_FOOBAR_HELP',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'calculated' => false,
                'len' => '255',
                'size' => '20',
                'id' => 'AccountsFooBar_c',
                'custom_module' => 'Accounts',
              ),
            );
        $df->setup($bean);
        $df->expects($this->any())
                ->method('createCustomTable')
                ->will($this->returnValue(null));

        $helper = $this->createPartialMock('MysqliManager', array('get_columns'));
        $helper->expects($this->any())
                ->method('get_columns')
                ->will($this->returnValue(array(
                'foobar_c' => array (
                    'name' => 'FooBar_c',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                )));
        // set the mock db manager (no longer a helper)
        $db = $GLOBALS['db'];
        $GLOBALS['db'] = $helper;

        $repair = $df->repairCustomFields(false);
        $this->assertEquals("", $repair);

        // reset the db
        $GLOBALS['db'] = $db;
    }
}


// test bean class

// Account is used to store account information.
class TestBean extends Company {
}
