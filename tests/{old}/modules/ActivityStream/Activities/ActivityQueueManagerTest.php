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
 * @group ActivityStream
 */

/**
 * @group activities
 * @group ActivityStream
 * @group activities_queue
 */
class ActivityQueueManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    const BOGUS_USER  = '0';
    const USER_ONE    = '1';
    const USER_TWO    = '2';
    const PORTAL_USER = '3';

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testChangeFields_TeamIDsChangedToNames_ChangesOccurredNormally()
    {
        $contact    = SugarTestContactUtilities::createContact();
        $teamBefore = SugarTestTeamUtilities::createAnonymousTeam();
        $teamAfter  = SugarTestTeamUtilities::createAnonymousTeam();

        $activityData = array(
            'object'  => array(
                'name'   => $contact->full_name,
                'type'   => 'Contact',
                'module' => 'Contacts',
                'id'     => $contact->id,
            ),
            'changes' => array(
                'team_id' => array(
                    'field_name' => 'team_id',
                    'data_type'  => 'id',
                    'before'     => $teamBefore->id,
                    'after'      => $teamAfter->id,
                ),
            ),
        );

        $expectedData = array(
            'object'  => array(
                'name'   => $contact->full_name,
                'type'   => 'Contact',
                'module' => 'Contacts',
                'id'     => $contact->id,
            ),
            'changes' => array(
                'team_id' => array(
                    'field_name' => 'team_id',
                    'data_type'  => 'id',
                    'before'     => $teamBefore->name,
                    'after'      => $teamAfter->name,
                ),
            ),
        );

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', array($contact, &$activityData));

        $this->assertEquals($expectedData, $activityData);
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testChangeFields_AssignedUserIDsChangedToNames_ChangesOccurredNormally()
    {
        $lead         = SugarTestLeadUtilities::createLead();
        $assignedUser = SugarTestUserUtilities::createAnonymousUser();

        $activityData = array(
            'object'  => array(
                'name'   => $lead->full_name,
                'type'   => 'Lead',
                'module' => 'Leads',
                'id'     => $lead->id,
            ),
            'changes' => array(
                'assigned_user_id' => array(
                    'field_name' => 'assigned_user_id',
                    'data_type'  => 'id',
                    'before'     => '',
                    'after'      => $assignedUser->id,
                ),
            ),
        );

        $expectedData = array(
            'object'  => array(
                'name'   => $lead->full_name,
                'type'   => 'Lead',
                'module' => 'Leads',
                'id'     => $lead->id,
            ),
            'changes' => array(
                'assigned_user_id' => array(
                    'field_name' => 'assigned_user_id',
                    'data_type'  => 'id',
                    'before'     => '',
                    'after'      => $assignedUser->name,
                ),
            ),
        );

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', array($lead, &$activityData));

        $this->assertEquals($expectedData, $activityData);
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testChangeFields_AccountParentIdNoParentType_ChangesOccurredNormally()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $activityData = array(
            'object'  => array(
                'name'   => $account1->name,
                'type'   => 'Account',
                'module' => 'Accounts',
                'id'     => $account1->id,
            ),
            'changes' => array(
                'parent_id' =>
                array(
                    'field_name' => 'parent_id',
                    'data_type'  => 'id',
                    'before'     => '',
                    'after'      => $account2->id,
                ),
            ),
        );

        $expectedData = array(
            'object'  => array(
                'name'   => $account1->name,
                'type'   => 'Account',
                'module' => 'Accounts',
                'id'     => $account1->id,
            ),
            'changes' => array(
                'parent_id' => array(
                    'field_name' => 'parent_id',
                    'data_type'  => 'id',
                    'before'     => '',
                    'after'      => $account2->name,
                ),
            ),
        );

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', array($account1, &$activityData));

        $this->assertEquals($expectedData, $activityData);
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testChangeFields_BeanParentIdIncludesParentType_ChangesOccurredNormally()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $contact = SugarTestContactUtilities::createContact();

        $contact->parent_type = 'Accounts';
        $contact->parent_id   = $account1;
        $contact->save();

        $activityData = array(
            'object'  => array(
                'name'   => $contact->full_name,
                'type'   => 'Account',
                'module' => 'Accounts',
                'id'     => $contact->id,
            ),
            'changes' => array(
                'parent_id' => array(
                    'field_name' => 'parent_id',
                    'data_type'  => 'id',
                    'before'     => $account1->id,
                    'after'      => $account2->id,
                ),
            ),
        );

        $expectedData = array(
            'object'  => array(
                'name'   => $contact->full_name,
                'type'   => 'Account',
                'module' => 'Accounts',
                'id'     => $contact->id,
            ),
            'changes' => array(
                'parent_id' => array(
                    'field_name' => 'parent_id',
                    'data_type'  => 'id',
                    'before'     => $account1->name,
                    'after'      => $account2->name,
                ),
            ),
        );

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', array($account1, &$activityData));

        $this->assertEquals($expectedData, $activityData);
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testPrepareChanges_FieldChangesIncludeActivityDisabledField_OnlyNonDisabledFieldsReturned()
    {
        $contact = BeanFactory::newBean('Contacts');

        //mock out field defs
        $originalFieldDefs = $contact->field_defs;
        $contact->field_defs = array(
            'foo' => array(
                'name' => 'foo',
                'activity_enabled' => false,
            ),
            'bar' => array(
                'name' => 'bar',
                'audited' => true,
                'activity_enabled' => true,
            ),
            'baz' => array(
                'name' => 'baz',
            ),
            'qux' => array(
                'name' => 'qux',
                'audited' => false,
            ),
            'quux' => array(
                'name' => 'quux',
                'audited' => true,
            ),
            'qir' => array(
                'name' => 'qir',
                'audited' => false,
                'activity_enabled' => true,
            ),
            'qiir' => array(
                'name' => 'qiir',
                'audited' => true,
                'activity_enabled' => false,
            ),
            'biiru' => array(
                'name' => 'biiru',
                'activity_enabled' => true,
            ),
        );

        $activityData = array(
            'changes' => array(
                'foo' => array(
                    'field_name' => 'foo',
                    'data_type'  => 'varchar',
                    'before'     => 'fooval1',
                    'after'      => 'fooval2',
                ),
                'bar' => array(
                    'field_name' => 'bar',
                    'data_type'  => 'varchar',
                    'before'     => 'barval1',
                    'after'      => 'barval2',
                ),
                'baz' => array(
                    'field_name' => 'baz',
                    'data_type'  => 'varchar',
                    'before'     => 'bazval1',
                    'after'      => 'bazval2',
                ),
                'qux' => array(
                    'field_name' => 'qux',
                    'data_type'  => 'varchar',
                    'before'     => 'qux1',
                    'after'      => 'qux2',
                ),
                'quux' => array(
                    'field_name' => 'quux',
                    'data_type'  => 'varchar',
                    'before'     => 'quux1',
                    'after'      => 'quux2',
                ),
                'qir' => array(
                    'field_name' => 'qir',
                    'data_type'  => 'varchar',
                    'before'     => 'qirval1',
                    'after'      => 'qirval2',
                ),
                'qiir' => array(
                    'field_name' => 'qiir',
                    'data_type'  => 'varchar',
                    'before'     => 'qiirval1',
                    'after'      => 'qiirval2',
                ),
                'biiru' => array(
                    'field_name' => 'biiru',
                    'data_type'  => 'varchar',
                    'before'     => 'biiryval1',
                    'after'      => 'biiruval2',
                ),
            ),
        );

        $expectedData = array(
            'changes' => array(
                'bar' => array(
                    'field_name' => 'bar',
                    'data_type'  => 'varchar',
                    'before'     => 'barval1',
                    'after'      => 'barval2',
                ),
                'quux' => array(
                    'field_name' => 'quux',
                    'data_type'  => 'varchar',
                    'before'     => 'quux1',
                    'after'      => 'quux2',
                ),
            ),
        );

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', array($contact, &$activityData));

        //restore contact field defs
        $contact->field_defs = $originalFieldDefs;

        $this->assertEquals($expectedData, $activityData);
    }

    public function dataProviderForAddSubscriptions()
    {
        return array(
            /*  1 */  array(self::USER_ONE,    self::USER_ONE,    false, 1),
            /*  2 */  array(self::USER_ONE,    self::USER_TWO,    false, 2),
            /*  3 */  array(self::USER_ONE,    self::USER_TWO,    true,  1),
            /*  4 */  array(self::USER_ONE,    self::PORTAL_USER, false, 1),
            /*  5 */  array(self::USER_ONE,    self::BOGUS_USER,  false, 1),
            /*  6 */  array(self::PORTAL_USER, self::USER_TWO,    false, 1),
            /*  7 */  array(self::PORTAL_USER, self::USER_TWO,    true,  0),
            /*  8 */  array(self::BOGUS_USER,  self::USER_TWO,    false, 1),
            /*  9 */  array(self::BOGUS_USER,  self::USER_TWO,    true,  0),
            /* 10 */  array(self::BOGUS_USER,  self::BOGUS_USER,  false, 0),
            /* 11 */  array(self::PORTAL_USER, self::PORTAL_USER, false, 0),
        );
    }

    /**
     * @dataProvider dataProviderForAddSubscriptions
     * @covers ActivityQueueManager::createOrUpdate
     * @covers ActivityQueueManager::addRecordSubscriptions
     */
    public function testAddSubscribers(
        $arg_assigned_user,
        $arg_createdby_user,
        $isUpdate,
        $subscriptions
    ) {
        $bean = SugarTestContactUtilities::createContact();

        if ($arg_assigned_user == self::BOGUS_USER) {
            $assignedUser     = new User();
            $assignedUser->id = '000A';
        } else {
            $assignedUser = SugarTestUserUtilities::createAnonymousUser();
            if ($arg_assigned_user == self::PORTAL_USER) {
                $assignedUser->portal_only=true;
                $assignedUser->save();
            }
        }

        if ($arg_createdby_user == $arg_assigned_user) {
            $createdByUser = $assignedUser;
        } elseif ($arg_createdby_user == self::BOGUS_USER) {
            $createdByUser     = new User();
            $createdByUser->id = '000B';
        } else {
            $createdByUser = SugarTestUserUtilities::createAnonymousUser();
            if ($arg_createdby_user == self::PORTAL_USER) {
                $createdByUser->portal_only=true;
                $createdByUser->save();
            }
        }

        $bean->assigned_user_id = $assignedUser->id;
        $bean->created_by       = $createdByUser->id;

        $save_enabled = Activity::$enabled;
        Activity::enable();

        $args = array(
            'isUpdate'    => $isUpdate,
            'dataChanges' => array("assigned_user_id" => array())
        );

        $mockActivity = self::getMockBuilder('Activity')->setMethods(array('save', 'processRecord'))->getMock();
        $mockActivity->expects($this->once())
            ->method('save');
        $mockActivity->expects($this->once())
            ->method('processRecord');

        $actManager = $this->createPartialMock(
            'ActivityQueueManager',
            array(
                'subscribeUserToRecord',
                'prepareChanges'
            )
        );
        $actManager->expects($this->exactly($subscriptions))
            ->method('subscribeUserToRecord');

        SugarTestReflection::callProtectedMethod($actManager, 'createOrUpdate', array($bean, $args, $mockActivity));

        Activity::$enabled = $save_enabled;
    }

    public function dataProviderForActivityMessageCreation()
    {
        return array(
            array(true, 'after_save', 'createOrUpdate'),
            array(false, 'after_save', null),
            array(true, 'before_save', null),
            array(true, 'after_relationship_add', 'link'),
            array(true, 'after_relationship_delete', 'unlink'),
        );
    }

    /**
     * @covers ActivityQueueManager::eventDispatcher
     * @dataProvider dataProviderForActivityMessageCreation
     */
    public function testEventDispatcher_ActivityMessageCreation($activityEnabled, $event, $expectedAction)
    {
        $actions     = array(
            'createOrUpdate',
            'link',
            'unlink',
        );
        $contact     = BeanFactory::newBean('Contacts');
        $contact->id = create_guid();

        $save_enabled = Activity::$enabled;
        Activity::enable();

        if (!$activityEnabled) {
            Activity::disable();
        }
        $actManager = self::createPartialMock(
            'ActivityQueueManager',
            array('isValidLink', 'createOrUpdate', 'link', 'unlink')
        );
        $actManager->expects($this->any())->method('isValidLink')->will($this->returnValue(true));
        foreach ($actions as $action) {
            if ($action === $expectedAction) {
                $actManager->expects($this->once())->method($action)->will($this->returnValue(false));
            } else {
                $actManager->expects($this->never())->method($action);
            }
        }
        $actManager->eventDispatcher($contact, $event, array());

        Activity::$enabled = $save_enabled;
    }

    /**
     * @covers ActivityQueueManager::isEnabledForModule
     * @dataProvider dataProviderModuleBlackListWhiteList
     */
    public function testIsEnabledForModule_WithModuleBlackListAndWhiteList($moduleName, $expected, $assertMessage)
    {
        $aqm = new ActivityQueueManager();
        $this->assertEquals($expected, $aqm->isEnabledForModule($moduleName), $assertMessage);
    }

    public static function dataProviderModuleBlackListWhiteList()
    {
        return array(
            array('Forecasts', false, 'expected blacklist module to be disabled'),
            array('Notes', true, 'expected whitelist module to be enabled'),
            array('Foo', false, 'expected nonexistent module to be disabled'),
        );
    }

    /**
     * @covers ActivityQueueManager::isEnabledForModule
     * @dataProvider dataProviderDifferentActivityAndAuditFlags
     */
    public function testIsEnabledForModule_DifferentActivityAndAuditFlags($auditFlag, $activityFlag, $expected, $assertMessage)
    {
        global $dictionary;
        $moduleNameSingular = 'Contact';
        $moduleNamePlural = 'Contacts';
        $auditBefore = $dictionary[$moduleNameSingular]['audited'];
        $activityBefore = $dictionary[$moduleNameSingular]['activity_enabled'];

        $dictionary[$moduleNameSingular]['audited'] = $auditFlag;
        $dictionary[$moduleNameSingular]['activity_enabled'] = $activityFlag;
        $aqm = new ActivityQueueManager();
        $this->assertEquals($expected, $aqm->isEnabledForModule($moduleNamePlural), $assertMessage);

        //cleanup
        $dictionary[$moduleNameSingular]['audited'] = $auditBefore;
        $dictionary[$moduleNameSingular]['activity_enabled'] = $activityBefore;
    }

    public static function dataProviderDifferentActivityAndAuditFlags()
    {
        return array(
            array(true, true, true, 'expected module with activity and audit enabled to return true'),
            array(false, false, false, 'expected module with activity and audit disabled to return false'),
            array(true, false, false, 'expected module with activity disabled to return false'),
            array(false, true, false, 'expected module with audit disabled to return false'),
        );
    }

    /**
     * @covers ActivityQueueManager::assignmentChanged
     * @dataProvider dataProviderAssignedUserChanged
     */
    public function testAssignmentChanged($auditedChanges, $allDataChanges, $expected, $assertMessage)
    {
        $bean = BeanFactory::newBean('Contacts');

        //mock out db manager
        $dbManagerClass = get_class($bean->db);
        $dbManager = self::createPartialMock($dbManagerClass, array('getDataChanges'));
        $dbManager->expects($this->any())->method('getDataChanges')->will($this->returnValue($allDataChanges));
        $bean->db = $dbManager;

        $auditedDataChanges = array(
            'dataChanges' => $auditedChanges,
        );

        $actManager = new ActivityQueueManager();
        $actual = SugarTestReflection::callProtectedMethod($actManager, 'assignmentChanged', array($bean, $auditedDataChanges));
        $this->assertEquals($expected, $actual, $assertMessage);
    }

    public static function dataProviderAssignedUserChanged()
    {
        return array(
            array(
                array('assigned_user_id' => array()),
                array('assigned_user_id' => array(), 'foo' => array()),
                true,
                'Assignment changed when assigned_user_id is in audited changes',
            ),
            array(
                array(),
                array('assigned_user_id' => array(), 'foo' => array()),
                true,
                'Assignment changed when assigned_user_id is not audited, but still changed',
            ),
            array(
                array(),
                array('foo' => array()),
                false,
                'Assignment did not change when assigned_user_id is not changed at all (audited or otherwise)',
            ),
        );
    }

    /**
     * @covers ActivityQueueManager::isLinkDupe
     * @dataProvider dataProviderIsLinkDupe
     */
    public function testIsLinkDupeReturnsValidResult(array $link1, array $link2, $expected)
    {
        $aqm = new ActivityQueueManager();
        $this->assertEquals($expected, SugarTestReflection::callProtectedMethod($aqm, 'isLinkDupe', array($link1, $link2)));
    }

    public static function dataProviderIsLinkDupe()
    {
        $link1 = array(
            'id' => 'foo',
            'module' => 'Accounts',
            'related_module' => 'Leads',
            'related_id' => 'bar',
            'link' => 'leads',
            'relationship' => 'account_leads',
        );

        $link2 = array(
            'related_id' => 'foo',
            'related_module' => 'Accounts',
            'module' => 'Leads',
            'id' => 'bar',
            'link' => 'account',
            'relationship' => 'account_leads',
        );

        $link3 = array(
            'id' => 'baz',
            'module' => 'Leads',
            'related_id' => 'bar',
            'related_module' => 'Accounts',
            'link' => 'account',
            'relationship' => 'account_leads',
        );

        $link4 = array(
            'id' => 'a1',
            'module' => 'Accounts',
            'related_id' => 'c1',
            'related_module' => 'Contacts',
            'link' => 'account',
            'relationship' => 'account_contacts',
        );

        $link5 = array(
            'id' => 'a1',
            'module' => 'Accounts',
            'related_id' => 'c2',
            'related_module' => 'Contacts',
            'link' => 'account',
            'relationship' => 'account_contacts',
        );

        return array(
            array($link1, $link2, true),
            array($link1, $link3, false),
            array($link2, $link3, false),
            array($link4, $link5, false),
        );
    }
}
