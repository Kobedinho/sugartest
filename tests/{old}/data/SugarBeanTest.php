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


use SugarTestAccountUtilities as AccountHelper;
use SugarTestUserUtilities as UserHelper;

/**
 * Class SugarBeanTest
 * @coversDefaultClass SugarBean
 */
class SugarBeanTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
	}

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
        BeanFactory::setBeanClass('Accounts', null);
        SugarTestHelper::tearDown();
    }

	public static function tearDownAfterClass()
	{
	    SugarTestHelper::tearDown();
	}

    public function testGetObjectName(){
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->getObjectName(), 'my_table', "SugarBean->getObjectName() is not returning the table name when object_name is empty.");
    }

    public function testGetAuditTableName(){
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_audit_table_name(), 'my_table_audit', "SugarBean->get_audit_table_name() is not returning the correct audit table name.");
    }

    /**
     * @ticket 47261
     */
    public function testGetCustomTableName()
    {
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_custom_table_name(), 'my_table_cstm', "SugarBean->get_custom_table_name() is not returning the correct custom table name.");
    }

    public function testRetrieveStringQuoting()
    {
        $db = $this->getDbMock();
        $db->expects($this->at(0))
            ->method('quote')
            ->with('bad\'string')
            ->willReturn('quoted string');

        $bean = new BeanMockTestObjectName();
        $bean->db = $db;
        $where = $bean->get_where(array(
            'test1' => 'bad\'string',
            'evil\'key' => 'data',
            'tricky-(select * from config)' => 'test',
        ));

        $this->assertNotContains('bad\'string', $where);
        $this->assertContains('quoted string', $where);
        $this->assertNotContains('evil\'key', $where);
        $this->assertNotContains('select * from config', $where);
    }

    /**
     * This test makes sure that the object we are looking for is returned from the build_related_list method as
     * something changed someplace that is causing it to return the template that was passed in.
     */
    public function testBuildRelatedListReturnsRecordBeanVsEmptyBean()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $bean = new SugarBean();

        $query = "select id FROM " . $account->table_name . " where id = '" . $account->id . "'";
        $relatedList = $bean->build_related_list($query, BeanFactory::newBean('Accounts'));
        $return = array_shift($relatedList);

        $this->assertEquals($account->id, $return->id);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }




    /**
     * Test to make sure that when a bean is cloned it removes all loaded relationships so they can be recreated on
     * the cloned copy if they are called.
     *
     * @group 51630
     * @return void
     */
    public function testCloneBeanDoesntKeepRelationship()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $account->load_relationship('contacts');

        // lets make sure the relationship is loaded
        $this->assertTrue(isset($account->contacts));

        $clone_account = clone $account;

        // lets make sure that the relationship is not on the cloned record
        $this->assertFalse(isset($clone_account->contacts));

        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Test whether a relate field is determined correctly
     *
     * @param array $field_defs
     * @param string $field_name
     * @param bool $is_relate
     * @dataProvider isRelateFieldProvider
     * @covers SugarBean::is_relate_field
     */
    public function testIsRelateField(array $field_defs, $field_name, $is_relate)
    {
        $bean = new BeanIsRelateFieldMock();
        $bean->field_defs = $field_defs;
        $actual = $bean->is_relate_field($field_name);

        if ($is_relate)
        {
            $this->assertTrue($actual);
        }
        else
        {
            $this->assertFalse($actual);
        }
    }

    public static function isRelateFieldProvider()
    {
        return array(
            // test for on a non-existing field
            array(
                array(), 'dummy', false,
            ),
            // test for non-specified field type
            array(
                array(
                    'my_field' => array(),
                ), 'my_field', false,
            ),
            // test on a non-relate field type
            array(
                array(
                    'my_field' => array(
                        'type' => 'varchar',
                    ),
                ), 'my_field', false,
            ),
            // test on a relate field type but link not specified
            array(
                array(
                    'my_field' => array(
                        'type' => 'relate',
                    ),
                ), 'my_field', false,
            ),
            // test when only link is specified
            array(
                array(
                    'my_field' => array(
                        'link' => 'my_link',
                    ),
                ), 'my_field', false,
            ),
            // test on a relate field type
            array(
                array(
                    'my_field' => array(
                        'type' => 'relate',
                        'link' => 'my_link',
                    ),
                ), 'my_field', true,
            ),
        );
    }

    /**
     * test that currency/decimal from db is a string value
     * @dataProvider provideCurrencyFieldStringValues
     * @group sugarbean
     * @group currency
     */
    public function testCurrencyFieldStringValue($type, $actual, $expected)
    {
        $mock = new SugarBean();
        $mock->id = 'SugarBeanMockStringTest';
        $mock->field_defs = array(
            'testDecimal' => array(
                'type' => $type
            ),
        );

        $mock->testDecimal = $actual;
        $mock->fixUpFormatting();
        $this->assertSame($expected, $mock->testDecimal);
    }

    public function provideCurrencyFieldStringValues()
    {
        return array(
            array('decimal', '500.01', '500.01'),
            array('decimal', 500.01, '500.01'),
            array('decimal', '-500.01', '-500.01'),
            array('currency', '500.01', '500.01'),
            array('currency', 500.01, '500.01'),
            array('currency', '-500.01', '-500.01'),
        );
    }

    /**
     * SP-618
     * Verify that calling getCleanCopy on uncommon beans (like SessionManager) and common beans returns a new instance of the bean and not a null
     * @group sugarbean
     */
    public function testGetCopyNotNull()
    {
        $mock = new SessionManager();
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of SessionManager SugarBean should not be null");
        $this->assertEquals($mock->module_name, $newInstance->module_name);

        $mock = new SugarBean();
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of SugarBean should not be null");

        $mock = BeanFactory::newBean('Accounts');
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of Accounts SugarBean should not be null");
        $this->assertEquals('Accounts', $newInstance->module_name);
    }

    /**
     * @group sugarbean
     */
    public function testGetNotificationRecipientsReturnsEmptyArray()
    {
        $mock = new SugarBean();
        unset($mock->assigned_user_id);

        $ret = $mock->get_notification_recipients();

        $this->assertEmpty($ret);
    }

    public function testGetNotificationRecipientsReturnsNonEmptyArray()
    {
        $mock = new SugarBean();
        $mock->assigned_user_id = '1';

        $ret = $mock->get_notification_recipients();

        $this->assertEquals('1',$ret[0]->id);
    }
    /**
     * Check that the decryption is not called until the actual value is used
     * @return void
     */
    public function testDecryptCallsNumber()
    {
        $oSugarBean = new BeanMockTestObjectName();

        $oSugarBean->field_defs = array(
            'test_field' => array(
                'name' => 'test_field',
                'type' => 'encrypt',
            ),
        );

        // must be a valid base64-encoded string
        $original_encrypted_value = $encrypted_value = base64_encode('smth');
        $oSugarBean->test_field = ''; //initialization to avoid "Indirect modification of overloaded property..." error
        $oSugarBean->test_field =& $encrypted_value; //use link to avoid calling __get method in assertEquals
        $oSugarBean->field_defs['test_field']['type'] = 'encrypt';
        $oSugarBean->check_date_relationships_load(); //$oSugarBean->test_field shouldn't be changed
        $this->assertEquals($original_encrypted_value, $encrypted_value);
        $decrypted_value = $oSugarBean->test_field; //$oSugarBean->test_field should be changed
        $this->assertNotEquals($encrypted_value, $decrypted_value);
    }

    /**
     * Check if SugarBean::checkUserAccess returns true for a valid case.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccess()
    {
        $user = UserHelper::createAnonymousUser(true, 1);
        $account = AccountHelper::createAccount();

        $this->assertTrue($account->checkUserAccess($user));
    }

    /**
     * @param array $parent_data   Parent bean data
     * @param array $child_data    Child bean data
     * @param array $fn_field_defs Function field definition
     * @param mixed $expected      Expected value
     *
     * @dataProvider functionFieldProvider
     */
    public function testProcessFunctionFields(array $parent_data, array $child_data, array $fn_field_defs, $expected)
    {
        $parent = new BeanFunctionFieldsMock();
        $child = new SugarBean();
        $child->field_defs['fn_field'] = $fn_field_defs;
        $child->fn_field = null;

        foreach ($parent_data as $key => $value) {
            $parent->$key = $value;
        }

        foreach ($child_data as $key => $value) {
            $child->$key = $value;
        }

        $child->field_defs = $fn_field_defs;

        $parent->processFunctionFields($child, array('fn_field' => $fn_field_defs));

        $this->assertEquals($expected, $child->fn_field);
    }

    public static function functionFieldProvider()
    {
        $parent_data = array('foo' => 'bar');
        $child_data = array('baz' => 'quux');

        return array(
            // source is parent bean, function is global function
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('foo'),
                    'function_name' => 'strlen',
                ),
                3,
            ),
            // source is child bean, function is static function of a class
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('baz'),
                    'function_params_source' => 'this',
                    'function_class' => 'BeanFunctionFieldsMock',
                    'function_name' => 'toUpper',
                ),
                'QUUX',
            ),
            // function declaration is in external file
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('foo'),
                    'function_name' => 'SugarBeanTest_external_function',
                    'function_require' => dirname(__FILE__) . '/SugarBeanTest/external_function.php',
                ),
                'bar',
            ),
            // argument is $this
            array(
                $parent_data,
                array(),
                array(
                    'function_params' => array('$this'),
                    'function_name' => 'get_class',
                ),
                'BeanFunctionFieldsMock',
            ),
            // param source is wrong
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('foo'),
                    'function_params_source' => 'unknown',
                    'function_name' => 'strlen',
                ),
                null,
            ),
            // function doesn't exist
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('foo'),
                    'function_name' => 'SugarBeanTest_unknown',
                ),
                null,
            ),
            // source field is not set
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('bar'),
                    'function_name' => 'strlen',
                ),
                null,
            ),
        );
    }

    /**
     * Check if SugarBean::checkUserAccess returns false without team access.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccessWithoutTeamAccess()
    {
        global $current_user;
        $owner = UserHelper::createAnonymousUser();

        $account = AccountHelper::createAccount(
            null,
            array('team_id' => $owner->id,'team_set_id' => $owner->id,)
        );
        $current_user = UserHelper::createAnonymousUser();
        $this->assertFalse($account->checkUserAccess($current_user));
    }

    /**
     * Check if SugarBean::checkUserAccess returns false without ACL access.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccessWithoutACLAccess()
    {
        $user = UserHelper::createAnonymousUser();
        $account = $this->getMockBuilder('Account')
            ->setMethods(array('ACLAccess'))
            ->getMock();
        $account->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));
        $account->id = 'foo';

        $this->assertFalse($account->checkUserAccess($user));
    }

    /**
     * This test will make sure that when you enter an operation that the one that actually entered the operation
     * actually is the one to leave it.
     */
    public function testEnterLeaveOperationMultipleTimes()
    {
        $ret1 = SugarBean::enterOperation('unit_test');
        $this->assertTrue($ret1);

        $ret2 = SugarBean::enterOperation('unit_test');
        $this->assertFalse($ret2);

        $this->assertFalse(SugarBean::leaveOperation('unit_test', $ret2));

        $this->assertTrue(SugarBean::leaveOperation('unit_test', $ret1));

        SugarBean::resetOperations();
    }

    /**
     *
     * Test logging for distinct mismatch/compensation and the
     * proper return of offending record ids.
     *
     * @covers SugarBean::logDistinctMismatch
     * @dataProvider providerTestLogDistinctMismatch
     * @group unit
     *
     * @param array $sqlRows
     * @param array $beans
     * @param string $level
     * @param array $expected
     */
    public function testLogDistinctMismatch(array $sqlRows, array $beans, $level, array $expected)
    {
        LoggerManager::getLogger()->setLevel($level);

        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $methodArgs = array($sqlRows, $beans);
        $this->assertEquals(
            $expected,
            SugarTestReflection::callProtectedMethod($bean, 'logDistinctMismatch', $methodArgs),
            "Wrong offending record ids returned"
        );
    }

    public function providerTestLogDistinctMismatch()
    {
        return array(

            // matching sqlRows vs beanSet
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a2', 'name' => 'record2'),
                    2 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                'debug',
                array(),
            ),

            // duplicate sqlRows
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a1', 'name' => 'record1'),
                    2 => array('id' => 'a2', 'name' => 'record2'),
                    3 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                'debug',
                array('a1' => 2),
            ),

            // duplicate sqlRows, no detailed logging (not enabled by default)
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a1', 'name' => 'record1'),
                    2 => array('id' => 'a2', 'name' => 'record2'),
                    3 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                'fatal',
                array(),
            ),
        );
    }

    /**
     *
     * Test fetchFromQuery with distinct compensation.
     *
     * @covers SugarBean::fetchFromQuery
     * @covers SugarBean::computeDistinctCompensation
     * @dataProvider providerTestFetchFromQueryWithDistinctCompensation
     * @group unit
     */
    public function testFetchFromQueryWithDistinctCompensation($sqlRows, $expected, $compensation)
    {
        // prepare SugarQuery
        $query = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('execute'))
            ->getMock();

        $query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($sqlRows));

        // sut
        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(array('call_custom_logic', 'logDistinctMismatch', 'getCleanCopy'))
            ->getMock();
        $bean->field_defs = array(
            'id' => array(
                'type' => 'id',
            ),
            'name' => array(
                'type' => 'name',
            ),
        );
        //Setup the beans returned by cleanCopy to be clones of the mock rather than real beans
        $bean->method('getCleanCopy')->will($this->returnCallback(
            function () use ($bean) { return clone $bean; }
        ));

        if ($compensation) {
            $bean->expects($this->once())
                ->method('logDistinctMismatch');
        }

        // execute fetch
        $options = array('compensateDistinct' => true);
        $results = $bean->fetchFromQuery($query, array(), $options);

        // tests
        $this->assertArrayHasKey(
            '_distinctCompensation',
            $results,
            'No distinct compensation returned'
        );

        $this->assertEquals(
            $compensation,
            $results['_distinctCompensation'],
            'Incorrect compensation result'
        );

        unset($results['_distinctCompensation']);

        foreach ($results as $key => $bean) {
            $this->assertEquals(
                $expected[$key],
                $bean->toArray(),
                'Incorrect bean result set'
            );
        }
    }

    public function providerTestFetchFromQueryWithDistinctCompensation()
    {
        return array(

            // matching sqlRows vs beanSet
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a2', 'name' => 'record2'),
                    2 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                0,
            ),

            // one duplicate sqlRows
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a1', 'name' => 'record1'),
                    2 => array('id' => 'a2', 'name' => 'record2'),
                    3 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                1,
            ),

            // multiple duplicate sqlRows with different records
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a1', 'name' => 'record1'),
                    2 => array('id' => 'a2', 'name' => 'record2'),
                    3 => array('id' => 'a3', 'name' => 'record3'),
                    4 => array('id' => 'a3', 'name' => 'record3'),
                    5 => array('id' => 'a3', 'name' => 'record3'),
                    6 => array('id' => 'a4', 'name' => 'record4'),
                    7 => array('id' => 'a5', 'name' => 'record5'),
                    8 => array('id' => 'a5', 'name' => 'record5'),
                    9 => array('id' => 'a1', 'name' => 'record1'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                    'a4' => array('id' => 'a4', 'name' => 'record4'),
                    'a5' => array('id' => 'a5', 'name' => 'record5'),
                ),
                5,
            ),
        );
    }

    /**
     * Tests SugarBean::create_new_list_query
     * This test is to make sure ret_array['secondary_select'] should not contain fields with relationship_fields defined
     */
    public function testCreateNewListQuery()
    {
        $bean = BeanFactory::newBean("Contacts");
        $filter = array(
            "account_id",
            "opportunity_role_fields",
            "opportunity_role_id",
            "opportunity_role"
        );
        $params = array(
            "distinct" => false,
            "joined_tables" => array(0 => "opportunities_contacts"),
            "include_custom_fields" => true,
            "collection_list" => null
        );
        $query = $bean->create_new_list_query("", "", $filter, $params, 0, "", true);

        $this->assertNotContains("opportunity_role_fields", $query["secondary_select"], "secondary_select should not contain fields with relationship_fields defined (e.g. opportunity_role_fields).");
        $this->assertContains("opportunity_role_id", $query["secondary_select"], "secondary_select should contain the fields that's defined in relationship_fields (e.g. opportunity_role_id).");

        $bean = BeanFactory::newBean("Contacts");
        $filter = array(
            "account_name",
            "account_id"
        );
        $params = array(
            "join_type" => "LEFT JOIN",
            "join_table_alias" => "accounts",
            "join_table_link_alias" => "jtl0"
        );
        $query = $bean->create_new_list_query("", "", $filter, $params, 0, "", true);

        $this->assertEquals(1, substr_count($query["secondary_select"], " account_id"), "secondary_select should not contain duplicate alias names.");

        $bean = BeanFactory::newBean('Calls');
        $query = $bean->create_new_list_query('', '', array('contact_name', 'contact_id'), array(), 0, '', true);

        $this->assertContains("contact_id", $query["secondary_select"], "secondary_select should contain rel_key field (e.g. contact_id).");
    }

    /**
     * @dataProvider dataProviderFieldDefs
     * @param array $defs
     * @covers ::getFieldDefinitions
     */
    public function testGetFieldDefinitionsWithNoFilter($defs)
    {
        $bean = new BeanMockTestObjectName();

        $bean->field_defs = $defs;

        $actual = $bean->getFieldDefinitions();
        $this->assertSameSize($defs, $actual);
        $this->assertSame($defs, $actual);
    }

    /**
     * @dataProvider dataProviderFieldDefs
     * @param array $defs
     * @covers ::getFieldDefinitions
     */
    public function testGetFieldDefinitionsWithFilter($defs)
    {
        $bean = new BeanMockTestObjectName();

        $bean->field_defs = $defs;

        $actual = $bean->getFieldDefinitions('id_name', array('opportunity_id'));
        $this->assertCount(1, $actual);
        $this->assertArrayHasKey('id_name', $actual['opportunity_name']);
    }


    public static function dataProviderFieldDefs()
    {
        return array(
            array(array(
                'opportunity_id' => array(
                    'name' => 'opportunity_id'
                ),
                'opportunity_name' => array(
                    'name' => 'opportunity_name',
                    'id_name' => 'opportunity_id'
                ),
                'name' => array(
                    'name' => 'name'
                ),
            ))
        );
    }

    /**
     * @covers ::handle_remaining_relate_fields
     */
    public function testHandleRemainingRelateFields()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('load_relationship'))
            ->getMock();

        $bean->id = 'unit_test_id';
        $bean->opportunity_id = 'new_unit_test_id';

        $bean->rel_fields_before_value = array('opportunity_id' => 'old_unit_test_id');

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('add', 'delete', 'resetLoaded'))
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->once())
            ->method('delete')
            ->with('unit_test_id', 'old_unit_test_id')
            ->willReturn(true);

        $link2->expects($this->once())
            ->method('add')
            ->with('new_unit_test_id')
            ->willReturn(true);

        $link2->expects($this->never())
            ->method('resetLoaded');

        $bean->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities')
            ->willReturn(true);

        $bean->opportunities = $link2;
        $bean->field_defs = array(
            'opportunity_id' => array(
                'name' => 'opportunity_id',
                'type' => 'id'
            ),
            'opportunity_name' => array(
                'name' => 'opportunity_name',
                'id_name' => 'opportunity_id',
                'link' => 'opportunities',
                'save' => true,
                'type' => 'relate'
            ),
            'opportunities' => array(
                'name' => 'opportunities',
                'type' => 'link',
            )
        );

        $actual = SugarTestReflection::callProtectedMethod($bean, 'handle_remaining_relate_fields');

        $this->assertContains('opportunities', $actual['add']['success']);
        $this->assertContains('opportunities', $actual['remove']['success']);
    }

    /**
     * @covers ::handle_remaining_relate_fields
     */
    public function testHandleRemainingRelateFieldsDoesNotRemove()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('load_relationship'))
            ->getMock();

        $bean->id = 'unit_test_id';
        $bean->opportunity_id = 'new_unit_test_id';

        $bean->rel_fields_before_value = array();

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('add', 'delete', 'resetLoaded'))
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->never())
            ->method('delete');

        $link2->expects($this->once())
            ->method('add')
            ->with('new_unit_test_id')
            ->willReturn(true);

        $link2->expects($this->never())
            ->method('resetLoaded');

        $bean->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities')
            ->willReturn(true);

        $bean->opportunities = $link2;
        $bean->field_defs = array(
            'opportunity_id' => array(
                'name' => 'opportunity_id',
                'type' => 'id'
            ),
            'opportunity_name' => array(
                'name' => 'opportunity_name',
                'id_name' => 'opportunity_id',
                'link' => 'opportunities',
                'save' => true,
                'type' => 'relate'
            ),
            'opportunities' => array(
                'name' => 'opportunities',
                'type' => 'link',
            )
        );

        $actual = SugarTestReflection::callProtectedMethod($bean, 'handle_remaining_relate_fields');

        $this->assertContains('opportunities', $actual['add']['success']);
        $this->assertEmpty($actual['remove']['success']);
    }

    /**
     * @covers ::handle_remaining_relate_fields
     */
    public function testHandleRemainingRelateFieldsDoesNotAdd()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('load_relationship'))
            ->getMock();

        $bean->id = 'unit_test_id';
        $bean->opportunity_id = '';

        $bean->rel_fields_before_value = array('opportunity_id' => 'old_unit_test_id');

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('add', 'delete', 'resetLoaded'))
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->never())
            ->method('add');

        $link2->expects($this->once())
            ->method('delete')
            ->with('unit_test_id', 'old_unit_test_id')
            ->willReturn(true);

        $link2->expects($this->never())
            ->method('resetLoaded');

        $bean->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities')
            ->willReturn(true);

        $bean->opportunities = $link2;
        $bean->field_defs = array(
            'opportunity_id' => array(
                'name' => 'opportunity_id',
                'type' => 'id'
            ),
            'opportunity_name' => array(
                'name' => 'opportunity_name',
                'id_name' => 'opportunity_id',
                'link' => 'opportunities',
                'save' => true,
                'type' => 'relate'
            ),
            'opportunities' => array(
                'name' => 'opportunities',
                'type' => 'link',
            )
        );

        $actual = SugarTestReflection::callProtectedMethod($bean, 'handle_remaining_relate_fields');

        $this->assertContains('opportunities', $actual['remove']['success']);
        $this->assertEmpty($actual['add']['success']);
    }

    private function getDbMock()
    {
        /** @var DBManager|PHPUnit_Framework_MockObject_MockObject $db */
        $db = $this->getMockBuilder('DBManager')
            ->setMethods(array('checkError'))
            ->getMockForAbstractClass();
        $db->expects($this->any())
            ->method('fromConvert')
            ->willReturnCallback(function ($string) {
                return $string;
            });

        return $db;
    }
}

class BeanMockTestObjectName extends SugarBean
{
    var $table_name = "my_table";
}

class BeanIsRelateFieldMock extends SugarBean
{
    public function is_relate_field($field_name_name)
    {
        return parent::is_relate_field($field_name_name);
    }
}

class BeanFunctionFieldsMock extends SugarBean
{
    public function processFunctionFields(SugarBean $bean, array $fields)
    {
        parent::processFunctionFields($bean, $fields);
    }

    public static function toUpper($arg)
    {
        return strtoupper($arg);
    }
}
