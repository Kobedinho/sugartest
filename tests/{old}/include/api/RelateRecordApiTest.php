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


class RelateRecordApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
        unset($_SESSION['ACL']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestProspectListsUtilities::removeAllCreatedProspectLists();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testCreateRelatedRecord()
    {
        $relateApiArgs = array('param1' => 'value1');
        $moduleApiArgs = array('param2' => 'value2');
        $service = SugarTestRestUtilities::getRestServiceMock();

        $link = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
        $link->expects($this->any())
            ->method('getRelatedModuleName')
            ->willReturn('TestModule');

        $primaryBean = new SugarBean();
        $primaryBean->testLink = $link;

        $relatedBean = new SugarBean();
        $relatedBean->field_defs = array();
        $moduleApi = $this->getMockBuilder('ModuleApi')
            ->setMethods(array('createBean'))
            ->getMock();
        $moduleApi->expects($this->once())
            ->method('createBean')
            ->with($service, $moduleApiArgs)
            ->willReturn($relatedBean);

        /** @var RelateRecordApi|PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('RelateRecordApi')
            ->setMethods(array(
                'loadBean',
                'checkRelatedSecurity',
                'getModuleApi',
                'getModuleApiArgs',
                'formatNearAndFarRecords',
                'getRelatedRecord',
            ))
            ->getMock();
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($primaryBean);
        $api->expects($this->any())
            ->method('checkRelatedSecurity')
            ->willReturn(array('testLink'));
        $api->expects($this->once())
            ->method('getModuleApi')
            ->with($service, 'TestModule')
            ->willReturn($moduleApi);
        $api->expects($this->once())
            ->method('getModuleApiArgs')
            ->with($relateApiArgs, 'TestModule')
            ->willReturn($moduleApiArgs);
        $api->expects($this->any())
            ->method('getRelatedRecord')
            ->willReturn(array());

        $api->createRelatedRecord($service, $relateApiArgs);
    }

    public function testLoadModuleApiSuccess()
    {
        $moduleApi = $this->loadModuleApi('Users');
        $this->assertInstanceOf('UsersApi', $moduleApi);
    }

    public function testLoadModuleApiFailure()
    {
        $moduleApi = $this->loadModuleApi('UnknownModule');
        $this->assertNull($moduleApi);
    }

    private function loadModuleApi($module)
    {
        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        return SugarTestReflection::callProtectedMethod($api, 'loadModuleApi', array($service, $module));
    }

    /**
     * @dataProvider getModuleApiProvider
     */
    public function testGetModuleApi($loaded, $expected)
    {
        $service = SugarTestRestUtilities::getRestServiceMock();

        $api = $this->createPartialMock('RelateRecordApi', array('loadModuleApi'));
        $api->expects($this->once())
            ->method('loadModuleApi')
            ->with($service, 'TheModule')
            ->willReturn($loaded);

        $actual = SugarTestReflection::callProtectedMethod($api, 'getModuleApi', array($service, 'TheModule'));
        $this->assertInstanceOf($expected, $actual);
    }

    public static function getModuleApiProvider()
    {
        return array(
            'module-specific' => array(new UsersApi(), 'UsersApi'),
            'non-module' => array(new stdClass(), 'ModuleApi'),
            'default' => array(null, 'ModuleApi'),
        );
    }

    /**
     * @dataProvider getModuleApiArgsProvider
     */
    public function testGetModuleApiArgs(array $args, $module, array $expected)
    {
        $api = new RelateRecordApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'getModuleApiArgs', array($args, $module));
        $this->assertEquals($expected, $actual);
    }

    public static function getModuleApiArgsProvider()
    {
        return array(
            array(
                array(
                    'module' => 'PrimaryModule',
                    'record' => 'PrimaryRecord',
                    'link_name' => 'LinkName',
                    'property' => 'value',
                ),
                'RelateModule',
                array(
                    'relate_module' => 'PrimaryModule',
                    'relate_record' => 'PrimaryRecord',
                    'module' => 'RelateModule',
                    'property' => 'value',
                ),
            ),
        );
    }

    public function testCreateRelatedNote() {
        $contact = BeanFactory::newBean("Contacts");
        $contact->last_name = "Related Record Unit Test Contact";
        $contact->save();
        // Get the real data that is in the system, not the partial data we have saved
        $contact->retrieve($contact->id);
        $this->createdBeans[] = $contact;
        $noteName = "Related Record Unit Test Note";

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Contacts",
            "record" => $contact->id,
            "link_name" => "notes",
            "name" => $noteName,
            "assigned_user_id" => $GLOBALS['current_user']->id,
        );
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['related_record']['id']);
        $this->assertEquals($noteName, $result['related_record']['name']);

        $note = BeanFactory::getBean("Notes", $result['related_record']['id']);
        // Get the real data that is in the system, not the partial data we have saved
        $note->retrieve($note->id);
        $this->createdBeans[] = $note;

        $contact->load_relationship("notes");
        $relatedNoteIds = $contact->notes->get();
        $this->assertNotEmpty($relatedNoteIds);
        $this->assertEquals($note->id, $relatedNoteIds[0]);
    }

    public function testViewNoneCreate() {
        $this->markTestIncomplete('This is getting following and _module on the array. FRM team will fix it');
        // setup ACL
        unset($_SESSION['ACL']);
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Contacts']['module']['admin']['aclaccess'] = 99;
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Notes']['module']['access']['aclaccess'] = 90;
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Notes']['module']['edit']['aclaccess'] = 90;
        // create a record
        $contact = BeanFactory::newBean("Contacts");
        $contact->last_name = "Related Record Unit Test Contact";
        $contact->save();
        // Get the real data that is in the system, not the partial data we have saved
        $contact->retrieve($contact->id);
        $this->createdBeans[] = $contact;
        $noteName = "Related Record Unit Test Note";

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Contacts",
            "record" => $contact->id,
            "link_name" => "notes",
            "name" => $noteName,
            "assigned_user_id" => $GLOBALS['current_user']->id,
        );
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);
        $this->assertEquals(count($result['related_record']), 1, "More than one field was returned");
        $this->assertNotEmpty($result['related_record']['id'], "ID was empty");
        unset($_SESSION['ACL']);
        $this->createdBeans[] = BeanFactory::getBean("Notes", $result['related_record']['id']);
    }

    /**
     * @group createRelatedLinksFromRecordList
     */
    public function testCreateRelatedLinksFromRecordList_AllRelationshipsAddedSuccessfully()
    {
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $records = array ($account1->id, $account2->id);
        $recordListId = RecordListFactory::saveRecordList($records, 'Reports');

        $mockAPI = self::createPartialMock("RelateRecordApi", array("loadBean", "requireArgs"));
        $mockAPI->expects(self::once())
            ->method("loadBean")
            ->will(self::returnValue($prospectList));

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module"    => "ProspectLists",
            "record"    => $prospectList->id,
            "link_name" => "accounts",
            "remote_id" => $recordListId,
        );

        $result = $mockAPI->createRelatedLinksFromRecordList($api,$args);
        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['record']['id']);
        $this->assertEquals(2, count($result['related_records']['success']));
        $this->assertEquals(0, count($result['related_records']['error']));

        RecordListFactory::deleteRecordList($recordListId);
    }

    /**
     * @group createRelatedLinksFromRecordList
     */
    public function testCreateRelatedLinksFromRecordList_RelationshipsFailedToAdd()
    {
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $records = array ($account1->id, $account2->id);
        $recordListId = RecordListFactory::saveRecordList($records, 'Reports');


        $relationshipStub = $this->getMockRelationship();
        $relationshipStub->expects($this->once())
            ->method('add')
            ->will($this->returnValue(array($account1->id)));

        $stub = $this->getMockBuilder(BeanFactory::getObjectName('ProspectLists'))->getMock();
        $stub->accounts = $relationshipStub;

        $mockAPI = self::createPartialMock("RelateRecordApi", array("loadBean", "requireArgs", "checkRelatedSecurity"));
        $mockAPI->expects(self::once())
            ->method("loadBean")
            ->will(self::returnValue($stub));
        $mockAPI->expects(self::once())
            ->method("requireArgs")
            ->will(self::returnValue(true));
        $mockAPI->expects(self::once())
            ->method("checkRelatedSecurity")
            ->will(self::returnValue(array('accounts')));

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module"    => "ProspectLists",
            "record"    => $prospectList->id,
            "link_name" => "accounts",
            "remote_id" => $recordListId,
        );

        $result = $mockAPI->createRelatedLinksFromRecordList($api,$args);

        $this->assertNotEmpty($result['record']);
        $this->assertEquals(1, count($result['related_records']['success']));
        $this->assertEquals(1, count($result['related_records']['error']));

        RecordListFactory::deleteRecordList($recordListId);
    }

    /**
     * Helper to get a mock relationship
     * @return mixed
     */
    protected function getMockRelationship()
    {
        return $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetRelatedFieldsReturnsOnlyFieldsForPassedInLink()
    {
        $opp = $this->getMockBuilder('Opportunity')->setMethods(array('save'))->getMock();
        $contact = $this->getMockBuilder('Contact')->setMethods(array('save'))->getMock();

        $rr_api = new RelateRecordApi();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $fields = SugarTestReflection::callProtectedMethod(
            $rr_api,
            'getRelatedFields',
            array(
                $api,
                // all of the below fields contain a rname_link.
                array(
                    'accept_status_calls' => '',
                    'accept_status_meetings' => '',
                    'opportunity_role' => 'Unit Test'
                ),
                $opp,
                'contacts',
                $contact
            )
        );

        // this should only contain one field as opportunity_role is the only valid one for the contacts link
        $this->assertCount(1, $fields);
    }

    public function testDeleteRelatedLink()
    {
        $call = SugarTestCallUtilities::createCall();
        $contact = SugarTestContactUtilities::createContact();

        $this->assertTrue($call->load_relationship('contacts'), 'Relationship is not loaded');
        $call->contacts->add($contact);

        $call = BeanFactory::retrieveBean('Calls', $call->id, array('use_cache' => false));
        $this->assertEquals($contact->id, $call->contact_id, 'Contact is not linked to call');

        // unregister bean in order to make sure API won't take it from cache
        // where the call is stored w/o linked contact
        BeanFactory::unregisterBean('Calls', $call->id);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->deleteRelatedLink($service, array(
            'module' => 'Calls',
            'record' => $call->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
        ));

        $this->assertArrayHasKey('record', $response);
        $this->assertEquals($call->id, $response['record']['id'], 'Call is not returned by API');
        $this->assertEmpty($response['record']['contact_id'], 'Contact is not unlinked from call');
    }

    /**
     * Before Save hook should be called only once.
     * @ticket PAT-769
     */
    public function testBeforeSaveOnCreateRelatedRecord()
    {
        LogicHook::refreshHooks();
        $hook = array(
            'Notes',
            'before_save',
            Array(1, 'Notes::before_save', __FILE__, 'SugarBeanBeforeSaveTestHook', 'beforeSave')
        );
        call_user_func_array('check_logic_hook_file', $hook);

        $contact = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'notes',
            'name' => 'Test Note',
            'assigned_user_id' => $api->user->id,
        );
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        call_user_func_array('remove_logic_hook', $hook);
        $this->createdBeans[] = BeanFactory::getBean('Notes', $result['related_record']['id']);
        $expectedCount = SugarBeanBeforeSaveTestHook::$callCounter;
        SugarBeanBeforeSaveTestHook::$callCounter = 0;

        $this->assertEquals(1, $expectedCount);
    }

    /**
     * opportunity_role should be saved when creating related contact
     * @ticket PAT-1281
     */
    public function testCreateRelatedRecordRelateFields()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "opportunity_role" => "Technical Decision Maker",
            "module" => "Opportunities",
            "record" => $opportunity->id,
            "link_name" => "contacts",
            'assigned_user_id' => $api->user->id,
        );

        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        $this->assertEquals($result['related_record']['opportunity_role'], "Technical Decision Maker");
    }

    /**
     * @dataProvider normalizeLinkIdsSuccessProvider
     */
    public function testNormalizeLinkIdsSuccess($ids, array $expected)
    {
        $actual = $this->normalizeLinkIds($ids);
        $this->assertEquals($expected, $actual);
    }

    public static function normalizeLinkIdsSuccessProvider()
    {
        return array(
            array(
                array('id1', array('id' => 'id2', 'key' => 'value')),
                array(
                    'id1' => array(),
                    'id2' => array('key' => 'value'),
                ),
            )
        );
    }

    /**
     * @dataProvider normalizeLinkIdsFailureProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testNormalizeLinkIdsFailure($ids)
    {
        $this->normalizeLinkIds($ids);
    }

    public static function normalizeLinkIdsFailureProvider()
    {
        return array(
            'non-array' => array('id'),
            'no-id' => array(
                array(
                    array('key' => 'value'),
                ),
            ),
        );
    }

    private function normalizeLinkIds($ids)
    {
        $api = new RelateRecordApi();
        return SugarTestReflection::callProtectedMethod($api, 'normalizeLinkIds', array($ids));
    }
}

class SugarBeanBeforeSaveTestHook
{
    static public $callCounter = 0;

    public function beforeSave($bean, $event, $arguments)
    {
        self::$callCounter++;
    }

    public function testCreateRecordACL()
    {
        $contact = SugarTestContactUtilities::createContact();
        $case = $this->getMockBuilder('SugarBean')
            ->setMethods(array('ACLAccess', 'save'))
            ->getMock();
        $case->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));
        $case->field_defs = array();
        $case->module_dir = 'Cases';
        $case->module_name = 'Cases';
        $case->id = 'the-id';

        /** @var RelateRecordApi|PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('RelateRecordApi')
            ->setMethods(array('loadBean'))
            ->getMock();
        $api->expects($this->any())
            ->method('loadBean')
            ->will($this->onConsecutiveCalls($contact, $case));

        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->createRelatedRecord($service, array(
            'link_name' => 'cases',
        ));

        $this->assertArrayHasKey('related_record', $response);
        $this->assertArrayHasKey('_acl', $response['related_record']);
    }
}
