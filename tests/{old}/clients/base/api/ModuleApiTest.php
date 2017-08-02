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

require_once ("tests/{old}/SugarTestRestUtilities.php");

/**
 * @group ApiTests
 */
class ModuleApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $accounts, $account_ids;
    public $roles;
    /**
     * @var ModuleApi
     */
    public $moduleApi;
    public $serviceMock;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");
    }

    public function setUp()
    {
        // load up the unifiedSearchApi for good times ahead
        $this->moduleApi = new ModuleApi();
        $account = BeanFactory::newBean('Accounts');
        $account->name = "ModulaApiTest setUp Account";
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->billing_address_city = 'Cupertino';
        $account->billing_address_country = 'USA';
        $account->googleplus = 'info@sugarcrm.com';
        $account->save();
        $this->accounts[] = $account;
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown()
    {
        SugarACL::resetACLs();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        // delete the bunch of accounts crated
        $GLOBALS['db']->query("DELETE FROM accounts WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'");

        SugarTestAccountUtilities::deleteM2MRelationships('contacts');
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    // test set favorite
    public function testSetFavorite()
    {
        $result = $this->moduleApi->setFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id));
        $this->assertTrue((bool)$result['my_favorite'], "Was not set to true");

        $this->assertArrayHasKey('following', $result, 'API response does not contain "following" key');
        $this->assertNotEmpty($result['following'], 'Bean was not auto-followed when marked as favorite');

        return $this->accounts[0];
    }

    /**
     * @depends testSetFavorite
     */
    public function testRemoveFavorite(Account $account)
    {
        $result = $this->moduleApi->unsetFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $account->id)
        );

        $this->assertArrayHasKey('my_favorite', $result, 'API response does not contain "my_favorite" key');
        $this->assertEmpty($result['my_favorite'], 'Bean was not removed from favorites');

        $this->assertArrayHasKey('following', $result, 'API response does not contain "following" key');
        $this->assertNotEmpty($result['following'], 'Bean was auto-unfollowed when removed from favorites');
    }
    // test set favorite of deleted record
    public function testSetFavoriteDeleted()
    {
        $this->accounts[0]->mark_deleted($this->accounts[0]->id);
        $this->setExpectedException('SugarApiExceptionNotFound',
            "Could not find record: {$this->accounts[0]->id} in module: Accounts");
        $result = $this->moduleApi->setFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id));
    }
    // test remove favorite of deleted record
    public function testRemoveFavoriteDeleted()
    {
        $result = $this->moduleApi->setFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id));
        $this->assertTrue((bool)$result['my_favorite'], "Was not set to true");

        $this->accounts[0]->deleted = 1;
        $this->accounts[0]->save();
        $this->setExpectedException('SugarApiExceptionNotFound',
            "Could not find record: {$this->accounts[0]->id} in module: Accounts");

        $result = $this->moduleApi->setFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id));
    }
    // test set my_favorite on bean
    public function testSetFavoriteOnBean()
    {
        $result = $this->moduleApi->updateRecord($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id, "my_favorite" => true));
        $this->assertTrue((bool)$result['my_favorite'], "Was not set to true");
    }
    // test remove my_favorite on bean
    public function testRemoveFavoriteOnBean()
    {
        $result = $this->moduleApi->updateRecord($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id, "my_favorite" => true));
        $this->assertTrue((bool)$result['my_favorite'], "Was not set to true");

        $result = $this->moduleApi->updateRecord($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id,
                "my_favorite" => false));
        $this->assertFalse((bool)$result['my_favorite'], "Was not set to False");
    }

    public function testCreate()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $this->assertEquals("Test Account", $result['name']);

        $account = BeanFactory::newBean('Accounts');
        $account->retrieve($result['id']);
        $this->assertAttributeNotEmpty('id',$account);
        $this->assertEquals("Test Account", $account->name);
    }

    public function testprocessAfterCreateOperations_afterSaveOperationSpecified_copiesRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $GLOBALS['dictionary']['Account']['after_create'] = array(
            'copy_rel_from' => array(
                'contacts',
            )
        );

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            array(
                 'module' => 'Accounts',
                 'after_create' => array(
                     'copy_rel_from' => $accountBean->id
                 )
            ),
            $newAccountBean
        );

        unset($GLOBALS['dictionary']['Account']['after_create']);

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(1, count($newAccountBean->contacts->beans));
    }

    public function testprocessAfterCreateOperations_copyRelFromVarDefNotSpecified_doesNotCopyRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            array(
                 'module' => 'Accounts',
                 'after_create' => array(
                     'copy_rel_from' => $accountBean->id
                 )
            ),
            $newAccountBean
        );

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(0, count($newAccountBean->contacts->beans));
    }

    public function testprocessAfterCreateOperations_copyRelFromUrlParameterNotSpecified_doesNotCopyRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $GLOBALS['dictionary']['Account']['after_create'] = array(
            'copy_rel_from' => array(
                'contacts',
            )
        );

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            array(
                 'module' => 'Accounts',
            ),
            $newAccountBean
        );

        unset($GLOBALS['dictionary']['Account']['after_create']);

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(0, count($newAccountBean->contacts->beans));
    }

    public function testUpdate()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $id = $result['id'];

        $result = $this->moduleApi->updateRecord($this->serviceMock,
                array('module' => 'Accounts', 'record' => $id, 'name' => 'Changed Account'));
        $this->assertArrayHasKey("id", $result);
        $this->assertEquals($id, $result['id']);

        $account = BeanFactory::newBean('Accounts');
        $account->retrieve($result['id']);
        $this->assertAttributeNotEmpty('id',$account);
        $this->assertEquals("Changed Account", $account->name);
    }

    public function testUpdateNonConflict()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $id = $result['id'];
        $timedate = TimeDate::getInstance();
        $dm = $timedate->fromIso($result['date_modified']);

        $result = $this->moduleApi->updateRecord($this->serviceMock,
                array('module' => 'Accounts', 'record' => $id, 'name' => 'Changed Account',
                        '_headers' => array('X_TIMESTAMP' => $timedate->asIso($dm)),
                ));
        $this->assertArrayHasKey("id", $result);
        $this->assertEquals($id, $result['id']);
    }

    /**
     * @expectedException SugarApiExceptionEditConflict
     */
    public function testUpdateConflict()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account',
                'assigned_user_id' => $GLOBALS['current_user']->id,
        ));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $id = $result['id'];
        $timedate = TimeDate::getInstance();
        // change modified data to not match the record
        $dm = $timedate->fromIso($result['date_modified'])->get("-1 minute");

        try {
            $result = $this->moduleApi->updateRecord($this->serviceMock,
                    array('module' => 'Accounts', 'record' => $id, 'name' => 'Changed Account',
                            '_headers' => array('X_TIMESTAMP' => $timedate->asIso($dm)),
                    ));
        } catch(SugarApiExceptionEditConflict $e) {
            $this->assertNotEmpty($e->extraData);
            $this->arrayHasKey("record", $e->extraData);
            $this->assertEquals('Test Account', $e->extraData['record']['name']);
            throw $e;
        }
    }

    public function testViewNoneCreate()
    {
        // setup ACL
        $rejectacl = $this->createPartialMock('SugarACLStatic', array('checkAccess'));
        $rejectacl->expects($this->any())->method('checkAccess')->will($this->returnCallback(function($module, $view, $context) {
                if($module == 'Accounts' && $view == 'view') {
                    return false;
                }
                return true;
            }
        ));
        SugarACL::setACL('Accounts', array($rejectacl));
        // create a record
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id));
        // verify only id returns
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayNotHasKey("name", $result);

        // cleanup ACL mock
        SugarACL::resetACLs('Accounts');
    }

    /**
     * Integration test returned fields based on fields list and/or view parameter
     * @dataProvider providerTestModuleReturnedField
     */
    public function testModuleReturnedFields($args, $expected, $suppressed)
    {
        $args['module'] = 'Accounts';
        $args['record'] = $this->accounts[0]->id;

        $reply = $this->moduleApi->retrieveRecord($this->serviceMock, $args);

        foreach ($expected as $field) {
            $this->assertArrayHasKey(
                $field,
                $reply,
                "Expected field $field not present"
            );
        }

        foreach ($suppressed as $field) {
            $this->assertArrayNotHasKey(
                $field,
                $reply,
                "Field $field should not be present"
            );
        }
    }

    public function providerTestModuleReturnedField()
    {
        return array(

            // field list only
            array(
                array(
                    'fields' => 'name,billing_address_country',
                ),
                array(
                    'name',
                    'billing_address_country',
                ),
                array(
                    'googleplus',
                ),
            ),

            // view only
            array(
                array(
                    'view' => 'record',
                ),
                array(
                    'name',
                    'billing_address_country',
                ),
                array(
                    'googleplus',
                ),
            ),

            // field list and view combined
            array(
                array(
                    'fields' => 'name,billing_address_country',
                    'view' => 'record',
                ),
                array(
                    'name',
                    'billing_address_country',
                    'billing_address_city',
                ),
                array(
                    'googleplus',
                ),
            ),

            // nothing specified - expecting all fields
            array(
                array(),
                array(
                    'name',
                    'billing_address_country',
                    'billing_address_city',
                    'googleplus',
                ),
                array(),
            ),
        );
    }

    public function testGetLoadedAndFormattedBean()
    {
        $account = $this->getMockBuilder('Account')
            ->setMethods(array('ACLAccess'))
            ->getMock();
        $account->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));
        $account->id = $this->accounts[0]->id;
        $account->date_modified = $this->accounts[0]->date_modified;

        $api = $this->getMockBuilder('ModuleApi')
            ->setMethods(array('loadBean'))
            ->getMock();
        $api->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($account));

        $data = SugarTestReflection::callProtectedMethod(
            $api,
            'getLoadedAndFormattedBean',
            array($this->serviceMock, array(
                'module' => 'Accounts',
                'record' => $this->accounts[0]->id,
            ))
        );

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', $data, 'API response does not contain ID');
        $this->assertEquals($data['id'], $account->id, 'API has returned wrong ID');
        $this->assertArrayHasKey('date_modified', $data, 'API response does not contain last modification date');
        $this->assertArrayHasKey('_acl', $data, 'API response does not contain ACL data');
        $this->assertArrayNotHasKey('name', $data, 'API response contains should not contain "name" field');
    }

    /**
     * @dataProvider getRelatedRecordArgumentsSuccessProvider
     */
    public function testGetRelatedRecordArgumentsSuccess(array $fieldDefs, array $args, $action, array $expected)
    {
        $actual = $this->getRelatedRecordArguments($fieldDefs, $args, $action);
        $this->assertEquals($expected, $actual);
    }

    public static function getRelatedRecordArgumentsSuccessProvider()
    {
        $fieldDefs = array(
            'name' => array(
                'type' => 'varchar',
            ),
            'contacts' => array(
                'type' => 'link',
            ),
        );
        $action = 'some-action';

        return array(
            'some-data' => array(
                $fieldDefs,
                array(
                    'name' => array(
                        'some-action' => array('a28c2304-f9b6-97ca-f2e6'),
                        'some-other-action' => array(
                            array(
                                'first_name' => 'Max',
                                'last_name' => 'Jensen',
                            ),
                        ),
                    ),
                    'contacts' => array(
                        'some-action' => array(
                            '89179177-41d7-311f-90ef',
                            array(
                                'id' => '473d122f-0a45-fef6-0138',
                                'role' => 'owner',
                            ),
                        ),
                        'some-other-action' => array(
                            array(
                                'first_name' => 'Chris',
                                'last_name' => 'Oliver',
                            ),
                        ),
                    ),
                ),
                $action,
                array(
                    'contacts' => array(
                        '89179177-41d7-311f-90ef',
                        array(
                            'id' => '473d122f-0a45-fef6-0138',
                            'role' => 'owner',
                        ),
                    ),
                ),
            ),
            'no-data-for-action' => array(
                $fieldDefs,
                array(
                    'contacts' => array(
                        'some-other-action' => array(),
                    ),
                ),
                $action,
                array(),
            ),
        );
    }

    /**
     * @dataProvider getRelatedRecordArgumentsFailureProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testGetRelatedRecordArgumentsFailure(array $fieldDefs, array $args, $action)
    {
        $this->getRelatedRecordArguments($fieldDefs, $args, $action);
    }

    public static function getRelatedRecordArgumentsFailureProvider()
    {
        $fieldDefs = array(
            'contacts' => array(
                'type' => 'link',
            ),
        );
        $action = 'the-action';

        return array(
            'link-data-non-array' => array(
                $fieldDefs,
                array(
                    'contacts' => 1,
                ),
                $action,
            ),
            'action-data-non-array' => array(
                $fieldDefs,
                array(
                    'contacts' => array(
                        'the-action' => 2,
                    ),
                ),
                $action,
            ),
        );
    }

    private function getRelatedRecordArguments(array $fieldDefs, array $args, $action)
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldDefinitions'))
            ->getMock();
        $bean->expects($this->once())
            ->method('getFieldDefinitions')
            ->willReturn($fieldDefs);

        return SugarTestReflection::callProtectedMethod(
            $this->moduleApi,
            'getRelatedRecordArguments',
            array($bean, $args, $action)
        );
    }

    public function testLinkRelatedRecords()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $relateRecordApi */
        $api = $this->getApiWithMockedRelateRecordApi('createRelatedLinks', $relateRecordApi);
        $bean = $this->getPrimaryBean('primary-module', 'primary-id');

        $relateRecordApi->expects($this->at(0))
            ->method('createRelatedLinks')
            ->with($this->serviceMock, array(
                'module' => 'primary-module',
                'record' => 'primary-id',
                'link_name' => 'link1',
                'ids' => array(
                    'id11',
                    array(
                        'id' => 'id12',
                        'field12' => 'value12',
                    )
                ),
            ));

        $relateRecordApi->expects($this->at(1))
            ->method('createRelatedLinks')
            ->with($this->serviceMock, array(
                'module' => 'primary-module',
                'record' => 'primary-id',
                'link_name' => 'link2',
                'ids' => array('id21'),
            ));

        SugarTestReflection::callProtectedMethod(
            $api,
            'linkRelatedRecords',
            array($this->serviceMock, $bean, array(
                'link1' => array(
                    'id11',
                    array(
                        'id' => 'id12',
                        'field12' => 'value12',
                    )
                ),
                'link2' => array('id21'),
            ))
        );
    }

    public function testUnlinkRelatedRecords()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $relateRecordApi */
        $api = $this->getApiWithMockedRelateRecordApi('deleteRelatedLink', $relateRecordApi);
        $bean = $this->getPrimaryBean('primary-module', 'primary-id');

        $relateRecordApi->expects($this->at(0))
            ->method('deleteRelatedLink')
            ->with($this->serviceMock, array(
                'module' => 'primary-module',
                'record' => 'primary-id',
                'link_name' => 'link1',
                'remote_id' => 'id1',
            ));

        $relateRecordApi->expects($this->at(1))
            ->method('deleteRelatedLink')
            ->with($this->serviceMock, array(
                'module' => 'primary-module',
                'record' => 'primary-id',
                'link_name' => 'link2',
                'remote_id' => 'id2',
            ));

        SugarTestReflection::callProtectedMethod(
            $api,
            'unlinkRelatedRecords',
            array($this->serviceMock, $bean, array(
                'link1' => array('id1'),
                'link2' => array('id2'),
            ))
        );
    }

    public function testCreateRelatedRecords()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $relateRecordApi */
        $api = $this->getApiWithMockedRelateRecordApi('createRelatedRecord', $relateRecordApi);
        $bean = $this->getPrimaryBean('primary-module', 'primary-id');

        $relateRecordApi->expects($this->at(0))
            ->method('createRelatedRecord')
            ->with($this->serviceMock, array(
                'module' => 'primary-module',
                'record' => 'primary-id',
                'link_name' => 'link1',
                'name' => 'Underwater Mining Inc.',
            ));

        $relateRecordApi->expects($this->at(1))
            ->method('createRelatedRecord')
            ->with($this->serviceMock, array(
                'module' => 'primary-module',
                'record' => 'primary-id',
                'link_name' => 'link2',
                'first_name' => 'Latanya',
                'last_name' => 'Ollie',
            ));

        SugarTestReflection::callProtectedMethod(
            $api,
            'createRelatedRecords',
            array($this->serviceMock, $bean, array(
                'link1' => array(
                    array('name' => 'Underwater Mining Inc.'),
                ),
                'link2' => array(
                    array(
                        'first_name' => 'Latanya',
                        'last_name' => 'Ollie',
                    ),
                ),
            ))
        );
    }

    /**
     * @param string $method
     * @param PHPUnit_Framework_MockObject_MockObject $relateRecordApi
     * @return ModuleApi|PHPUnit_Framework_MockObject_MockObject
     */
    private function getApiWithMockedRelateRecordApi($method, &$relateRecordApi)
    {
        $relateRecordApi = $this->getMockBuilder('RelateRecordApi')
            ->setMethods(array($method))
            ->getMock();

        $moduleApi = $this->getMockBuilder('ModuleApi')
            ->setMethods(array('getRelateRecordApi'))
            ->getMock();
        $moduleApi->expects($this->any())
            ->method('getRelateRecordApi')
            ->willReturn($relateRecordApi);

        return $moduleApi;
    }

    /**
     * @param string $module
     * @param string $id
     *
     * @return SugarBean
     */
    private function getPrimaryBean($module, $id)
    {
        $bean = new SugarBean();
        $bean->module_name = $module;
        $bean->id = $id;

        return $bean;
    }
}

class ModuleApiTestMock extends ModuleApi
{
    public function processAfterCreateOperationsMock($args, SugarBean $bean)
    {
        $this->processAfterCreateOperations($args, $bean);
    }
}
