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


class IBMDB2ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var IBMDB2Manager */
    protected $_db = null;

    static public function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
    }

    static public function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        $this->_db = new IBMDB2Manager();
    }

    /**
     * Testing that FTS index dropped if dropTable had been called for its table
     * @ticket CRYS-463
     */
    public function testFTSIndexDropWhileTableDropping()
    {
        $table = create_guid();

        $indexOk = array(
            'name' => create_guid(),
            'type' => 'fulltext',
            'fields' => array('description'),
            'message_locale' => 'EN_US',
        );
        $indexBad = array(
            'name' => create_guid(),
            'type' => 'index',
            'fields' => array('id'),
        );

        $account = $this->createMock('Account');
        $account->expects($this->any())->method('getTableName')->will($this->returnValue($table));

        $db = $this->createPartialMock(get_class($this->_db), array('get_indices', 'dropIndexes', 'dropTableName'));
        $db->expects($this->once())->method('get_indices')->with($this->equalTo($table))->will($this->returnValue(array(
            $indexOk['name'] => $indexOk,
            $indexBad['name'] => $indexBad,
        )));
        $db->expects($this->once())->method('dropIndexes')->with($this->equalTo($table), $this->equalTo(array($indexOk)), $this->equalTo(true));
        // stop parent::dropTable call
        $db->expects($this->any())->method('dropTableName');

        $db->dropTable($account);
    }

    /**
     * Testing that get_indices selects FTS indices too
     * @ticket CRYS-463
     */
    public function testGetIndices()
    {
        $db = $this->createPartialMock('IBMDB2Manager', array(
            'populate_index_data',
            'populate_fulltext_index_data',
        ));

        $db->expects($this->once())
            ->method('populate_index_data');
        $db->expects($this->once())
            ->method('populate_fulltext_index_data');

        $db->get_indices('mytable');
    }

    /**
     * @ticket PAT-389
     */
    public function testAddColumnSQL()
    {
        $fieldDef = array(
            'name' => 'testColumn',
            'required' => true,
            'type' => 'int',
            'isnull' => false
        );
        $this->assertNotContains('NOT NULL', $this->_db->addColumnSQL('testTable', $fieldDef), 'New column should be nullable if required but no default');
        $fieldDef['default'] = 0;
        $this->assertContains('NOT NULL', $this->_db->addColumnSQL('testTable', $fieldDef), 'New column should be not null if required with default');
    }

    public function providerConvert()
    {
        $returnArray = array(
            array(
                array('1.23', 'round', array(6)),
                "round(1.23, 6)"
            ),
            array(
                array('date_created', 'date_format', array('%v')),
                "TO_CHAR(date_created, 'IW')"
            ),
        );
        return $returnArray;
    }

    /**
     * @dataProvider providerConvert
     */
    public function testConvert(array $parameters, $result)
    {
        $this->assertEquals($result, call_user_func_array(array($this->_db, "convert"), $parameters));
    }

    /**
     * Test asserts that massageField generates correct default value for field if it's needed
     *
     * @dataProvider providerForMassageFieldDefDefault
     */
    public function testMassageFieldDefDefault(array $defs, $expected)
    {
        $this->_db->massageFieldDef($defs, 'table');
        if (isset($expected)) {
            $this->assertArrayHasKey('default', $defs, 'Default value is not present');
            $this->assertEquals($expected, $defs['default'], 'Default value is incorrect');
        } else {
            $this->assertArrayNotHasKey('default', $defs, 'Default value is incorrect');
        }
    }

    static public function providerForMassageFieldDefDefault()
    {
        return array(
            array(
                array(
                    'name' => 'test',
                    'type' => 'int',
                ),
                null,
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'int',
                    'default' => 5,
                ),
                5,
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'int',
                    'required' => true,
                ),
                0,
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'varchar',
                ),
                null,
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'varchar',
                    'default' => 'string',
                ),
                'string',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'varchar',
                    'required' => true,
                ),
                '',
            ),
        );
    }

    /**
     * Test order_stability capability BR-2097
     */
    public function testOrderStability()
    {
        $msg = 'IBMDB2Manager should not have order_stability capability';
        $this->assertFalse($this->_db->supports('order_stability'), $msg);
    }
}
