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

require_once 'tests/{old}/include/database/TestBean.php';

class DBManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    protected $_db;
    protected $created = array();

    protected $backupGlobals = false;

    /**
     * @var bool Backup for DBManager::encode.
     */
    protected $dbEncode;

    static public function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['db']->query('DELETE FROM forecast_tree');
    }

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        if(empty($this->_db))
        {
            $this->_db = DBManagerFactory::getInstance();
            $this->dbEncode = $this->_db->getEncode();
        }
        $this->created = array();

    }

    public function tearDown()
    {
        $this->_db->setEncode($this->dbEncode);
        foreach($this->created as $table => $dummy) {
            $this->_db->dropTableName($table);
        }
    }

    protected function createTableParams($tablename, $fieldDefs, $indices)
    {
        $this->created[$tablename] = true;
        return $this->_db->createTableParams($tablename, $fieldDefs, $indices);
    }

    protected function dropTableName($tablename)
    {
        unset($this->created[$tablename]);
        return $this->_db->dropTableName($tablename);
    }

    private function _createRecords(
        $num
        )
    {
        $beanIds = array();
        for ( $i = 0; $i < $num; $i++ ) {
            $bean = new Contact();
            $bean->id = create_guid();
            $bean->last_name = "foobar";
            $this->_db->insert($bean);
            $beanIds[] = $bean->id;
        }

        return $beanIds;
    }

    private function _removeRecords(
        array $ids
        )
    {
        foreach ($ids as $id)
            $this->_db->query("DELETE From contacts where id = '{$id}'");
    }

    public function testGetDatabase()
    {
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli',$this->_db->getDatabase());
        else
            $this->assertTrue(is_resource($this->_db->getDatabase()));
    }

    public function testCheckError()
    {
        $this->assertFalse($this->_db->checkError("testCheckError"));
        $this->assertFalse($this->_db->lastError());
    }

    public function testCheckErrorNoConnection()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError("testCheckErrorNoConnection"));
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testGetQueryTime()
    {
        // BR-3387.  MSSQL caches the result, the second run will cost 'NO TIME'
        // using a random query.
        $randVal= rand(0, 10000);
        $sql = "SELECT accounts.* FROM accounts WHERE DELETED = 0";
        $this->_db->limitQuery($sql,0,1+$randVal,true);
        $this->assertTrue($this->_db->getQueryTime() > 0);
    }

    public function testCheckConnection()
    {
        $this->_db->checkConnection();
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli',$this->_db->getDatabase());
        else
            $this->assertTrue(is_resource($this->_db->getDatabase()));
    }

    public function testInsert()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id = create_guid();

        $this->assertTrue($this->_db->insert($bean));

        $result = $this->_db->query("select id, last_name from contacts where id = '{$bean->id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'],$bean->last_name);
        $this->assertEquals($row['id'],$bean->id);

        $this->_db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testUpdate()
    {
        list($id) = $this->_createRecords(1);

        $bean = new Contact();
        $bean->last_name = 'newfoobar' . mt_rand();
        $this->assertTrue($this->_db->update($bean));

        $result = $this->_db->query("select id, last_name from contacts where id = '{$id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'],$bean->last_name);
        $this->assertEquals($row['id'],$id);

        $this->_db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testCreateTableParams()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_'. $tablename,
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);
    }

    public function testRepairTableNoChanges()
    {
        $tableName = 'testRTNC_' . mt_rand();
        $params =  array(
                /* VARDEF - id -  ROW[name] => 'id'  [vname] => 'LBL_ID'  [required] => 'true'  [type] => 'char'  [reportable] => ''  [comment] => 'Unique identifier'  [dbType] => 'id'  [len] => '36'  */
            'id' =>
                array (
                'name' => 'id',
                'vname' => 'LBL_ID',
                'required'=>true,
                'type' => 'id',
                'reportable'=>false,
                'comment' => 'Unique identifier'
                ),
            'date_entered' =>
                array (
                'name' => 'date_entered',
                'vname' => 'LBL_DATE_ENTERED',
                'type' => 'datetime',
                'required'=>true,
                'comment' => 'Date record created'
                ),
            'date_modified' =>
                array (
                  'name' => 'date_modified',
                  'vname' => 'LBL_DATE_MODIFIED',
                  'type' => 'datetime',
                  'required'=>true,
                  'comment' => 'Date record last modified'
                ),
            'modified_user_id' =>
                array (
                  'name' => 'modified_user_id',
                  'rname' => 'user_name',
                  'id_name' => 'modified_user_id',
                  'vname' => 'LBL_MODIFIED',
                  'type' => 'assigned_user_name',
                  'table' => 'modified_user_id_users',
                  'isnull' => 'false',
                  'dbType' => 'id',
                  'required'=> false,
                  'len' => 36,
                  'reportable'=>true,
                  'comment' => 'User who last modified record'
                ),
            'created_by' =>
                array (
                  'name' => 'created_by',
                  'rname' => 'user_name',
                  'id_name' => 'created_by',
                  'vname' => 'LBL_CREATED',
                  'type' => 'assigned_user_name',
                  'table' => 'created_by_users',
                  'isnull' => 'false',
                  'dbType' => 'id',
                  'len' => 36,
                  'comment' => 'User ID who created record'
                ),
            'name' =>
                array (
                  'name' => 'name',
                  'type' => 'varchar',
                  'vname' => 'LBL_NAME',
                  'len' => 150,
                  'comment' => 'Name of the allowable action (view, list, delete, edit)'
                ),
            'category' =>
                array (
                  'name' => 'category',
                  'vname' => 'LBL_CATEGORY',
                  'type' => 'varchar',
                  'len' =>100,
                  'reportable'=>true,
                    'required'=>true,
                    'isnull' => false,
                  'comment' => 'Category of the allowable action (usually the name of a module)'
                ),
            'acltype' =>
                array (
                  'name' => 'acltype',
                  'vname' => 'LBL_TYPE',
                  'type' => 'varchar',
                  'len' =>100,
                  'reportable'=>true,
                  'comment' => 'Specifier for Category, usually "module"'
                ),
            'aclaccess' =>
                array (
                  'name' => 'aclaccess',
                  'vname' => 'LBL_ACCESS',
                  'type' => 'int',
                  'len'=>3,
                  'reportable'=>true,
                  'comment' => 'Number specifying access priority; highest access "wins"'
                ),
            'deleted' =>
                array (
                  'name' => 'deleted',
                  'vname' => 'LBL_DELETED',
                  'type' => 'bool',
                  'reportable'=>false,
                  'comment' => 'Record deletion indicator'
                ),
            'roles' =>
                array (
                    'name' => 'roles',
                    'type' => 'link',
                    'relationship' => 'acl_roles_actions',
                    'source'=>'non-db',
                    'vname'=>'LBL_USERS',
                ),
  			'reverse' =>
                array (
                    'name' => 'reverse',
                    'vname' => 'LBL_REVERSE',
                    'type' => 'bool',
                    'default' => 0
                ),
  		 	'deleted2' =>
                array (
                    'name' => 'deleted2',
                    'vname' => 'LBL_DELETED2',
                    'type' => 'bool',
                    'reportable'=>false,
                    'default' => '0'
                ),
            'primary_address_country' =>
                array (
                   'name' => 'primary_address_country',
                   'vname' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
                   'type' => 'varchar',
                   'group'=>'primary_address',
                   'comment' => 'Country for primary address',
                   'merge_filter' => 'enabled',
                ),
            'refer_url' => array (
                'name' => 'refer_url',
                'vname' => 'LBL_REFER_URL',
                'type' => 'varchar',
                'len' => '255',
                'default' => 'http://',
                'comment' => 'The URL referenced in the tracker URL; no longer used as of 4.2 (see campaign_trkrs)'
                ),
            'budget' => array (
                'name' => 'budget',
                'vname' => 'LBL_CAMPAIGN_BUDGET',
                'type' => 'currency',
                'dbType' => 'double',
                'comment' => 'Budgeted amount for the campaign'
                ),
            'time_from' => array (
                'name' => 'time_from',
                'vname' => 'LBL_TIME_FROM',
                'type' => 'time',
                'required' => false,
                'reportable' => false,
                ),
            'description' =>
                array (
                'name' => 'description',
                'vname' => 'LBL_DESCRIPTION',
                'type' => 'text',
                'comment' => 'Full text of the note',
                'rows' => 6,
                'cols' => 80,
                ),
            'cur_plain' => array (
                'name' => 'cur_plain',
                'vname' => 'LBL_curPlain',
                'type' => 'currency',
            ),
            'cur_len_prec' => array (
                'name' => 'cur_len_prec',
                'vname' => 'LBL_curLenPrec',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26,6',
            ),
            'cur_len' => array (
                'name' => 'cur_len',
                'vname' => 'LBL_curLen',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26',
            ),
            'cur_len_prec2' => array (
                'name' => 'cur_len_prec2',
                'vname' => 'LBL_curLenPrec',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26',
                'precision' => '6',
            ),
            'token_ts' =>
            array (
                'name' => 'token_ts',
                'type' => 'long',
                'required' => true,
                'comment' => 'Token timestamp',
                'function' => array('name' => 'displayDateFromTs', 'returns' => 'html', 'onListView' => true)
            ),
            'conskey' => array(
                'name'		=> 'conskey',
                'type'		=> 'varchar',
                'len'		=> 32,
                'required'	=> true,
                'isnull'	=> false,
            ),
        );
        $indexes = array(
            array(
                'name' => "idx_{$tableName}",
                'type' =>'primary',
                'fields' => array(
                    'id',
                    'category',
                )
            )
        );

        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }

        $this->createTableParams($tableName, $params, $indexes);

        $repair = $this->_db->repairTableParams($tableName, $params, $indexes, true);
        $this->assertEmpty($repair, "Unexpected repairs: " . $repair);

        $this->dropTableName($tableName);
    }

    /**
     * Test creation of primary key on existing index.
     */
    public function testPrimaryKeyOnExistingIndex()
    {
        $tableName = 'testRTNC2_' . mt_rand();
        $fields = array (
            'list_id' => array(
                'name' => 'list_id',
                'type' => 'id',
                'required' => true,
                'reportable' => false,
            ),
            'bean_id' => array(
                'name' => 'bean_id',
                'type' => 'id',
                'required' => true,
                'reportable' => false,
            ),
        );
        $indices = array(
            array(
                'name' => 'testRTNC2_list_id_idx',
                'type' =>'index',
                'fields' => array(
                    'list_id',
                )
            ),
            array(
                'name' => 'testRTNC2_list_id_bean_idx',
                'type' =>'index',
                'fields' => array(
                    'list_id',
                    'bean_id',
                )
            ),
        );
        $this->createTableParams($tableName, $fields, $indices);
        $indices = array(
            array(
                'name' => 'testRTNC2_list_id_idx',
                'type' =>'index',
                'fields' => array(
                    'list_id',
                )
            ),
            array(
                'name' => 'idx_testRTNC2_pk',
                'type' =>'primary',
                'fields' => array(
                    'list_id',
                    'bean_id',
                )
            ),
        );
        $repair = $this->_db->repairTableParams($tableName, $fields, $indices, true);
        $this->assertNotEmpty($repair, "Shouldn't be empty repair");

        $dbIndexes = $this->_db->get_indices($tableName);

        //Should replaced with assertArraySubset in future
        $pk = false;
        foreach ($dbIndexes as $ind) {
            if ($ind['type'] == 'primary') {
                $pk = true;
                break;
            }
        }
        $this->assertTrue($pk, 'There should be primary index for table');
    }

    public function testRepairTableParamsAddData()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
        );

        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());

		$params['bar'] =  array (
                    'name' => 'bar',
                    'type' => 'int',
                    );
        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayNotHasKey('bar', $cols);

        $repair = $this->_db->repairTableParams($tableName, $params, array(), false);
        $this->assertRegExp('#MISSING IN DATABASE.*bar#i', $repair);
        $repair = $this->_db->repairTableParams($tableName, $params, array(), true);
        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayHasKey('bar', $cols);
        $this->assertEquals('bar', $cols['bar']['name']);
        $this->assertEquals($this->_db->getColumnType('int'), $cols['bar']['type']);

        $this->dropTableName($tableName);
    }

    public function testRepairTableParamsAddIndex()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    'isnull' => false,
                    'required' => true,
                    ),
                'bar' => array (
                    'name' => 'bar',
                    'type' => 'int',
                    ),
        );
        $primaryKey = $tableName . '_pk';
        $indices = array(
            array(
                'name' => $primaryKey,
                'type' => 'primary',
                'fields' => array('foo'),
            ),
            array(
                'name' => 'test_index',
                'type' => 'index',
                'fields' => array('foo', 'bar', 'bazz'),
            ),
        );
        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());
		$params['bazz'] =  array (
                    'name' => 'bazz',
                    'type' => 'int',
        );

        $repair = $this->_db->repairTableParams($tableName, $params, $indices, false);
        $this->assertRegExp('#MISSING IN DATABASE.*bazz#i', $repair);
        $this->assertRegExp('#MISSING INDEX IN DATABASE.*test_index#i', $repair);
        $this->assertRegExp('#MISSING INDEX IN DATABASE.*primary#i', $repair);
        $this->_db->repairTableParams($tableName, $params, $indices, true);

        $idx = $this->_db->get_indices($tableName);
        foreach ($idx as $index) {
            if ($index['type'] == 'primary') {
                $idx['primary'] = $index;
                break;
            }
        }
        $this->assertArrayHasKey('test_index', $idx);
        $this->assertContains('foo', $idx['test_index']['fields']);
        $this->assertContains('bazz', $idx['test_index']['fields']);
        $this->assertArrayHasKey('primary', $idx);
        $this->assertContains('foo', $idx['primary']['fields']);

        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayHasKey('bazz', $cols);
        $this->assertEquals('bazz', $cols['bazz']['name']);
        $this->assertEquals($this->_db->getColumnType('int'), $cols['bazz']['type']);

        $this->dropTableName($tableName);
    }

    public function testRepairTableParamsAddIndexAndData()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'bar' => array (
                    'name' => 'bar',
                    'type' => 'int',
                    ),
        );
        $index = array(
			'name'			=> 'test_index',
			'type'			=> 'index',
			'fields'		=> array('foo', 'bar'),
		);
        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());

        $repair = $this->_db->repairTableParams($tableName, $params, array($index), false);
        $this->assertRegExp('#MISSING INDEX IN DATABASE.*test_index#i', $repair);
        $repair = $this->_db->repairTableParams($tableName, $params, array($index), true);
        $idx = $this->_db->get_indices($tableName);
        $this->assertArrayHasKey('test_index', $idx);
        $this->assertContains('foo', $idx['test_index']['fields']);
        $this->assertContains('bar', $idx['test_index']['fields']);

        $this->dropTableName($tableName);
    }

    public function testCompareFieldInTables()
    {
        $tablename1 = 'test1_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test2_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesNotInTable1()
    {
        $tablename1 = 'test3_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test4_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);
        $this->assertEquals($res['msg'],'not_exists_table1');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesNotInTable2()
    {
        $tablename1 = 'test5_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test6_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesFieldsDoNotMatch()
    {
        $tablename1 = 'test7_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test8_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'int',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testAddIndexes()
    {
        //TODO Fix test with normal index inspection
        $this->markTestIncomplete(
              'TODO Reimplement test not using compareIndexInTables.'
            );
        $tablename1 = 'test17_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test18_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        // first test not executing the statement
        $this->_db->addIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        // now, execute the statement
        $this->_db->addIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                ))
            );
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testDropIndexes()
    {
        //TODO Fix test with normal index inspection
        $this->markTestIncomplete(
              'TODO Reimplement test not using compareIndexInTables.'
            );

        $tablename1 = 'test19_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test20_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals('match', $res['msg']);

        // first test not executing the statement
        $this->_db->dropIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals('match', $res['msg']);

        // now, execute the statement
        $sql = $this->_db->dropIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            true
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals('not_exists_table2', $res['msg']);

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testModifyIndexes()
    {
        //TODO Fix test with normal index inspection
        $this->markTestIncomplete(
              'TODO Reimplement test not using compareIndexInTables.'
            );
        $tablename1 = 'test21_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_'. $tablename1,
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test22_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_'. $tablename2,
                    'type'   => 'index',
                    'fields' => array('foobar'),
                    )
                )
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->_db->modifyIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->_db->modifyIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                ))
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function providerRepairIndexes()
    {
        return array(
        // create PK
            array(
                array(),
                array(array('name' => 'pkey', 'type' => 'primary', 'fields' => array('id'))),
                'ADD {"name":"pkey","type":"primary","fields":["id"]}',
            ),
        // PK name change
            array(
                array(array('name' => 'pkey', 'type' => 'primary', 'fields' => array('id'))),
                array(array('name' => 'pkey2', 'type' => 'primary', 'fields' => array('id'))),
                '',
            ),
        // PK removal
            array(
                array(array('name' => 'pkey', 'type' => 'primary', 'fields' => array('id'))),
                array(),
                '',
            ),
        // Index add
            array(
                array(),
                array(array('name' => 'mykey', 'type' => 'index', 'fields' => array('foo', 'bar'))),
                'ADD {"name":"mykey","type":"index","fields":["foo","bar"]}',
            ),
        // Index remove
            array(
                array(array('name' => 'mykey', 'type' => 'index', 'fields' => array('foo', 'bar'))),
                array(),
                '',
            ),
        // Index change
            array(
                array(array('name' => 'mykey', 'type' => 'index', 'fields' => array('foo'))),
                array(array('name' => 'mykey', 'type' => 'index', 'fields' => array('foo', 'bar'))),
                'ADD {"name":"mykey","type":"index","fields":["foo","bar"]}',
            ),
        // Index change 2
            array(
                array(array('name' => 'mykey', 'type' => 'index', 'fields' => array('foo'))),
                array(array('name' => 'mykeynew', 'type' => 'index', 'fields' => array('foo', 'bar'))),
                'ADD {"name":"mykeynew","type":"index","fields":["foo","bar"]}',
            ),
        // Index rename
            array(
                array(array('name' => 'mykey', 'type' => 'index', 'fields' => array('foo', 'bar'))),
                array(array('name' => 'mykeynew', 'type' => 'index', 'fields' => array('foo', 'bar'))),
                '',
            ),
        );
    }

    /**
     * @dataProvider providerRepairIndexes
     * @param array $current
     * @param array $new
     * @param string $query
     */
    public function testRepairIndexes($current, $new, $query)
    {
        $tablename1 = 'test23_' . mt_rand();
        $dbmock = $this->getMockBuilder(get_class($this->_db))
            ->setMethods(array('get_columns', 'get_indices', 'add_drop_constraint'))
            ->getMock();
        if (!($dbmock instanceof DBManager)) {
            // Failed to instantiate the driver, skip it
            $this->markTestSkipped("Could not load DB driver");
        }
        $db_columns = array(
                'id' => array(
                    'name' => 'id',
                    'type' => 'id',
                ),
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'int',
                    ),
                'bar' => array (
                    'name' => 'bar',
                    'type' => 'short',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'float',
                    ),
        );


        $dbmock->expects($this->any())
        ->method('get_columns')
        ->will($this->returnValue($db_columns));

        $dbmock->expects($this->any())
        ->method('get_indices')
        ->will($this->returnValue($current));

        $dbmock->expects($this->any())
        ->method('add_drop_constraint')
        ->will($this->returnCallback(
            function ($table, $definition, $drop = false) {
                if ($definition['type'] == 'clustered') {
                    $definition['type'] = 'index'; // SQL Server uses clustered index, we need switch that back
                }
                return "ALTER TABLE $table ". ($drop?"DROP":"ADD") . " ". json_encode($definition);
            }
        ));

        $sql = SugarTestReflection::callProtectedMethod(
            $dbmock,
            'repairTableIndices',
            array($tablename1, $db_columns, $new, false)
        );

        if (!empty($query)) {
            $this->assertContains("ALTER TABLE $tablename1", $sql);
            $this->assertContains($query, $sql);
        } else {
            $this->assertNotContains("ALTER TABLE $tablename1", $sql);
        }
    }

    public function testAddColumn()
    {
        $tablename1 = 'test23_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test24_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        $this->_db->addColumn(
            $tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    )
                )
            );

        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function alterColumnDataProvider()
    {
        return array(
            array(
                 1,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'required' => true, 'default' => 'sugar'),
                'temp' => array ('name' => 'foobar', 'type' => 'int')                           // Check if type conversion works
            ),
            array(
                2,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'default' => 'kilroy'),
                'temp' => array ('name' => 'foobar', 'type' => 'double', 'default' => '99999')  // Check if default gets replaced
            ),
            array(
                3,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255'),
                'temp' => array ('name' => 'foobar', 'type' => 'double', 'default' => '99999')  // Check if default gets dropped
            ),
            array(
                4,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'required' => true, 'default' => 'sweet'),
                'temp' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '1500',)      // Check varchar shortening
            ),
            array(
                5,
                'target' => array ('name' => 'foobar', 'type' => 'longtext', 'required' => true),
                'temp' => array ('name' => 'foobar', 'type' => 'text', 'default' => 'dextrose') // Check clob(65k) to clob(2M or so) conversion
            ),
            array(
                6,
                'target' => array ('name' => 'foobar', 'type' => 'double', 'required' => true),
                'temp' => array ('name' => 'foobar', 'type' => 'int', 'default' => 0)           // Check int to double change
            ),
        );
    }



    /**
     * @dataProvider alterColumnDataProvider
     * @param  $i
     * @param  $target
     * @param  $temp
     * @return void
     */
    public function testAlterColumn($i, $target, $temp)
    {
        if ($this->_db instanceof OracleManager && ($i == 4 || $i == 5)) {
            $this->markTestSkipped("Cannot reliably shrink columns in Oracle");
        }

        $foo_col = array ('name' => 'foo', 'type' => 'varchar', 'len' => '255'); // Common column between tables

        $tablebase = 'testac_'. mt_rand() . '_';

        $t1 = $tablebase . $i .'A';
        $t2 = $tablebase . $i .'B';
        $this->createTableParams(  $t1,
                                        array('foo' => $foo_col, 'foobar' => $target),
                                        array());
        $this->createTableParams(  $t2,
                                        array('foo' => $foo_col, 'foobar' => $temp),
                                        array());

        $res = $this->_db->compareFieldInTables('foobar', $t1, $t2);

        $this->assertEquals('no_match', $res['msg'],
                            "testAlterColumn table columns match while they shouldn't for table $t1 and $t2: "
                            . print_r($res,true) );

        $this->_db->alterColumn($t2, array('foobar' => $target));

        $res = $this->_db->compareFieldInTables('foobar', $t1, $t2);

        $this->assertEquals('match', $res['msg'],
                            "testAlterColumn table columns don't match while they should for table $t1 and $t2: "
                            . print_r($res,true) );

        $this->dropTableName($t1);
        $this->dropTableName($t2);
    }

    public function testOracleAlterVarchar2ToNumber()
    {
        $insertValue = '100';
        if (!($this->_db instanceof OracleManager)) {
            $this->markTestSkipped('This test can run only on Oracle instance');
        }
        $params = array(
            'foo' => array(
                'name' => 'foo',
                'vname' => 'LBL_FOO',
                'type' => 'enum',
                'dbType' => 'varchar',
            ),
        );
        $tableName = 'testVarchar2ToNumber' . mt_rand();

        if ($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, array());

        $this->_db->insertParams($tableName, $params, array('foo' => $insertValue));

        $params = array(
            'foo' => array(
                'name' => 'foo',
                'vname' => 'LBL_FOO',
                'type' => 'enum',
                'dbType' => 'int',
            ),
        );

        $this->_db->repairTableParams($tableName, $params, array(), true);

        $columns = $this->_db->get_columns($tableName);
        $this->assertEquals('number', $columns['foo']['type']);

        $checkResult = $this->_db->fetchOne('SELECT foo FROM ' . $tableName);
        $this->assertEquals($insertValue, $checkResult['foo']);
    }

    public function testDropTableName()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);

        $this->assertFalse(in_array($tablename,$this->_db->getTablesArray()));
    }

    public function testDisconnectAll()
    {
        DBManagerFactory::disconnectAll();
        $this->assertTrue($this->_db->checkError("testDisconnectAll"));
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testQuery()
    {
        $beanIds = $this->_createRecords(5);

        $result = $this->_db->query("SELECT id From contacts where last_name = 'foobar'");
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli_result',$result);
        else
            $this->assertTrue(is_resource($result));

        while ( $row = $this->_db->fetchByAssoc($result) )
            $this->assertTrue(in_array($row['id'],$beanIds),"Id not found '{$row['id']}'");

        $this->_removeRecords($beanIds);
    }

    public function disabledLimitQuery()
    {
        $beanIds = $this->_createRecords(5);
        $_REQUEST['module'] = 'contacts';
        $result = $this->_db->limitQuery("SELECT id From contacts where last_name = 'foobar'",1,3);
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli_result',$result);
        else
            $this->assertTrue(is_resource($result));

        while ( $row = $this->_db->fetchByAssoc($result) ) {
            if ( $row['id'][0] > 3 || $row['id'][0] < 0 )
                $this->assertFalse(in_array($row['id'],$beanIds),"Found {$row['id']} in error");
            else
                $this->assertTrue(in_array($row['id'],$beanIds),"Didn't find {$row['id']}");
        }
        unset($_REQUEST['module']);
        $this->_removeRecords($beanIds);
    }

    public function testLimitQueryOrderedByAlias()
    {
        $sql = <<<SQL
SELECT
    id,
    name name_alias
FROM
    accounts
WHERE
    deleted = 0
ORDER BY
    name_alias ASC
SQL;
        $result = $this->_db->limitQuery($sql, 0, 1);
        $this->assertNotEmpty($result, $this->_db->lastDbError());
    }

    public function testGetOne()
    {
        $beanIds = $this->_createRecords(1);

        $id = $this->_db->getOne("SELECT id From contacts where last_name = 'foobar'");
        $this->assertEquals($id,$beanIds[0]);

        // bug 38994
        if ($this->_db->dbType == 'mysql') {
            $id = $this->_db->getOne($this->_db->limitQuerySql("SELECT id From contacts where last_name = 'foobar'", 0, 1));
            $this->assertEquals($id,$beanIds[0]);
        }

        $this->_removeRecords($beanIds);
    }

    public function testGetFieldsArray()
    {
        $beanIds = $this->_createRecords(1);

        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");
        $fields = $this->_db->getFieldsArray($result,true);

        $this->assertEquals(array("id"),$fields);

        $this->_removeRecords($beanIds);
    }

    public function testGetAffectedRowCount()
    {
        if(!$this->_db->supports("affected_rows")) {
            $this->markTestSkipped('Skipping, backend doesn\'t support affected rows');
        }

        $beanIds = $this->_createRecords(1);
        // need to keep $result
        $result = $this->_db->query("DELETE From contacts where id = '{$beanIds[0]}'", false, '', false, true);
        $this->assertEquals(1, $this->_db->getAffectedRowCount($result));
    }

    public function testFetchByAssoc()
    {
        $beanIds = $this->_createRecords(1);

        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");

        $row = $this->_db->fetchByAssoc($result);

        $this->assertTrue(is_array($row));
        $this->assertEquals($row['id'],$beanIds[0]);

        $this->_removeRecords($beanIds);
    }

    public function testDisconnect()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError("testDisconnect"));
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testGetTablesArray()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $this->assertTrue($this->_db->tableExists($tablename));

        $this->dropTableName($tablename);
    }

    public function testVersion()
    {
        $ver = $this->_db->version();

        $this->assertTrue(is_string($ver));
    }

    public function testTableExists()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);
    }

    public function providerCompareVardefs()
    {
        $returnArray = array(
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                false),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'len' => '255',
                ),
                false),
            array(
                array(
                    'name' => 'foo',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'FOO',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
                array(
                    array(
                        'name' => 'foo',
                        'type' => 'decimal',
                        'len' => '16,6',
                        'default' => '0.000000',
                    ),
                    array(
                        'name' => 'foo',
                        'type' => 'decimal',
                        'default' => '',
                        'no_default' => '',
                        'len' => '16,6',
                        'size' => '20',
                        'precision' => '6',
                    ),
                    true),
                    array(
                        array(
                            'name' => 'foo',
                            'type' => 'decimal',
                            'len' => '16,6',
                            'default' => '0.000000',
                        ),
                        array(
                            'name' => 'foo',
                            'type' => 'decimal',
                            'default' => '0',
                            'no_default' => '',
                            'len' => '16,6',
                            'size' => '20',
                            'precision' => '6',
                        ),
                        true),
                        array(
                            array(
                                'name' => 'solution_number',
                                'type' => 'int',
                                'len' => '11',
                                'auto_increment' => '1',
                                'required' => 'true',
                            ),
                            array(
                                'name' => 'solution_number',
                                'type' => 'int',
                                'len' => '11',
                                'auto_increment' => 'true',
                                'required' => 'true',
                                'autoinc_next' => '51',
                                'dbType' => 'int'
                            ),
                            true,),
            );

        return $returnArray;
    }

    /**
     * @dataProvider providerCompareVarDefs
     */
    public function testCompareVarDefs($fieldDef1,$fieldDef2,$expectedResult)
    {
        if ( $expectedResult ) {
            $this->assertTrue($this->_db->compareVarDefs($fieldDef1,$fieldDef2));
        }
        else {
            $this->assertFalse($this->_db->compareVarDefs($fieldDef1,$fieldDef2));
        }
    }

    /**
     * @ticket 34892
     */
    public function test_Bug34892_MssqlNotClearingErrorResults()
    {
            // execute a bad query
            $this->_db->query("select dsdsdsdsdsdsdsdsdsd", false, "test_Bug34892_MssqlNotClearingErrorResults", true);
            // assert it found an error
            $this->assertNotEmpty($this->_db->lastError(), "lastError should return true as a result of the previous illegal query");
            // now, execute a good query
            $this->_db->query("select * from config");
            // and make no error messages are asserted
            $this->assertEmpty($this->_db->lastError(), "lastError should have cleared the previous error and return false of the last legal query");
    }

    public function vardefProvider()
    {
        $GLOBALS['log']->info('DBManagerTest.vardefProvider: _db = ' . print_r($this->_db));
        $this->setUp(); // Just in case the DB driver is not created yet.
        $emptydate = $this->_db->emptyValue("date");
        $emptytime = $this->_db->emptyValue("time");
        $emptydatetime = $this->_db->emptyValue("datetime");

        return array(
            array("testid", array (
                  'id' =>
                  array (
                    'name' => 'id',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  ),
                  array("id" => "test123"),
                  array("id" => "'test123'")
            ),
            array("testtext", array (
                  'text1' =>
                  array (
                    'name' => 'text1',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  'text2' =>
                  array (
                    'name' => 'text2',
                    'type' => 'varchar',
                  ),
                  ),
                  array(),
                  array("text1" => "''", "text2" => "NULL"),
                  array()
            ),
            array("testtext2", array (
                  'text1' =>
                  array (
                    'name' => 'text1',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  'text2' =>
                  array (
                    'name' => 'text2',
                    'type' => 'varchar',
                  ),
                  ),
                  array('text1' => 'foo', 'text2' => 'bar'),
                  array("text1" => "'foo'", 'text2' => "'bar'"),
            ),
            array("testreq", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                        'required'=>true,
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                        'required'=>true,
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                        'required'=>true,
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                        'required'=>true,
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                        'required'=>true,
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                        'required'=>true,
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                        'required'=>true,
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                        'required'=>true,
                      ),
                  ),
                  array("id" => "test123", 'intval' => 42, 'floatval' => 42.24,
                  		'money' => 56.78, 'test_dtm' => '2002-01-02 12:34:56', 'test_dtm2' => '2011-10-08 01:02:03',
                        'test_dt' => '1998-10-04', 'test_tm' => '03:04:05'
                  ),
                  array("id" => "'test123'", 'intval' => 42, 'floatval' => 42.24,
                  		'money' => 56.78, 'test_dtm' => $this->_db->convert('\'2002-01-02 12:34:56\'', "datetime"), 'test_dtm2' => $this->_db->convert('\'2011-10-08 01:02:03\'', 'datetime'),
                        'test_dt' => $this->_db->convert('\'1998-10-04\'', 'date'), 'test_tm' => $this->_db->convert('\'03:04:05\'', 'time')
                  ),
            ),
            array("testreqnull", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                        'required'=>true,
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                        'required'=>true,
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                        'required'=>true,
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                        'required'=>true,
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                        'required'=>true,
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                        'required'=>true,
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                        'required'=>true,
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                        'required'=>true,
                      ),
                  ),
                  array(),
                  array("id" => "''", 'intval' => 0, 'floatval' => 0,
                  		'money' => 0, 'test_dtm' => "$emptydatetime", 'test_dtm2' => "$emptydatetime",
                        'test_dt' => "$emptydate", 'test_tm' => "$emptytime"
                  ),
                  array(),
            ),
            array("testnull", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                      ),
                  ),
                  array("id" => 123),
                  array("id" => "'123'", 'intval' => 'NULL', 'floatval' => 'NULL',
                        'money' => 'NULL', 'test_dtm' => 'NULL', 'test_dtm2' => 'NULL',
                        'test_dt' => 'NULL', 'test_tm' => 'NULL'),
                  array(),
            ),
            array("testempty", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                      ),
                   'text_txt' =>
                      array (
                        'name' => 'test_txt',
                        'type' => 'varchar',
                      ),
                  ),
                  array("id" => "", 'intval' => '', 'floatval' => '',
                  		'money' => '', 'test_dtm' => '', 'test_dtm2' => '',
                        'test_dt' => '', 'test_tm' => '', 'text_txt' => null,
                  ),
                  array("id" => "''", 'intval' => "NULL", 'floatval' => "NULL",
                        'money' => "NULL", 'test_dtm' => "NULL", 'test_dtm2' => "NULL",
                        'test_dt' => "NULL", 'test_tm' => 'NULL', 'test_txt' => 'NULL',
                  ),
                  array('intval' => 'NULL', 'floatval' => 'NULL',
                  		'money' => 'NULL', 'test_dtm' => 'NULL', 'test_dtm2' => 'NULL',
                        'test_dt' => 'NULL', 'test_tm' => 'NULL',
                  ),
            ),
        );
    }

    /**
     * Test the canInstall
     * @return void
     */
    public function testCanInstall() {
        $DBManagerClass = get_class($this->_db);
        if(!method_exists($this->_db, 'version') || !method_exists($this->_db, 'canInstall'))
            $this->markTestSkipped(
              "Class {$DBManagerClass} doesn't implement canInstall or version methods");

        $method = new ReflectionMethod($DBManagerClass, 'canInstall');
        if($method->class == 'DBManager')
            $this->markTestSkipped(
              "Class {$DBManagerClass} or one of it's ancestors doesn't override DBManager's canInstall");

        // First assuming that we are only running unit tests against a supported database :)
        $this->assertTrue($this->_db->canInstall(), "Apparently we are not running this unit test against a supported database!!!");

        $DBstub = $this->getMockBuilder($DBManagerClass)->setMethods(array('version'))->getMock();
        $DBstub->expects($this->any())
               ->method('version')
               ->will($this->returnValue('0.0.0')); // Expect that any supported version is higher than 0.0.0

        $this->assertTrue(is_array($DBstub->canInstall()), "Apparently we do support version 0.0.0 in " . $DBManagerClass);
    }

    public function providerValidateQuery()
    {
        return array(
            array(true, 'SELECT * FROM accounts'),
            array(false, 'SELECT * FROM blablabla123'),
        );
    }

    /**
     * Test query validation
     * @dataProvider providerValidateQuery
     * @param $good
     * @param $sql
     * @return void
     */
    public function testValidateQuery($good, $sql)
    {
        $check = $this->_db->validateQuery($sql);
        $this->assertEquals($good, $check);
    }

    public function testTextSizeHandling()
    {
        $tablename = 'testTextSize';// . mt_rand();
        $fielddefs = array(
                        'id' =>
                            array (
                            'name' => 'id',
                            'required'=>true,
                            'type' => 'id',
                            ),
                        'test' => array (
                            'name' => 'test',
                            'type' => 'longtext',
                            //'len' => '255',
                            ),
                        'dummy' => array (
                            'name' => 'dummy',
                            'type' => 'longtext',
                            //'len' => '255',
                            ),
                        );

        $this->createTableParams($tablename, $fielddefs, array());
        $basestr = '0123456789abcdefghijklmnopqrstuvwxyz';
        $str = $basestr;
        while(strlen($str) < 159900)
        {
            $str .= $basestr;
        }

        for($i = 0; $i < 50; $i++)
        {
            $str .= $basestr;
            $size = strlen($str);
            $this->_db->insertParams($tablename, $fielddefs, array('id' => $size, 'test' => $str, 'dummy' => $str));

            $select = "SELECT test FROM $tablename WHERE id = '{$size}'";
            $strresult = $this->_db->getOne($select);

			$this->assertNotEmpty($strresult, "Failed to read data just written to temp table");
            $this->assertEquals(0, mb_strpos($str, $strresult), "String returned from temp table did not match data just written");
        }
    }

    public function testGetIndicesContainsPrimary()
    {
        $indices = $this->_db->get_indices('accounts');

        // find if any are primary
        $found = false;

        foreach($indices as $index)
        {
            if($index['type'] == "primary") {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Primary Key Not Found On Module');
    }

    /*
     * testDBGuidGeneration
     * Tests that the first 1000 DB generated GUIDs are unique
     */
    public function testDBGuidGeneration()
    {

		$guids = array();
		$sql = "SELECT {$this->_db->getGuidSQL()} {$this->_db->getFromDummyTable()}";
		for($i = 0; $i < 1000; $i++)
		{
			$newguid = $this->_db->getOne($sql);
			$this->assertFalse(in_array($newguid, $guids), "'$newguid' already existed in the array of GUIDs!");
			$guids []= $newguid;
		}
	}

    public function testAddPrimaryKey()
    {
        $tablename = 'testConstraints';
        $fielddefs = array(
                        'id' =>
                            array (
                            'name' => 'id',
                            'required'=>true,
                            'type' => 'id',
                            ),
                        'test' => array (
                            'name' => 'test',
                            'type' => 'longtext',
                            ),
                        );

        $this->createTableParams($tablename, $fielddefs, array());
        unset($this->created[$tablename]); // that table is required by testRemovePrimaryKey test

        $sql = $this->_db->add_drop_constraint(
            $tablename,
            array(
                'name'   => 'testConstraints_pk',
                'type'   => 'primary',
                'fields' => array('id'),
                ),
            false
            );

        $result = $this->_db->query($sql);

        $indices = $this->_db->get_indices($tablename);

        // find if any are primary
        $found = false;

        foreach($indices as $index)
        {
            if($index['type'] == "primary") {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Primary Key Not Found On Table');
    }

    /**
     * @depends testAddPrimaryKey
     */
    public function testRemovePrimaryKey()
    {
        $tablename = 'testConstraints';
        $this->created[$tablename] = true;

         $sql = $this->_db->add_drop_constraint(
            $tablename,
            array(
                'name'   => 'testConstraints_pk',
                'type'   => 'primary',
                'fields' => array('id'),
                ),
            true
            );

        $result = $this->_db->query($sql);

        $indices = $this->_db->get_indices($tablename);

        // find if any are primary
        $found = false;

        foreach($indices as $index)
        {
            if($index['type'] == "primary") {
                $found = true;
                break;
            }
        }

        $this->assertFalse($found, 'Primary Key Found On Table');
    }


    private function addChildren($tableName, $parent_id, $parent_name, $number, $level, $stoplevel)
    {
        if($level >= $stoplevel) return;
        for($sibling = 0; $sibling < $number; $sibling++)
        {
            $id = create_guid();
            $name = "{$parent_name}_{$sibling}";
            $this->addRecord($tableName, $id, $parent_id, $name, $level);
            $this->addChildren($tableName, $id, $name, $number, $level + 1, $stoplevel);
        }
    }

    private function addRecord($tableName, $id, $parent_id, $name, $level)
    {
        $this->_db->query(sprintf(
            'INSERT INTO %s (id, parent_id, name, db_level) VALUES (%s, %s, %s, %s)',
            $tableName,
            $this->_db->quoted($id),
            ($parent_id !== null ? $this->_db->quoted($parent_id) : 'NULL'),
            $this->_db->quoted($name),
            $this->_db->quoted($level)
        ));
    }

    private function setupRecursiveStructure($tableName)
    {
        $params =  array(
            'id' => array (
                'name' => 'id',
                'type' => 'id',
                'required'=>true,
                ),
            'parent_id' => array (
                'name' => 'parent_id',
                'type' => 'id',
                ),
            'name' => array (
                'name' => 'name',
                'type' => 'varchar',
                'len' => '20',
                ),
           'db_level' => array (  // For verification purpose
                'name' => 'db_level',
                'type' => 'int',
                ),
        );
        $indexes = array(
            array(
                'name'   => 'idx_'. $tableName .'_id',
                'type'   => 'primary',
                'fields' => array('id'),
                ),
            array(
                'name'   => 'idx_'. $tableName .'parent_id',
                'type'   => 'index',
                'fields' => array('parent_id'),
                ),
        );

        $this->createTableParams($tableName, $params, $indexes);

        // Load data
        $id = create_guid();
        $name = '1';
        $this->addRecord($tableName, $id, NULL, $name, 0);
        $this->addChildren($tableName, $id, $name, 2, 1, 10);
    }

    public function providerRecursiveQuery()
    {
        return array(
            array('1_0_0_0_0_0_0_0_0_0', '9', 1),
            array('1_1_1_1_1_1_1_1_1_1', '9', 1),
            array('1_0_0_0_0_0_0_0_0', '8', 3),
            array('1_1_1_1_1_1_1_1', '7', 7),
            array('1_0_0_0_0_0_0', '6', 15),
            array('1_1_1_1_1_1', '5', 31),
            array('1_0_0_0_0', '4', 63),
            array('1_1_1_1', '3', 127),
            array('1_0_0', '2', 255),
            array('1_1', '1', 511),
            array('1', '0', 1023),
        );
    }

    /**
     * @dataProvider providerRecursiveQuery
     * @group hierarchy
     * @param $startName
     * @param $startDbLevel
     * @param $nrchildren
     */
    public function testRecursiveQuery($startName, $startDbLevel, $nrchildren)
    {
        if ( !$this->_db->supports('recursive_query') )
        {
            $this->markTestSkipped('DBManager does not support recursive query');
        }

        $this->_db->preInstall();

        // setup test table and fill it with data if it doesn't already exist
        $table = 'test_recursive';
        if (!$this->_db->tableExists($table)) {
            $this->setupRecursiveStructure($table);
        }

        $startId = $currentId = $this->_db->getOne('SELECT id FROM ' . $table . ' WHERE name = '
            . $this->_db->quoted($startName));
        $levels = $startDbLevel;

        // Testing lineage
        $lineageSQL = $this->_db->getRecursiveSelectSQL(
            $table,
            'id',
            'parent_id',
            'id, parent_id, name, db_level',
            true,
            'id = ' . $this->_db->quoted($startId)
        );

        $result = $this->_db->query($lineageSQL);

        $currentName = null;
        while($row = $this->_db->fetchByAssoc($result))
        {
            $currentName = $row['name'];
            $this->assertEquals($currentId, $row['id'], "Incorrect ID found");
            if (!empty($row['parent_id'])) {
                $currentId = $row['parent_id'];
            }
            $this->assertEquals($levels--, $row['db_level'], "Incorrect level found");
        }
        $this->assertEquals('1', $currentName, "Incorrect top node name");
        $this->assertEquals(0, $levels+1, "Incorrect end level"); //Compensate for extra -1 after last node level assert

        // Testing children
        $childcount = 0;
        $childrenSQL = $this->_db->getRecursiveSelectSQL(
            $table,
            'id',
            'parent_id',
            // select ID even if we don't need it because the MSSQL implementation will use if (or parent ID)
            // internally depending on the value of $lineage (probably should be fixed)
            'id, parent_id, name',
            false,
            'id = ' . $this->_db->quoted($startId)
        );

        $result = $this->_db->query($childrenSQL);

        while(($row = $this->_db->fetchByAssoc($result)) != null)
        {
            $this->assertStringStartsWith(
                $startName,
                $row['name'],
                'Row id doesn\'t start with starting name as expected'
            );
            $childcount++;
        }
        $this->assertEquals($nrchildren, $childcount, "Number of found descendants does not match expectations");
    }

    // Inserts a 2D array of data into the specified table
    // First row of array must be the column headers, first column must be PK column name
    public function insertTableArray( $tableName, $tableDataArray ) {

        $sqlPrefix = "INSERT INTO $tableName ( {$tableDataArray[0][0]} ";  // has to be at least one column
        for ($col = 1; $col < count($tableDataArray[0]); $col++) {
            $sqlPrefix .= ", {$tableDataArray[0][$col]}";
        }
        $sqlPrefix .= ") VALUES (";
        $sqlPostfix = ")";
        $sqlBody = "";

        // do the inserts for each row of data
        for ($row = 1; $row< count($tableDataArray); $row++) {
            $rowData = $tableDataArray[$row];
            $sqlBody = "'{$rowData[0]}'";
            for ($col = 1; $col < count($rowData); $col++) {
                $sqlBody .= ",'{$rowData[$col]}'";
            }

            // Insert the data
            $sql = $sqlPrefix . $sqlBody . $sqlPostfix;
            $result = $this->_db->query($sql);
        }
    }

    // Deletes a 2D array of data from the specified table
    // First row of array must be column headers, first column must be PK column name
    public function deleteTableArray( $tableName, $tableDataArray ) {

        $sql = "DELETE FROM $tableName WHERE {$tableDataArray[0][0]} IN ( '{$tableDataArray[1][0]}'";  // has to be at least one column
        for ($row = 2; $row < count($tableDataArray); $row++) {
            $sql .= ",'{$tableDataArray[$row][0]}'";
        }
        $sql .= ")";

        // Delete the data
        $result = $this->_db->query($sql);

    }


    /**
     */
    public function testRecursiveQueryMultiHierarchy()
    {

        if ( !$this->_db->supports('recursive_query') )
        {
            $this->markTestSkipped('DBManager does not support recursive query');
        }

        $this->_db->preInstall();

        // Setup test data
        $tableName = 'forecast_tree';
        $this->assertTrue($this->_db->tableExists($tableName), "Table $tableName does not exist");
        $tableDataArray = array(  array( 'id',             'parent_id',      'name',     'hierarchy_type', 'user_id' )
        , array( 'sales_test_1',    null,            'sales1',   'sales_test',     'user1'   )
        , array( 'sales_test_11',  'sales_test_1',   'sales11',  'sales_test',     'user11'  )
        , array( 'sales_test_12',  'sales_test_1',   'sales12',  'sales_test',     'user12'  )
        , array( 'sales_test_13',  'sales_test_1',   'sales13',  'sales_test',     'user13'  )
        , array( 'sales_test_121', 'sales_test_12',  'sales121', 'sales_test',     'user121' )
        , array( 'sales_test_122', 'sales_test_12',  'sales122', 'sales_test',     'user122' )
        , array( 'sales_test_131', 'sales_test_13',  'sales131', 'sales_test',     'user131' )
        , array( 'sales_test_132', 'sales_test_13',  'sales132', 'sales_test',     'user132' )
        , array( 'sales_test_133', 'sales_test_13',  'sales133', 'sales_test',     'user133' )
        , array( 'prod_test_1',     null,            'prod1',    'prod_test',      'user1'   )
        , array( 'prod_test_11',   'prod_test_1',    'prod11',   'prod_test',      'user11'  )
        , array( 'prod_test_12',   'prod_test_1',    'prod12',   'prod_test',      'user12'  )
        , array( 'prod_test_13',   'prod_test_1',    'prod13',   'prod_test',      'user13'  )
        , array( 'prod_test_121',  'prod_test_12',   'prod121',  'prod_test',      'user121' )
        , array( 'prod_test_122',  'prod_test_12',   'prod122',  'prod_test',      'user122' )
        , array( 'prod_test_131',  'prod_test_13',   'prod131',  'prod_test',      'user131' )
        , array( 'prod_test_132',  'prod_test_13',   'prod132',  'prod_test',      'user132' )
        , array( 'prod_test_133',  'prod_test_13',   'prod133',  'prod_test',      'user133' )
        , array( 'prod_test_1321', 'prod_test_132',  'prod1321', 'prod_test',      'user1321')
        );

        $this->insertTableArray( $tableName, $tableDataArray );

        // idStarting, Up/Down, Forecast_Tree Type, expected result count
        $resultsDataArray = array( array('sales_test_1',    false, 'sales_test',9)
                                  ,array('sales_test_13',   false, 'sales_test',4)
                                  ,array('sales_test_131',  true,  'sales_test',3)
                                  ,array('sales_test_13',   true,  'sales_test',2)
                                  ,array('sales_test_1',    true,  'sales_test',1)
                                  ,array('prod_test_1',     false, 'prod_test',10)
                                  ,array('prod_test_13',    false, 'prod_test', 5)
                                  ,array('prod_test_1321',  true,  'prod_test', 4)
                                  ,array('prod_test_133',   true,  'prod_test', 3)
                                  ,array('prod_test_1',     true,  'prod_test', 1)
        );

        // Loop through each test
        foreach ( $resultsDataArray as $resultsRow ) {

            // Get where clause
            $whereClause = "hierarchy_type='$resultsRow[2]'";

            // Get hierarchical result set
            $key = 'id';
            $parent_key = 'parent_id';
            $fields = 'id, parent_id';
            $lineage = $resultsRow[1];
            $startWith = "id = '{$resultsRow[0]}'";
            $level = null;

            $hierarchicalSQL = $this->_db->getRecursiveSelectSQL($tableName, $key, $parent_key, $fields, $lineage, $startWith, $level, $whereClause);
            $result = $this->_db->query($hierarchicalSQL);
            $resultsCnt = 0;

            while(($row = $this->_db->fetchByAssoc($result)) != null)
            {
                $resultsCnt++;
            }

            $this->assertEquals($resultsCnt, $resultsRow[3], "Incorrect number or records. Found: $resultsCnt Expected: $resultsRow[3] for ID: $resultsRow[0]");
        }

        // remove data from table
        $result = $this->deleteTableArray( $tableName, $tableDataArray );

    }

    /**
     * @group dbmanager
     * @group db
     * @ticket PAT-2513
     */
    public function testMassageValueReturnIsZeroForFloatWhenValIsNullAndFieldIsRequired()
    {
        $fieldDef = array(
            'name' => 'test_field',
            'type' => 'float',
            'required' => true,
            'default' => '',
        );

        $return = $this->_db->massageValue('', $fieldDef);

        $this->assertEquals('0', $return);
    }

    /**
     * @group dbmanager
     * @group db
     * @ticket PAT-2513
     */
    public function testMassageValueReturnIsNullForFloatWhenValIsNullAndFieldIsNotRequired()
    {
        $fieldDef = array(
            'name' => 'test_field',
            'type' => 'float',
            'required' => false,
            'default' => '',
        );

        $return = $this->_db->massageValue('', $fieldDef);

        $this->assertEquals('NULL', $return);
    }

    /**
     * @group dbmanager
     * @group db
     * @ticket 61597
     */
    public function testMessageValueReturnIsNotEmptyStringWhenEnumFieldIsRequiredAndDefaultIsEmptyAndValIsNull()
    {
        $fieldDef = array(
            'name' => 'test_field',
            'type' => 'enum',
            'required' => true,
            'default' => ''
        );

        $return = $this->_db->massageValue(null, $fieldDef);

        $this->assertEquals("''", $return);
    }

    /**
     * @group dbmanager
     * @group db
     * @ticket 61597
     */
    public function testMessageValueReturnIsNullWhenEnumFieldIsNotRequiredAndDefaultIsEmptyAndValIsNull()
    {
        $fieldDef = array(
            'name' => 'test_field',
            'type' => 'enum',
            'required' => false,
            'default' => ''
        );

        $return = $this->_db->massageValue(null, $fieldDef);

        $this->assertEquals('NULL', $return);
    }

    public function searchStringProvider()
    {
        return array(
            array(
                'wildcard' => '%',
                'inFront' => false,
                'search' => 'test*test2',
                'expected' => 'test*test2%'
            ),
            array(
                'wildcard' => '*',
                'inFront' => false,
                'search' => 'test*test2',
                'expected' => 'test%test2%'
            ),
            array(
                'wildcard' => '%',
                'inFront' => true,
                'search' => 'test*test2',
                'expected' => '%test*test2%'
            ),
            array(
                'wildcard' => '*',
                'inFront' => true,
                'search' => 'test*test2',
                'expected' => '%test%test2%'
            ),
            array(
                'wildcard' => '',
                'inFront' => true,
                'search' => 'test*test2',
                'expected' => '%test*test2%'
            ),
        );
    }

    /**
     * Unit test for DBManager::sqlLikeString().
     *
     * @dataProvider searchStringProvider
     * @param $wildcard string The sugar config wildcard character.
     * @param $inFront bool The sugar config to prepend the search string with a wildcard.
     * @param $search string The search query string.
     * @param $expected string The expected return value upon calling DBManager::sqlLikeString().
     */
    public function testSqlLikeString($wildcard, $inFront, $search, $expected)
    {
        $defaultConfigWildcard = $GLOBALS['sugar_config']['search_wildcard_char'];
        $defaultWildcardInFront = $GLOBALS['sugar_config']['search_wildcard_infront'];

        $GLOBALS['sugar_config']['search_wildcard_char'] = $wildcard;
        $GLOBALS['sugar_config']['search_wildcard_infront'] = $inFront;

        $str = $this->_db->sqlLikeString($search);
        $this->assertEquals($expected, $str);

        $GLOBALS['sugar_config']['search_wildcard_char'] = $defaultConfigWildcard;
        $GLOBALS['sugar_config']['search_wildcard_infront'] = $defaultWildcardInFront;
    }

    /**
     * Returns def and its expectation
     *
     * @return array
     */
    static public function getTypeForOneColumnSQLRep()
    {
        return array(
            array(
                array(
                    'name' => 'test',
                    'type' => 'date',
                    'default' => '',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'datetime',
                    'default' => '',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'encrypt',
                    'default' => '',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'dropdown',
                    'default' => '',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'decimal',
                    'default' => '',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'float',
                    'default' => '',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'integer',
                    'default' => '',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'date',
                    'default' => 'not-empty',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'datetime',
                    'default' => 'not-empty',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'encrypt',
                    'default' => 'not-empty',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'dropdown',
                    'default' => 'not-empty',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'decimal',
                    'default' => 'not-empty',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'float',
                    'default' => 'not-empty',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'integer',
                    'default' => 'not-empty',
                ),
                'once',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'date',
                    'default' => 'not-empty',
                    'no_default' => true,
                ),
                'never',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'datetime',
                    'default' => 'not-empty',
                    'no_default' => true,
                ),
                'never',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'encrypt',
                    'default' => 'not-empty',
                    'no_default' => true,
                ),
                'never',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'dropdown',
                    'default' => 'not-empty',
                    'no_default' => true,
                ),
                'never',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'decimal',
                    'default' => 'not-empty',
                    'no_default' => true,
                ),
                'never',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'float',
                    'default' => 'not-empty',
                    'no_default' => true,
                ),
                'never',
            ),
            array(
                array(
                    'name' => 'test',
                    'type' => 'integer',
                    'default' => 'not-empty',
                    'no_default' => true,
                ),
                'never',
            ),
        );
    }
    /**
     * Testing that massageValue is called only when we need that
     *
     * @dataProvider getTypeForOneColumnSQLRep
     */
    public function testOneColumnSQLRep($fieldDef, $expected)
    {
        $db = $this->getMockBuilder(get_class($this->_db))->setMethods(array('massageValue'))->getMock();
        $method = $db->expects($this->$expected())->method('massageValue');
        if ($expected != 'never') {
            $method->with($this->equalTo($fieldDef['default']), $this->equalTo($fieldDef))->will($this->returnValue("correct"));
        }

        $result = SugarTestReflection::callProtectedMethod($db, 'oneColumnSQLRep', array($fieldDef));
        if ($expected == 'once') {
            $this->assertContains('correct', $result);
        } elseif ($expected == 'never') {
            $this->assertNotContains('correct', $result);
        }
    }

    public function lengthTestProvider()
    {
        $data = array(
            array(
                array('len' => '5'),
                array('len' => '4', 'precision' => '2'),
                "7,2"
            ),
            array(
                array('len' => '4'),
                array('len' => '12', 'precision' => '2'),
                "12,2"
            ),
            array(
                array('type' => 'decimal', 'len' => '10', 'precision' => '6'),
                array('len' => '12', 'precision' => '2'),
                "12,6"
            ),
            array(
                array('type' => 'decimal', 'len' => '12', 'precision' => '6'),
                array('len' => '14', 'precision' => '6'),
                "14,6"
            ),
            array(
                array('len' => '4,2', 'precision' => '2', 'type' => 'decimal'),
                array('len' => '26,2', 'precision' => '2'),
                "26,2"
            ),
        );

        $result = array();
        foreach (array(
            'MysqliManager',
            'SqlsrvManager',
            'IBMDB2Manager',
        ) as $driver) {
            foreach ($data as $item) {
                $item[] = $driver;
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * @ticket BR-1787
     * @group unit
     * @dataProvider lengthTestProvider
     */
    public function testChangeFieldLength($dbcol, $vardefcol, $result, $driver)
    {
        DBManagerFactory::getDbDrivers(); // load the drivers
        $DBManagerClass = get_class($this->_db);
        $db_columns = array(
            "id" => array("name" => "id", 'type' => 'char', 'len' => '36'),
            "quantity" => array("name" => "quantity", 'type' => 'int', 'len' => '5'),
        );
        $db_columns['quantity'] = array_merge($db_columns['quantity'], $dbcol);

        $vardefs = array(
            "id" => array("name" => "id", 'type' => 'id', 'len' => '36'),
            "quantity" => array("name" => "quantity", 'type' => 'decimal', 'len' => '4', 'precision' => '2'),
        );
        $vardefs['quantity'] = array_merge($vardefs['quantity'], $vardefcol);

        // Oracle currently forces decimals to be 20,2 - can't test here
        $dbmock = $this->getMockBuilder($driver)
            ->setMethods(array('get_columns', 'get_field_default_constraint_name', 'get_indices', 'checkIdentity'))
            ->getMock();
        if (!($dbmock instanceof DBManager)) {
                // Failed to instantiate the driver, skip it
                $this->markTestSkipped("Could not load driver for $driver");
        }
        $dbmock->expects($this->any())
               ->method('get_columns')
               ->will($this->returnValue($db_columns));
        $dbmock->expects($this->any())
            ->method('get_field_default_constraint_name')
            ->will($this->returnValue(array()));
        $dbmock->expects($this->any())
            ->method('get_indices')
            ->will($this->returnValue(array()));
        $dbmock->expects($this->any())
            ->method('checkIdentity')
            ->will($this->returnValue(false));

        $sql = SugarTestReflection::callProtectedMethod($dbmock, 'repairTableColumns', array("faketable", $vardefs, false));
        $this->assertRegExp("#quantity.*?decimal\\($result\\)#i", $sql, "Bad length change for $driver");
    }

    public function isNullableProvider()
    {
        return array(
            array(
                array (
                    'name' => 'id',
                    'vname' => 'LBL_TAG_NAME',
                    'type' => 'id',
                    'len' => '36',
                    'required'=>true,
                    'reportable'=>false,
                ),
                false,
            ),
            array(
                array (
                    'name' => 'parent_tag_id',
                    'vname' => 'LBL_PARENT_TAG_ID',
                    'type' => 'id',
                    'len' => '36',
                    'required'=>false,
                    'reportable'=>false,
                ),
                true,
            ),
            array(
                array (
                    'name' => 'any_id',
                    'vname' => 'LBL_ANY_ID',
                    'dbType' => 'id',
                    'len' => '36',
                    'required'=>false,
                    'reportable'=>false,
                ),
                true,
            ),
            array(
                array (
                    'name' => 'any_id',
                    'vname' => 'LBL_ANY_ID',
                    'type' => 'id',
                    'len' => '36',
                    'reportable'=>false,
                ),
                true,
            ),
            array(
                array (
                    'name' => 'any_id',
                    'vname' => 'LBL_ANY_ID',
                    'dbType' => 'id',
                    'len' => '36',
                    'reportable'=>false,
                ),
                true,
            ),
        );
    }

    /**
     * @ticket PAT-579
     * @dataProvider isNullableProvider
     */
    public function testIsNullable($vardef, $isNullable)
    {
        $this->assertEquals($isNullable, SugarTestReflection::callProtectedMethod($this->_db, 'isNullable', array($vardef)));
    }

    private function setupInsertStructure()
    {
        // create test table for operational testing
        $tableName = 'test_insert';
        $params =  array(
            'id' => array (
                'name' => 'id',
                'type' => 'id',
                'required'=>true,
            ),
            'col1' => array (
                'name' => 'col1',
                'type' => 'varchar',
                'len' => '100',
            ),
            'col2' => array (
                'name' => 'col2',
                'type' => 'text',
                'len' =>'200000',
            ),
            'col3' => array(
                'name' => 'col3',
                'type' => 'date',
            )
        );
        $indexes = array(
            array(
                'name'   => 'idx_'. $tableName .'_id',
                'type'   => 'primary',
                'fields' => array('id'),
            ),
        );
        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, $indexes);

        return array('tableName' => $tableName,
                     'params' => $params);
    }

    public function providerInsert()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'col1' => "col1 data",
                    'col2' => "col2 data",
                    'col3' => '2012-12-31',
                )
            ),
            array(
                array(
                    'id' => 2,
                    'col1' => "2",
                    'col2' => "col2 data",
                    'col3' => '2012-12-31',
                )
            ),
        );
    }

    /**
     * @dataProvider providerInsert
     * @param $data
     */
    public function testInsertParams($data)
    {
        $dataStructure = $this->setupInsertStructure();
        $params = $dataStructure['params'];
        $tableName = $dataStructure['tableName'];
        $this->assertTrue($this->_db->insertParams($tableName, $params, $data));
        $resultsCntExpected = 1;

        $result = $this->_db->query("SELECT * FROM $tableName");
        $resultsCnt = 0;
        while(($row = $this->_db->fetchByAssoc($result)) != null)  {
            $resultsCnt++;
        }
        $this->assertEquals($resultsCnt, $resultsCntExpected, "Incorrect number or records. Found: $resultsCnt Expected: $resultsCntExpected");
    }

    public function testInsertBlob()
    {
        $dataStructure = $this->setupInsertStructure();
        $params = $dataStructure['params'];
        $tableName = $dataStructure['tableName'];
        $this->_db->query("DELETE FROM $tableName");

        $blobData = '0123456789abcdefghijklmnopqrstuvwxyz';
        while(strlen($blobData) < 100000) {
            $blobData .= $blobData;
        }

        $data = array( 'id'=> '1', 'col1' => '10', 'col2' => $blobData);
        $this->assertTrue($this->_db->insertParams($tableName, $params, $data));

        $result = $this->_db->query("SELECT * FROM $tableName");
        $row = $this->_db->fetchByAssoc($result);
        $foundLen = strlen($row['col2']);
        $expectedLen = strlen($blobData);
        $this->assertEquals($row['col2'], $blobData, "Failed test writing blob data. Found: $foundLen chars, Expected: $expectedLen");
    }

    public function testInsertUpdateBean()
    {
        // insert test
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id = create_guid();
        $bean->description = 'description' . mt_rand();
        $bean->new_with_id = true;
        $this->_db->insert($bean);

        $result = $this->_db->query("select id, last_name, description from contacts where id = '{$bean->id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($bean->last_name, $row['last_name'], 'last_name failed');
        $this->assertEquals($bean->description, $row['description'], 'description failed');
        $this->assertEquals($bean->id, $row['id'],'id failed');

        // update test
        $bean->last_name = 'newfoobar' . mt_rand();   // change their lastname field
        $bean->description = 'newdescription' . mt_rand();
        $this->_db->update($bean, array('id'=>$bean->id));
        $result = $this->_db->query("select id, last_name, description from contacts where id = '{$bean->id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($bean->last_name, $row['last_name'], 'last_name failed');
        $this->assertEquals($bean->description, $row['description'], 'description failed');
        $this->assertEquals($bean->id, $row['id'], 'id failed');
    }

    private function setupDataTypesStructure()
    {
        // create test table for data type testing
        $tableName = 'test_types';
        $params =  array( 'id'                  =>array ('name'=>'id',                  'type'=>'id','required'=>true),
                            'int_param'           =>array ('name'=>'int_param',           'type'=>'int',     'default'=>1),
                            'double_param'        =>array ('name'=>'double_param',        'type'=>'double',  'default'=>1),     //len,precision
                            'float_param'         =>array ('name'=>'float_param',         'type'=>'float',   'default'=>1),
                            'uint_param'          =>array ('name'=>'uint_param',          'type'=>'uint',    'default'=>1),
                            'ulong_param'         =>array ('name'=>'ulong_param',         'type'=>'ulong',   'default'=>1),
                            'long_param'          =>array ('name'=>'long_param',          'type'=>'long',    'default'=>1),
                            'short_param'         =>array ('name'=>'short_param',         'type'=>'short',   'default'=>1),
                            'varchar_param'       =>array ('name'=>'varchar_param',       'type'=>'varchar', 'default'=>'test'),
                            'text_param'          =>array ('name'=>'text_param',          'type'=>'text',    'default'=>'test'),
                            'longtext_param'      =>array ('name'=>'longtext_param',      'type'=>'longtext','default'=>'test'),
                          'date_param'          =>array ('name'=>'date_param',          'type'=>'date'),
                            'enum_param'          =>array ('name'=>'enum_param',          'type'=>'enum',    'default'=>'test'),
                            'relate_param'        =>array ('name'=>'relate_param',        'type'=>'relate',  'default'=>'test'),
                            'multienum_param'     =>array ('name'=>'multienum_param',     'type'=>'multienum', 'default'=>'test'),
                            'html_param'          =>array ('name'=>'html_param',          'type'=>'html',    'default'=>'test'),
                            'longhtml_param'      =>array ('name'=>'longhtml_param',      'type'=>'longhtml','default'=>'test'),
                          'datetime_param'      =>array ('name'=>'datetime_param',      'type'=>'datetime'),
                          'datetimecombo_param' =>array ('name'=>'datetimecombo_param', 'type'=>'datetimecombo'),
                          'time_param'          =>array ('name'=>'time_param',          'type'=>'time'),
                          'bool_param'          =>array ('name'=>'bool_param',          'type'=>'bool'),
                          'tinyint_param'       =>array ('name'=>'tinyint_param',       'type'=>'tinyint'),
                            'char_param'          =>array ('name'=>'char_param',          'type'=>'char',    'default'=>'test'),
                            'id_param'            =>array ('name'=>'id_param',            'type'=>'id',      'default'=>'test'),
                            'blob_param'          =>array ('name'=>'blob_param',          'type'=>'blob',    'default'=>'test'),
                            'longblob_param'      =>array ('name'=>'longblob_param',      'type'=>'longblob','default'=>'test'),
                            'currency_param'      =>array ('name'=>'currency_param',      'type'=>'currency','default'=>1.11),
                            'decimal_param'       =>array ('name'=>'decimal_param',       'type'=>'decimal', 'len' => 10, 'precision' => 4,    'default'=>1.11),
                            'decimal2_param'      =>array ('name'=>'decimal2_param',      'type'=>'decimal2', 'len' => 10, 'precision' => 4,    'default'=>1.11),
                            'url_param'           =>array ('name'=>'url_param',           'type'=>'url',     'default'=>'test'),
                            'encrypt_param'       =>array ('name'=>'encrypt_param',       'type'=>'encrypt', 'default'=>'test'),
                            'file_param'          =>array ('name'=>'file_param',          'type'=>'file',    'default'=>'test'),
                        );

        $indexes = array(
            array(
                'name'   => 'idx_'. $tableName .'_id',
                'type'   => 'primary',
                'fields' => array('id'),
            ),
        );
        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, $indexes);

        return array('tableName' => $tableName,
                     'params' => $params);
    }


    /**
     * Each row is inserted and then read back and checked, including defaults.
     */
    private function setupDataTypesData()
    {
        return array(array( 'id'                  => create_guid(),
                        'int_param'           => 1,
                        'double_param'        => 1,
                        'float_param'         => 1.1,
                        'uint_param'          => 1,
                        'ulong_param'         => 1,
                        'long_param'          => 1,
                        'short_param'         => 1,
                        'varchar_param'       => 'varchar',
                        'text_param'          => 'text',
                        'longtext_param'      => 'longtext',
                        'date_param'          => '2012-12-31',
                        'enum_param'          => 'enum',
                        'relate_param'        => 'relate',
                        'multienum_param'     => 'multienum',
                        'html_param'          => 'html',
                        'longhtml_param'      => 'longhtml',
                        'datetime_param'      => '2012-12-31 01:01:01',
                        'datetimecombo_param' => '2012-12-31 01:01:01',
                        'time_param'          => '01:01:01',
                        'bool_param'          => '1',
                        'tinyint_param'       => 1,
                        'char_param'          => 'char',
                        'id_param'            => 'id',
                        'blob_param'          => 'blob',
                        'longblob_param'      => 'longblob',
                        'currency_param'      => 123.456,
                        'decimal_param'       => 12.34,
                        'decimal2_param'      => 12.34,
                        'url_param'           => 'utl',
                        'encrypt_param'       => 'encrypt',
                        'file_param'          => 'file',
                          ),
                     array( 'id'                  => create_guid(),
                            'int_param'           => 2,
                            'double_param'        => 2,
                            'float_param'         => 2.2,
                            'uint_param'          => 2,
                            'ulong_param'         => 2,
                            'long_param'          => 2,
                            'short_param'         => 2,
                            'varchar_param'       => 'varchar',
                            'text_param'          => 'text',
                            'longtext_param'      => 'longtext',
                            'date_param'          => '2012-12-31',
                            'enum_param'          => 'enum',
                            'relate_param'        => 'relate',
                            'multienum_param'     => 'multienum',
                            'html_param'          => 'html',
                            'longhtml_param'      => 'longhtml',
                            'datetime_param'      => '2012-12-31 01:01:01',
                            'datetimecombo_param' => '2012-12-31 01:01:01',
                            'time_param'          => '01:01:01',
                            'bool_param'          => '1',
                            'tinyint_param'       => 1,
                            'char_param'          => 'char',
                            'id_param'            => 'id',
                            'blob_param'          => 'blob',
                            'longblob_param'      => 'longblob',
                            'currency_param'      => 123.456,
                            'decimal_param'       => 12.34,
                            'decimal2_param'      => 12.34,
                            'url_param'           => 'utl',
                            'encrypt_param'       => 'encrypt',
                            'file_param'          => 'file',
                          ),
                     array( 'id'                  => create_guid(),
                            'int_param'           => 3,
                            'double_param'        => 3,
                        ),
        );
    }

    public function testDataTypes()
    {
        // create data table
        $dataStructure = $this->setupDataTypesStructure();
        $params = $dataStructure['params'];
        $tableName = $dataStructure['tableName'];

        // load and test each data record
        $dataArray = $this->setupDataTypesData();

        foreach($dataArray as $data) {  // insert a single row of data and check it column by column
            $this->_db->insertParams($tableName, $params, $data);
            $id = $data['id'];
            $result = $this->_db->query("SELECT * FROM $tableName WHERE id = " . $this->_db->quoted($id));
            while(($row = $this->_db->fetchByAssoc($result)) != null) {
                    foreach ($data as $colKey => $col ) {
                        $found = $this->_db->fromConvert($row[$colKey], $params[$colKey]['type']);
                        $expected=$data[$colKey];
                        if (empty($expected)) { // if null then compare to the table defined default
                            $expected = $params[$colKey]['default'];
                        }
                        $this->assertEquals( $expected, $found, "Failed prepared statement data compare for column $colKey. Found: $found  Expected: $expected");
                    }
            }
        }
    }

    /**
     * This test is checking conversion blob field to clob
     * @param string $data Data for insert into table
     *
     * @dataProvider providerBlobToClob
     */
    public function testAlterTableBlobToClob($data)
    {
        if (!($this->_db instanceof IBMDB2Manager)) {
            $this->markTestSkipped('This test can run only on DB2 instance');
        }
        $params = array(
            'logmeta' => array(
                'name' => 'logmeta',
                'vname' => 'LBL_LOGMETA',
                'type' => 'json',
                'dbType' => 'longblob',
            ),
        );
        $tableName = 'testAlterTableBlobToClob' . mt_rand();

        if ($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, array());

        $this->_db->insertParams($tableName, $params, array('logmeta' => $data));

        $params = array(
            'logmeta' => array(
                'name' => 'logmeta',
                'vname' => 'LBL_LOGMETA',
                'type' => 'json',
                'dbType' => 'longtext',
            ),
        );

        $this->_db->repairTableParams($tableName, $params, array(), true);

        $columns = $this->_db->get_columns($tableName);
        $this->assertEquals('clob', $columns['logmeta']['type']);

        $checkResult = $this->_db->fetchOne('SELECT logmeta FROM ' . $tableName);
        $this->assertEquals($data, $checkResult['logmeta']);
    }

    public function testCreateTableSQL()
    {
        $sql = $this->_db->createTableSQL(new Contact);

        $this->assertRegExp('/create\s*table\s*contacts/i',$sql);
    }

    public function testCreateTableSQLParams()
    {
        $bean = BeanFactory::newBean('Contacts');

        $sql = $this->_db->createTableSQLParams(
            $bean->getTableName(),
            $bean->getFieldDefinitions(),
            $bean->getIndices());

        $this->assertRegExp('/create\s*table\s*contacts/i',$sql);
    }

    public function testCreateIndexSQL()
    {
        $sql = $this->_db->createIndexSQL(
            new Contact,
            array('id' => array('name'=>'id')),
            'idx_id');

        $this->assertRegExp('/create\s*unique\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*\)/i',$sql);

        $sql = $this->_db->createIndexSQL(
            new Contact,
            array('id' => array('name'=>'id')),
            'idx_id',
            false);

        $this->assertRegExp('/create\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*\)/i',$sql);

        $sql = $this->_db->createIndexSQL(
            new Contact,
            array('id' => array('name'=>'id'),'deleted' => array('name'=>'deleted')),
            'idx_id');

        $this->assertRegExp('/create\s*unique\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*,\s*deleted\s*\)/i',$sql);
    }

    public function testGetFieldType()
    {
        $fieldDef = array(
            'dbType'    => 'varchar',
            'dbtype'    => 'int',
            'type'      => 'char',
            'Type'      => 'bool',
            'data_type' => 'email',
        );

        $this->assertEquals($this->_db->getFieldType($fieldDef),'varchar');
        unset($fieldDef['dbType']);
        $this->assertEquals($this->_db->getFieldType($fieldDef),'int');
        unset($fieldDef['dbtype']);
        $this->assertEquals($this->_db->getFieldType($fieldDef),'char');
        unset($fieldDef['type']);
        $this->assertEquals($this->_db->getFieldType($fieldDef),'bool');
        unset($fieldDef['Type']);
        $this->assertEquals($this->_db->getFieldType($fieldDef),'email');
    }
    public function testGetAutoIncrement()
    {
        $case = BeanFactory::newBean('Cases');
        $case->name = "foo";
        $case->save();
        $case->retrieve($case->id);
        $lastAuto = $case->case_number;
        $helperResult = $this->_db->getAutoIncrement("cases", "case_number");

        $this->assertEquals($lastAuto + 1, $helperResult);
    }

    public function testGetAutoIncrementSQL()
    {
        if( $this->_db->dbType != 'oci8') {
            $this->markTestSkipped('Only applies to Oracle');
        }

        $sql = $this->_db->getAutoIncrementSQL('cases', 'case_number');
        $this->assertRegExp('/cases_case_number_seq\.nextval/i',$sql);
    }

    public function testSetAutoIncrementStart()
    {
        $case = BeanFactory::newBean('Cases');
        $case->name = "foo";
        $case->save();
        $case->retrieve($case->id);
        $lastAuto = $case->case_number;
        $case->deleted = true;
        $case->save();
        $newAuto = $lastAuto + 5;
        $this->_db->setAutoIncrementStart("cases", "case_number", $newAuto);
        $case2 = BeanFactory::newBean('Cases');
        $case2->name = "foo2";
        $case2->save();
        $case2->retrieve($case2->id);
        $case_number = $case2->case_number;

        $GLOBALS['db']->query("DELETE FROM cases WHERE id= '{$case->id}'");
        $GLOBALS['db']->query("DELETE FROM cases WHERE id= '{$case2->id}'");

        $this->assertEquals($newAuto, $case_number);
    }
    public function testAddColumnSQL()
    {
        $sql = $this->_db->addColumnSQL(
            'contacts',
            array('foo' => array('name'=>'foo','type'=>'varchar'))
        );

        $this->assertRegExp('/alter\s*table\s*contacts/i',$sql);
    }

    public function testAlterColumnSQL()
    {
        $sql = $this->_db->alterColumnSQL(
            'contacts',
            array('foo' => array('name'=>'foo','type'=>'varchar'))
        );

        // Generated SQL may be a sequence of statements
        switch(gettype($sql)){
            case 'array':
                $sql = $sql[0];
            case 'string':
                $this->assertRegExp('/alter\s*table\s*contacts/i',$sql);
                break;
        }
    }

    public function testDropTableSQL()
    {
        $sql = $this->_db->dropTableSQL(new Contact);

        $this->assertRegExp('/drop\s*table.*contacts/i',$sql);
    }

    public function testDropTableNameSQL()
    {
        $sql = $this->_db->dropTableNameSQL('contacts');

        $this->assertRegExp('/drop\s*table.*contacts/i',$sql);
    }

    public function testDeleteColumnSQL()
    {
        $sql = $this->_db->deleteColumnSQL(
            new Contact,
            array('foo' => array('name'=>'foo','type'=>'varchar'))
        );
        if ( $this->_db->dbType == 'oci8' )
            $this->assertRegExp('/alter\s*table\s*contacts\s*drop\s*\(\s*foo\s*\)/i', $sql);
        else
            $this->assertRegExp('/alter\s*table\s*contacts\s*drop\s*column\s*foo/i',$sql);
    }

    public function testDropColumnSQL()
    {
        $tableName = 'drop_columns_sql_test';

        $id = array(
            'name' => 'id',
            'type' => 'id'
        );

        $field1 = array(
            'name' => 'test1',
            'type' => 'int'
        );

        $field2 = array(
            'name' => 'test2',
            'type' => 'int'
        );

        $this->createTableParams(
            $tableName,
            array(
                'id' => $id,
                'test1' => $field1,
                'test2' => $field2
            ),
            array()
        );

        $this->assertNotFalse(
            $this->_db->query(
                $this->_db->dropColumnSQL($tableName, $field1)
            )
        );

        $this->assertNotFalse(
            $this->_db->query(
                $this->_db->dropColumnSQL($tableName, array($field2))
            )
        );

        $this->_db->addColumn($tableName, array($field1, $field2));

        $this->assertNotFalse(
            $this->_db->query(
                $this->_db->dropColumnSQL($tableName, array($field1, $field2))
            )
        );
    }

    public function testMassageValue()
    {
        $this->assertEquals(
            $this->_db->massageValue(123,array('name'=>'foo','type'=>'int')),
            123
        );
        if (in_array($this->_db->dbType, array('mssql'
        ,'oci8', 'ibm_db2'
        )))
            $this->assertEquals(
                $this->_db->massageValue("'dog'",array('name'=>'foo','type'=>'varchar')),
                "'''dog'''"
            );
        else
            $this->assertEquals(
                $this->_db->massageValue("'dog'",array('name'=>'foo','type'=>'varchar')),
                "'\\'dog\\''"
            );
    }

    public function testGetColumnType()
    {
        switch($this->_db->dbType){
            case 'oci8': $expected_type = 'number'; break;
            case 'ibm_db2': $expected_type = 'integer'; break;
            default:
                $expected_type = 'int';
        }

        $this->assertEquals($expected_type, $this->_db->getColumnType('int'));
    }

    public function testIsFieldArray()
    {
        $this->assertTrue(
            $this->_db->isFieldArray(array('name'=>'foo','type'=>array('int')))
        );

        $this->assertFalse(
            $this->_db->isFieldArray(array('name'=>'foo','type'=>'int'))
        );

        $this->assertTrue(
            $this->_db->isFieldArray(array('name'=>'foo'))
        );

        $this->assertFalse(
            $this->_db->isFieldArray(1)
        );
    }

    public function testQuoted()
    {
        $this->assertEquals(
            "'".$this->_db->quote('foobar')."'",
            $this->_db->quoted('foobar')
        );
    }

    public function testGetIndices()
    {
        $indices = $this->_db->get_indices('contacts');

        foreach ( $indices as $index ) {
            $this->assertTrue(!empty($index['name']));
            $this->assertTrue(!empty($index['type']));
            $this->assertTrue(!empty($index['fields']));
        }
    }

    public function testAddDropConstraint()
    {
        $tablename = 'test' . date("YmdHis");
        $sql = $this->_db->add_drop_constraint(
            $tablename,
            array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            ),
            false
        );

        $this->assertRegExp("/idx_foo/i",$sql);
        $this->assertRegExp("/foo/i",$sql);

        $tablename = 'test' . date("YmdHis");
        $sql = $this->_db->add_drop_constraint(
            $tablename,
            array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            ),
            true
        );

        $this->assertRegExp("/idx_foo/i",$sql);
        $this->assertRegExp("/foo/i",$sql);
        $this->assertRegExp("/drop/i",$sql);
    }

    public function testNumberOfColumns()
    {
        $tablename = 'test' . date("YmdHis");
        $this->_db->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
            ),
            array()
        );

        $this->assertEquals($this->_db->number_of_columns($tablename),1);

        $this->_db->dropTableName($tablename);
    }

    public function testGetColumns()
    {
        $vardefs = $this->_db->get_columns('contacts');

        $this->assertTrue(isset($vardefs['id']));
        $this->assertTrue(isset($vardefs['id']['name']));
        $this->assertTrue(isset($vardefs['id']['type']));
    }

    /**
     * @ticket 22921
     */
    public function testEmptyPrecision()
    {
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2'))) {
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        }

        $sql = $this->_db->alterColumnSQL(
            'contacts',
            array('compensation_min' =>
                array(
                    'required' => false,
                    'name' => 'compensation_min',
                    'vname' => 'LBL_COMPENSATION_MIN',
                    'type' => 'float',
                    'massupdate' => 0,
                    'comments' => '',
                    'help' => '',
                    'importable' => 'true',
                    'duplicate_merge' => 'disabled',
                    'duplicate_merge_dom_value' => 0,
                    'audited' => 0,
                    'reportable' => 1,
                    'len' => '18',
                    'precision' => '',
                ),
            )
        );

        $this->assertNotRegExp('/float\s*\(18,\s*\)/i',$sql);
        $this->assertRegExp('/float\s*\(18\)/i',$sql);
    }

    /**
     * @ticket 22921
     */
    public function testBlankSpacePrecision()
    {
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2'))) {
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        }

        $sql = $this->_db->alterColumnSQL(
            'contacts',
            array('compensation_min' =>
                array(
                    'required' => false,
                    'name' => 'compensation_min',
                    'vname' => 'LBL_COMPENSATION_MIN',
                    'type' => 'float',
                    'massupdate' => 0,
                    'comments' => '',
                    'help' => '',
                    'importable' => 'true',
                    'duplicate_merge' => 'disabled',
                    'duplicate_merge_dom_value' => 0,
                    'audited' => 0,
                    'reportable' => 1,
                    'len' => '18',
                    'precision' => ' ',
                ),
            )
        );

        $this->assertNotRegExp('/float\s*\(18,\s*\)/i',$sql);
        $this->assertRegExp('/float\s*\(18\)/i',$sql);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecision()
    {
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2'))) {
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        }

        $sql = $this->_db->alterColumnSQL(
            'contacts',
            array('compensation_min' =>
                array(
                    'required' => false,
                    'name' => 'compensation_min',
                    'vname' => 'LBL_COMPENSATION_MIN',
                    'type' => 'float',
                    'massupdate' => 0,
                    'comments' => '',
                    'help' => '',
                    'importable' => 'true',
                    'duplicate_merge' => 'disabled',
                    'duplicate_merge_dom_value' => 0,
                    'audited' => 0,
                    'reportable' => 1,
                    'len' => '18',
                    'precision' => '2',
                ),
            )
        );

        if ( $this->_db->dbType == 'mssql' )
            $this->assertRegExp('/float\s*\(18\)/i',$sql);
        else
            $this->assertRegExp('/float\s*\(18,2\)/i',$sql);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionInLen()
    {
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2'))) {
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        }

        $sql = $this->_db->alterColumnSQL(
            'contacts',
            array('compensation_min' =>
                array(
                    'required' => false,
                    'name' => 'compensation_min',
                    'vname' => 'LBL_COMPENSATION_MIN',
                    'type' => 'float',
                    'massupdate' => 0,
                    'comments' => '',
                    'help' => '',
                    'importable' => 'true',
                    'duplicate_merge' => 'disabled',
                    'duplicate_merge_dom_value' => 0,
                    'audited' => 0,
                    'reportable' => 1,
                    'len' => '18,2',
                ),
            )
        );
        if ( $this->_db->dbType == 'mssql' )
            $this->assertRegExp('/float\s*\(18\)/i',$sql);
        else
            $this->assertRegExp('/float\s*\(18,2\)/i',$sql);
    }

    /**
     * @ticket 22921
     */
    public function testEmptyPrecisionMassageFieldDef()
    {
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2'))) {
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        }
        $fielddef = array(
            'required' => false,
            'name' => 'compensation_min',
            'vname' => 'LBL_COMPENSATION_MIN',
            'type' => 'float',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 1,
            'len' => '18',
            'precision' => '',
        );
        $this->_db->massageFieldDef($fielddef,'mytable');

        $this->assertEquals("18",$fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testBlankSpacePrecisionMassageFieldDef()
    {
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2'))) {
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        }

        $fielddef = array(
            'required' => false,
            'name' => 'compensation_min',
            'vname' => 'LBL_COMPENSATION_MIN',
            'type' => 'float',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 1,
            'len' => '18',
            'precision' => ' ',
        );
        $this->_db->massageFieldDef($fielddef,'mytable');

        $this->assertEquals("18",$fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionMassageFieldDef()
    {
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2'))) {
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        }
        $fielddef = array(
            'required' => false,
            'name' => 'compensation_min',
            'vname' => 'LBL_COMPENSATION_MIN',
            'type' => 'float',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 1,
            'len' => '18',
            'precision' => '2',
        );
        $this->_db->massageFieldDef($fielddef,'mytable');

        $this->assertEquals("18,2",$fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionInLenMassageFieldDef()
    {
        if (in_array($this->_db->dbType, array('oci8', 'ibm_db2'))) {
            $this->markTestSkipped("Skipping on {$this->_db->dbType}, as it doesn't apply to this backend");
        }
        $fielddef = array(
            'required' => false,
            'name' => 'compensation_min',
            'vname' => 'LBL_COMPENSATION_MIN',
            'type' => 'float',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 1,
            'len' => '18,2',
        );
        $this->_db->massageFieldDef($fielddef,'mytable');

        $this->assertEquals("18,2",$fielddef['len']);
    }

    public function testGetSelectFieldsFromQuery()
    {
        $i=0;
        foreach(array("", "DISTINCT ") as $distinct) {
            $fields = array();
            $expected = array();
            foreach(array("field", "''", "'data'", "sometable.field") as $data) {
                if($data[0] != "'") {
                    $data .= $i++;
                    $fields[] = "{$distinct}$data";
                    $dotfields = explode('.', $data);
                    $expected[] = $dotfields[count($dotfields)-1];
                }
                $as = "otherfield".($i++);
                $fields[] = "{$distinct}$data $as";
                $expected[] = $as;
                $as = "otherfield".($i++);
                $fields[] = "{$distinct}$data as $as";
                $expected[] = $as;
            }
            $query = "SELECT ".join(', ', $fields);
            $result = $this->_db->getSelectFieldsFromQuery($query);
            foreach($expected as $expect) {
                $this->assertContains($expect, array_keys($result), "Result should include $expect");
            }
        }
    }

    public function providerBlobToClob()
    {
        return array(
            array('testAlterTableBlobToClob'),
            array('縢嫆璻侁刵 榎 偢偣唲鷩'),
            array(serialize(range(1, 262144))),
        );
    }

    /**
     * specific test cases based around db type are to be done by functional tests elsewhere.
     * @dataProvider orderByEnumProvider
     * @param $order_by order by column
     * @param $values value array
     * @param $order_dir order by direction
     * @param $result expected result
     */
    public function testOrderByEnum($order_by, $values, $order_dir, $expected)
    {
        $result = $this->_db->orderByEnum($order_by, $values, $order_dir);
        $this->assertEquals($expected, $result);
    }

    public function orderByEnumProvider()
    {
        return array(
            array('', array(), '', '')
        );
    }

    public function testLimitSubQuery()
    {
        if (!$this->_db->supports('limit_subquery')) {
            $this->markTestSkipped('Backend does not support LIMIT clauses in subqueries');
        }

        $subQuery = $this->_db->limitQuery('SELECT id FROM users WHERE 1=1 ORDER BY id', 0, 1, false, '', false);
        $query = 'SELECT id FROM users WHERE id IN (' . $subQuery . ')';
        $row = $this->_db->fetchOne($query);
        $this->assertInternalType('array', $row);
    }

    public function testLimitSubQueryWithUnionAndComment()
    {
        $dummy = 'SELECT \'x\' id ' . $this->_db->getFromDummyTable();

        $query = <<<SQL
SELECT
  accounts.id
FROM accounts
JOIN (
  /* this comments makes a fool of SQL Server's query parser,
     it thinks that UNION is in the top level query */
  $dummy
  UNION
  $dummy
) x ON x.id = accounts.id
WHERE 1 = 1
ORDER BY accounts.id
SQL;

        // the LIMIT needs to be greater than 1
        $result = $this->_db->limitQuery($query, 0, 2);
        $this->assertNotEmpty($result);
    }

    public function testLimitUnionQueryWithoutOrderBy()
    {
        $dummy = 'SELECT 1 id ' . $this->_db->getFromDummyTable();

        $query = $dummy . ' UNION ALL ' . $dummy;

        $result = $this->_db->limitQuery($query, 0, 1);

        $this->assertArraySubset(array(
            'id' => 1,
        ), $this->_db->fetchRow($result));
    }
}
