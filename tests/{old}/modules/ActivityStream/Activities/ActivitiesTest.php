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

require_once 'modules/ACLActions/actiondefs.php';

/**
 * @group ActivityStream
 */
class ActivitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $activity;

    public function setUp()
    {
        parent::setUp();
        $this->activity = SugarTestActivityUtilities::createActivity();
    }

    public function tearDown()
    {
        SugarTestActivityUtilities::removeAllCreatedActivities();
        SugarTestCommentUtilities::removeAllCreatedComments();
        parent::tearDown();
    }

    /**
     * Tests that modifying a post does not modify the last comment associated
     * with the post.
     * @covers Activity
     */
    public function testThatTouchingAnActivityDoesNotModifyLastComment()
    {
        SugarTestCommentUtilities::createComment($this->activity);

        $count = $this->activity->comment_count;
        $last = $this->activity->last_comment;
        $bean = $this->activity->last_comment_bean;
        $this->activity->save();

        $this->assertEquals($count, $this->activity->comment_count);
        $this->assertEquals($last, $this->activity->last_comment);
        $this->assertEquals($bean, $this->activity->last_comment_bean);
    }

    /**
     * For a saved activity, adding a comment should return the comment object.
     * @covers Activity::addComment
     */
    public function testAddComment()
    {
        $comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->assertInternalType('string', $comment->id);
        $this->assertEquals($comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
        $this->assertEquals($comment->toJson(), $this->activity->last_comment);
    }

    /**
     * For an unsaved activity, adding a comment should return false.
     * @covers Activity::addComment
     */
    public function testAddComment2()
    {
        $record = SugarTestActivityUtilities::createUnsavedActivity();
        $comment = SugarTestCommentUtilities::createComment($record);
        $this->assertFalse($record->addComment($comment));
    }

    /**
     * addComment should only work for comments which have a parent of the
     * current activity.
     * @covers Activity::addComment
     */
    public function testAddComment3()
    {
        $record = SugarTestActivityUtilities::createActivity();
        $record2 = SugarTestActivityUtilities::createActivity();
        $comment = SugarTestCommentUtilities::createComment($record2);
        $this->assertFalse($record->addComment($comment));
    }

    /**
     * For a saved activity and comment, deleting the comment should delete it,
     * and decrements the counter.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment()
    {
        $comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->activity->deleteComment($comment->id);

        $this->assertNotEquals($comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(0, $this->activity->comment_count);
    }

    /**
     * On a saved post with no comments, deleting an arbitrary comment should do
     * nothing.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment2()
    {
        $orig_last_comment = $this->activity->last_comment_bean;
        $this->activity->deleteComment('foo');

        $this->assertEquals($orig_last_comment, $this->activity->last_comment_bean);
        $this->assertEquals(0, $this->activity->comment_count);
    }

    /**
     * On an unsaved post, deleting a comment should do nothing.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment3()
    {
        $record = SugarTestActivityUtilities::createUnsavedActivity();
        $orig_last_comment = $record->last_comment_bean;
        $record->deleteComment('foo');

        $this->assertEquals($orig_last_comment, $record->last_comment_bean);
        $this->assertEquals(0, $record->comment_count);
    }

    /**
     * For a saved activity with multiple comment, deleting the last comment
     * should delete it, and decrements the counter.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment4()
    {
        $first_comment = SugarTestCommentUtilities::createComment($this->activity);
        $second_comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->activity->deleteComment($second_comment->id);

        $this->assertEquals($first_comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
    }

    /**
     * For a saved activity with multiple comment, deleting a non-last comment
     * should delete it, and decrements the counter, but not change the last
     * comment.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment5()
    {
        $first_comment = SugarTestCommentUtilities::createComment($this->activity);
        $second_comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->activity->deleteComment($first_comment->id);

        $this->assertEquals($second_comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
    }

    /**
     * Test that data and last_comment are valid JSON when getting them from the
     * bean.
     * @covers Activity
     */
    public function testValidJson()
    {
        $this->assertInternalType('string', $this->activity->data);
        $this->assertNotEquals(false, json_decode($this->activity->data, true));

        $this->activity->retrieve($this->activity->id);
        $this->assertInternalType('string', $this->activity->data);
        $this->assertNotEquals(false, json_decode($this->activity->data, true));
        $this->assertInternalType('string', $this->activity->last_comment);
        $this->assertNotEquals(false, json_decode($this->activity->last_comment, true));

        $comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->assertInternalType('string', $this->activity->last_comment);
        $this->assertNotEquals(false, json_decode($this->activity->last_comment, true));
    }

    /**
     * @covers Activity::processPostSubscription
     */
    public function testProcessPostSubscription()
    {
        $relationshipStub = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $relationshipStub->expects($this->once())
            ->method('add');

        $stub = $this->createMock(BeanFactory::getObjectName('Activities'));
        $stub->expects($this->once())
            ->method('load_relationship')
            ->with('activities_teams')
            ->will($this->returnValue(true));
        $stub->activities_teams = $relationshipStub;

        SugarTestReflection::callProtectedMethod($stub, 'processPostSubscription', array());
    }

    public static function dataProvider_TestGetData()
    {
        return array(array('String'), array('Array'));
    }

    /**
     * @covers Activity::getDataString
     * @covers Activity::getDataArray
     * @dataProvider dataProvider_TestGetData
     */
    public function testGetData($format)
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $testData = array(
            'String' => '{"test":123}',
            'Array' => array('test' => 123),
        );

        foreach ($testData as $data) {
            $activity->data = $data;
            $result = SugarTestReflection::callProtectedMethod($activity, 'getData'.$format);
            $this->assertEquals($result, $testData[$format]);
        }
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_NullParentType_ReturnsNull()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = null;
        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertNull($result, 'Should return null when parent_type is null');
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_NullParentId_ReturnsAnEmptyBean()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = 'Contacts';
        $activity->parent_id = null;
        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertEmpty($result->id, 'Should return an empty bean');
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_DeleteActivity_ReturnsTheDeletedBean()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->mark_deleted($contact->id);
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = $contact->module_name;
        $activity->parent_id = $contact->id;
        $activity->activity_type = 'delete';
        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertEquals($contact->id, $result->id, 'Should return deleted bean');
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_NonDeleteActivity_ReturnsTheBean()
    {
        $contact = SugarTestContactUtilities::createContact();
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = $contact->module_name;
        $activity->parent_id = $contact->id;
        $activity->activity_type = 'update';
        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertEquals($contact->id, $result->id, 'Should return the bean');
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_NonDeleteActivityAndTheBeanIsDeleted_ReturnsNull()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->mark_deleted($contact->id);
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = $contact->module_name;
        $activity->parent_id = $contact->id;
        $activity->activity_type = 'update';
        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertNull($result, 'Should return null when the bean is deleted and it is not a delete activity');
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * @covers Activity::getChangedFieldsForUser
     */
    public function testGetChangedFieldsForUser_NonUpdateActivity_ReturnsEmptyArray()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->activity_type = 'foo';
        $activity->data = '{changes: []}';

        $bean = $this->createMock('SugarBean');
        $bean->expects($this->never())->method('ACLFilterFieldList');

        $result = SugarTestReflection::callProtectedMethod(
            $activity,
            'getChangedFieldsForUser',
            array(new User(), $bean)
        );
        $this->assertEquals(array(), $result, "Should return empty array");
    }

    /**
     * @covers Activity::getChangedFieldsForUser
     */
    public function testGetChangedFieldsForUser_NoDataChanges_ReturnsEmptyArray()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->activity_type = 'update';
        $activity->data = '{}';

        $bean = $this->createMock('SugarBean');
        $bean->expects($this->never())->method('ACLFilterFieldList');

        $result = SugarTestReflection::callProtectedMethod(
            $activity,
            'getChangedFieldsForUser',
            array(new User(), $bean)
        );
        $this->assertEquals(array(), $result, "Should return empty array");
    }

    /**
     * @covers Activity::getChangedFieldsForUser
     */
    public function testGetChangedFieldsForUser_DataChangesExist_ChecksACLAndReturnsFields()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->activity_type = 'update';
        $activity->data = '{"changes": [{"field_name": "foo"},{"field_name": "bar"}]}';

        $bean = $this->createMock('SugarBean');
        $bean->expects($this->once())->method('ACLFilterFieldList');

        $result = SugarTestReflection::callProtectedMethod(
            $activity,
            'getChangedFieldsForUser',
            array(new User(), $bean)
        );
        $this->assertEquals(array('foo','bar'), $result, "Should return array with two fields");
    }

    /**
     * @covers Activity::processPostTags
     */
    public function testProcessPostTags_WithTags_CallsProcessTags()
    {
        $activity = $this->createPartialMock('Activity', array('processTags'));
        $activity->expects($this->once())->method('processTags');

        $activity->data = '{"tags": ["tag1","tag2"]}';
        SugarTestReflection::callProtectedMethod($activity, 'processPostTags');
    }

    /**
     * @covers Activity::processPostTags
     */
    public function testProcessPostTags_WithNoTags_DoesNotCallProcessTags()
    {
        $activity = $this->createPartialMock('Activity', array('processTags'));
        $activity->expects($this->never())->method('processTags');

        $activity->data = '{}';
        SugarTestReflection::callProtectedMethod($activity, 'processPostTags');
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_WithNoTags_DoesNotProcessAnyRelationships()
    {
        $tags = array();
        $activity = $this->createPartialMock('Activity', array('processUserRelationships', 'processRecord'));
        $activity->expects($this->never())->method('processUserRelationships');
        $activity->expects($this->never())->method('processRecord');
        $activity->processTags($tags);
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_UserTagNonPostActivity_CallsProcessUserRelationships()
    {
        $tags = array(
            array('module'=>'Users', 'id'=>'123'),
        );
        $activity = $this->createPartialMock('Activity', array('processUserRelationships', 'processRecord'));
        $activity->expects($this->once())
            ->method('processUserRelationships')
            ->with($this->equalTo(array('123')));
        $activity->expects($this->never())->method('processRecord');

        $activity->parent_id = '456';
        $activity->processTags($tags);
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_UserTagPostToModuleWithUserAccess_CallsProcessRecord()
    {
        $user = BeanFactory::newBean('Users');
        BeanFactory::registerBean($user);
        $tags = array(
            array(
                'module' => $user->module_name,
                'id' => $user->id,
            ),
        );
        $activity = $this->createPartialMock(
            'Activity',
            array(
                'processUserRelationships',
                'processRecord',
                'userHasViewAccessToParentModule',
            )
        );
        $activity->expects($this->once())->method('userHasViewAccessToParentModule')->will($this->returnValue(true));
        $activity->expects($this->once())->method('processRecord');
        $activity->expects($this->never())->method('processUserRelationships');
        $activity->parent_type = 'Foo';
        $activity->processTags($tags);
        BeanFactory::unregisterBean($user);
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_UserTagPostToModuleWithNoAccess_DoesNotProcessAnyRelationships()
    {
        $tags = array(
            array('module'=>'Users', 'id'=>'123'),
        );
        $activity = $this->createPartialMock('Activity', array(
            'processUserRelationships',
            'processRecord',
            'userHasViewAccessToParentModule',
        ));
        $activity->expects($this->once())
            ->method('userHasViewAccessToParentModule')
            ->will($this->returnValue(false));
        $activity->expects($this->never())->method('processRecord');
        $activity->expects($this->never())->method('processUserRelationships');

        $activity->parent_type = 'Bar';
        $activity->processTags($tags);
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_NonUserTag_CallsProcessRecord()
    {
        $contact = BeanFactory::newBean('Contacts');
        BeanFactory::registerBean($contact);
        $tags = array(
            array(
                'module' => $contact->module_name,
                'id' => $contact->id,
            ),
        );
        $activity = $this->createPartialMock(
            'Activity',
            array(
                'processUserRelationships',
                'processRecord',
            )
        );
        $activity->expects($this->once())->method('processRecord');
        $activity->expects($this->never())->method('processUserRelationships');
        $activity->processTags($tags);
        BeanFactory::unregisterBean($contact);
    }

    /**
     * @covers Activity::userHasViewAccessToParentModule
     */
    public function testUserHasViewAccessToParentModule_NoParentType_ReturnsTrue()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = null;
        $result = SugarTestReflection::callProtectedMethod(
            $activity,
            'userHasViewAccessToParentModule',
            array(array('123'))
        );
        $this->assertTrue($result);
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_NoRelationship_ReturnsFalse()
    {
        $activity = $this->createPartialMock('Activity', array('load_relationship'));
        $activity->expects($this->once())
            ->method('load_relationship')
            ->will($this->returnValue(false));

        $result = $activity->processUserRelationships();
        $this->assertFalse($result);
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_NoUserIds_HandleUserToBeanRelationshipIsNotCalled()
    {
        $relationship = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $relationship->expects($this->never())->method('add');
        $activity = $this->createPartialMock(
            'Activity',
            array(
                'load_relationship',
                'getParentBean',
                'handleUserToBeanRelationship'
            )
        );
        $activity->expects($this->once())->method('load_relationship')->will($this->returnValue(true));
        $activity->expects($this->once())->method('getParentBean')->will($this->returnValue(null));
        $activity->expects($this->never())->method('handleUserToBeanRelationship');
        $activity->activities_users = $relationship;
        $activity->processUserRelationships(array());
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_OneOfTwoUsersExists_HandleUserToBeanRelationshipIsOnlyCalledOnce()
    {
        $parentBean = BeanFactory::newBean('Contacts');
        $relationship = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $relationship->expects($this->never())->method('add');
        $activity = $this->createPartialMock(
            'Activity',
            array(
                'load_relationship',
                'getParentBean',
                'handleUserToBeanRelationship'
            )
        );
        $activity->expects($this->once())->method('load_relationship')->will($this->returnValue(true));
        $activity->expects($this->once())->method('getParentBean')->will($this->returnValue($parentBean));
        $activity->expects($this->once())->method('handleUserToBeanRelationship')->will($this->returnValue(true));
        $activity->activities_users = $relationship;
        $user = BeanFactory::newBean('Users');
        BeanFactory::registerBean($user);
        $activity->processUserRelationships(array($user->id, create_guid()));
        BeanFactory::unregisterBean($user);
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_ParentBeanDoesNotExist_HandleUserToBeanRelationshipIsNotCalled()
    {
        $relationship = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $relationship->expects($this->never())->method('add');
        $activity = $this->createPartialMock(
            'Activity',
            array(
                'load_relationship',
                'getParentBean',
                'handleUserToBeanRelationship'
            )
        );
        $activity->expects($this->once())->method('load_relationship')->will($this->returnValue(true));
        $activity->expects($this->once())->method('getParentBean')->will($this->returnValue(null));
        $activity->expects($this->never())->method('handleUserToBeanRelationship');
        $activity->activities_users = $relationship;
        $user = BeanFactory::newBean('Users');
        BeanFactory::registerBean($user);
        $activity->processUserRelationships(array($user->id));
        BeanFactory::unregisterBean($user);
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_UserAndParentExist_HandleUserToBeanRelationshipIsCalled()
    {
        $parentBean = BeanFactory::newBean('Contacts');
        $relationship = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $relationship->expects($this->never())->method('add');
        $activity = $this->createPartialMock(
            'Activity',
            array(
                'load_relationship',
                'getParentBean',
                'handleUserToBeanRelationship'
            )
        );
        $activity->expects($this->once())->method('load_relationship')->will($this->returnValue(true));
        $activity->expects($this->once())->method('getParentBean')->will($this->returnValue($parentBean));
        $activity->expects($this->once())->method('handleUserToBeanRelationship');
        $activity->activities_users = $relationship;
        $user = BeanFactory::newBean('Users');
        BeanFactory::registerBean($user);
        $activity->processUserRelationships(array($user->id));
        BeanFactory::unregisterBean($user);
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_UserHasAccessToParent_RelationshipIsAdded()
    {
        $parentBean = $this->createPartialMock('SugarBean', array('checkUserAccess'));
        $parentBean->expects($this->once())->method('checkUserAccess')->will($this->returnValue(true));
        $relationship = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $relationship->expects($this->once())->method('add');
        $activity = $this->createPartialMock('Activity', array('load_relationship', 'getParentBean', 'getChangedFieldsForUser'));
        $activity->expects($this->once())->method('load_relationship')->will($this->returnValue(true));
        $activity->expects($this->once())->method('getParentBean')->will($this->returnValue($parentBean));
        $activity->expects($this->once())->method('getChangedFieldsForUser')->will($this->returnValue(true));
        $activity->activities_users = $relationship;
        $user = BeanFactory::newBean('Users');
        BeanFactory::registerBean($user);
        $activity->processUserRelationships(array($user->id));
        BeanFactory::unregisterBean($user);
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_UserDoesNotHaveAccessToTheParent_SubscriptionIsRemoved()
    {
        $contact = SugarTestContactUtilities::createContact();
        $user = SugarTestUserUtilities::createAnonymousUser();
        Subscription::subscribeUserToRecord($user, $contact, array('disable_row_level_security' => true));
        $relationship = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $relationship->expects($this->never())->method('add');
        $activity = $this->createPartialMock('Activity', array('load_relationship', 'getParentBean', 'getChangedFieldsForUser'));
        $activity->expects($this->once())->method('load_relationship')->will($this->returnValue(true));
        $activity->expects($this->once())->method('getParentBean')->will($this->returnValue($contact));
        $activity->expects($this->never())->method('getChangedFieldsForUser');
        $activity->activities_users = $relationship;
        $this->assertNotEmpty(Subscription::checkSubscription($user, $contact), 'The user should be subscribed');
        $data = array(
            'module' => array(
                'access' => array(
                    'aclaccess' => ACL_ALLOW_DISABLED,
                ),
            ),
        );
        ACLAction::setACLData($user->id, 'Contacts', $data);
        $activity->processUserRelationships(array($user->id));
        $this->assertEmpty(Subscription::checkSubscription($user, $contact), 'The user should not be subscribed');
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarACL::$acls = array();
    }
}
