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
 * @group api
 * @group activities
 */
class ActivitiesApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
        $this->api       = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user'];
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers ActivitiesApi::getQueryObject
     */
    public function testGetQueryObject_ForHomePage_ShowsOnlyHomePostsAndActivitiesUserLinkedTo()
    {
        $query = SugarTestReflection::callProtectedMethod(
            'ActivitiesApi',
            'getQueryObject',
            array(
                $this->getMock('SugarBean'),
                array('offset' => 0, 'limit' => 5),
                $this->api,
                true,
            )
        );
        $sql = $query->compile()->getSQL();
        $this->assertContains('activities.parent_type IS NULL', $sql);
        $this->assertContains('activities_users.parent_type = ?', $sql);
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_HomePage_MultipleModuleTypes_UserHasMixedFieldAccess_AppropriateFieldChangesReturned()
    {
        $records = array(
            array(
                'activities__date_modified' => '2015-07-09 15:09:57',
                'id' => 'ba0105b3-a975-9ea9-f1ed-559e8e1699c9',
                'date_entered' => '2015-07-09 15:09:57',
                'date_modified' => '2015-07-09 15:09:57',
                'modified_user_id' => '1',
                'created_by' => '1',
                'deleted' => '0',
                'parent_id' => '',
                'parent_type' => '',
                'activity_type' => 'update',
                'data' => json_encode(
                    array(
                        'object' => array(
                            'type' => 'Lead',
                            'module' => 'Leads',
                            'name' => 'Davey Crockett',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before' => 'xxx',
                                'after' => 'yyy',
                            ),
                        ),
                    )
                ),
                'comment_count' => '0',
                'last_comment' => '{"name":"","deleted":false,"data":[]}',
                'first_name' => 'Davey',
                'last_name' => 'Crockett',
                'picture' => '1d74bee8-a666-72ea-ed32-559bd81b44ec',
                'fields' => json_encode(array('first_name', 'last_name', 'lead_source', 'city')),
            ),
            array(
                'activities__date_modified' => '2015-07-09 15:09:55',
                'id' => '12037c89-f75f-a8fb-1284-559e8e347e76',
                'date_entered' => '2015-07-09 15:09:55',
                'date_modified' => '2015-07-09 15:09:55',
                'modified_user_id' => '1',
                'created_by' => '1',
                'deleted' => '0',
                'parent_id' => '',
                'parent_type' => '',
                'activity_type' => 'update',
                'data' => json_encode(
                    array(
                        'object' => array(
                            'type' => 'Contact',
                            'module' => 'Contacts',
                            'name' => 'Jim Bowie',
                        ),
                        'changes' => array(
                            'opt_out' => array(
                                'field_name' => 'opt_out',
                                'before' => false,
                                'after' => true,
                            ),
                        ),
                    )
                ),
                'comment_count' => '0',
                'last_comment' => '{"name":"","deleted":false,"data":[]}',
                'first_name' => 'Jim',
                'last_name' => 'Bowie',
                'picture' => '1d74bee8-a666-72ea-ed32-559bd81b44ec',
                'fields' => json_encode(array('opt_out')),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expectedLeadDataChanges = array(
            'object' => array(
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'Davey Crockett',
            ),
            'changes' => array(              // User Has Access to lead_source field - Change Data Expected
                'lead_source' => array(
                    'field_name' => 'lead_source',
                    'before' => 'xxx',
                    'after' => 'yyy',
                ),
            ),
        );

        $expectedContactDataChanges =  array(
            'object'  => array(
                'type'   => 'Contact',
                'module' => 'Contacts',
                'name'   => 'Jim Bowie',
            ),
            'changes' => array(),
        );

        $sugarQueryMock = $this->createPartialMock('SugarQuery', array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $aclLead = new TestSugarACLStatic();
        $aclLead->return_value = array('lead_source' => true);  //User Has Field Level Access to Leads::lead_source field
        $aclContact = new TestSugarACLStatic();
        $aclContact->return_value = array('opt_out' => false);     //User Does Not Have Field Level Access to Contacts::opt_out field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($aclLead);
        SugarACL::$acls['Contacts'] = array($aclContact);

        $activitiesApi = new TestActivitiesApi();
        $actual = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, null);

        $this->assertEquals($expectedLeadDataChanges, $actual['records'][0]['data'], "Expected Activities Records with Field Access Applied correctly across Modules");
        $this->assertEquals($expectedContactDataChanges, $actual['records'][1]['data'], "Expected Activities Records with Field Access Applied correctly across Modules");
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_ListView_UserHasFieldAccess_FieldChangesReturned()
    {
        $records = array(
            array(
                'display_parent_type' => '',
                'display_parent_id' => '',
                'comment_count' => 0,
                'last_comment' => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered' => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'fields' => json_encode(array('first_name', 'last_name', 'lead_source', 'city')),
                'data' => json_encode(
                    array(
                        'object' => array(
                            'type' => 'Lead',
                            'module' => 'Leads',
                            'name' => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before' => 'xxx',
                                'after' => 'yyy',
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expectedDataChanges = array(
            'object' => array(
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'John Doe',
            ),
            'changes' => array(
                'lead_source' => array(
                    'field_name' => 'lead_source',
                    'before' => 'xxx',
                    'after' => 'yyy',
                ),
            ),
        );

        $sugarQueryMock = $this->createPartialMock('SugarQuery', array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        $acl->return_value = array('lead_source' => true);  // User Has Field Level Access to Leads::lead_source field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($acl);

        $activitiesApi = new TestActivitiesApi();
        $actual = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, null);

        $this->assertEquals($expectedDataChanges, $actual['records'][0]['data'], "Expected Activities Records with Changed Fields Listed");
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_ListView_UserDoesNotHaveFieldAccess_FieldChangesNotReturned()
    {
        $records   = array(
            array(
                'display_parent_type' => '',
                'display_parent_id' => '',
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name'    => 'John',
                'last_name'     => 'Doe',
                'fields'        => json_encode(array('first_name', 'last_name', 'lead_source', 'city')),
                'data'          => json_encode(
                    array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expectedDataChanges = array(
            'object' => array(
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'John Doe',
            ),
            'changes' => array(),
        );

        $sugarQueryMock = $this->createPartialMock('SugarQuery', array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl                     = new TestSugarACLStatic();
        $acl->return_value       = array('lead_source' => false);  //User Has No Field Level Access to lead_source field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($acl);

        $activitiesApi = new TestActivitiesApi();
        $actual        = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, null);

        $this->assertEquals($expectedDataChanges, $actual['records'][0]['data'], "Expected Activities Records without data for Changed Fields");
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_RecordView_UserDoesNotHaveFieldAccess_FieldChangesNotReturned()
    {
        $records   = array(
            array(
                'display_parent_type' => '',
                'display_parent_id' => '',
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name'    => 'John',
                'last_name'     => 'Doe',
                'fields'        => json_encode(array('first_name', 'last_name', 'lead_source')),
                'data'          => json_encode(
                    array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                            'first_name' => array(
                                'field_name' => 'first_name',
                                'before'     => 'Johnathan',
                                'after'      => 'John',
                            ),
                            'last_name' => array(
                                'field_name' => 'last_name',
                                'before'     => 'Dough',
                                'after'      => 'Doe',
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expectedDataChanges = array(
        'object'  => array(
            'type'   => 'Lead',
            'module' => 'Leads',
            'name'   => 'John Doe',
        ),
        'changes' => array(),
    );

        $sugarQueryMock = $this->createPartialMock('SugarQuery', array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        //User Has Field Level Access to lead_source, first_name and last_name fields
        $acl->return_value  = array(
            'lead_source' => false,
            'first_name'  => false,
            'last_name'   => false,
        );
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($acl);

        $lead = SugarTestLeadUtilities::createLead();

        $activitiesApi = new TestActivitiesApi();
        $actual        = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, $lead);

        $this->assertEquals($expectedDataChanges, $actual['records'][0]['data'], "Expected Activities Records without data for Changed Fields");
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_RecordView_UserHasFieldAccess_FieldChangesReturned()
    {
        $records = array(
            array(
                'display_parent_type' => '',
                'display_parent_id' => '',
                'comment_count' => 0,
                'last_comment' => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered' => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'fields' => json_encode(array('first_name', 'last_name', 'lead_source')),
                'data' => json_encode(
                    array(
                        'object' => array(
                            'type' => 'Lead',
                            'module' => 'Leads',
                            'name' => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before' => 'xxx',
                                'after' => 'yyy',
                            ),
                            'first_name' => array(
                                'field_name' => 'first_name',
                                'before' => 'Johnathan',
                                'after' => 'John',
                            ),
                            'last_name' => array(
                                'field_name' => 'last_name',
                                'before' => 'Dough',
                                'after' => 'Doe',
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expectedDataChanges = array(
            'object' => array(
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'John Doe',
            ),
            'changes' => array(
                'lead_source' => array(
                    'field_name' => 'lead_source',
                    'before' => 'xxx',
                    'after' => 'yyy',
                ),
                'first_name' => array(
                    'field_name' => 'first_name',
                    'before' => 'Johnathan',
                    'after' => 'John',
                ),
                'last_name' => array(
                    'field_name' => 'last_name',
                    'before' => 'Dough',
                    'after' => 'Doe',
                ),
            ),
        );

        $sugarQueryMock = $this->createPartialMock('SugarQuery', array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        //User Has Field Level Access to lead_source, first_name and last_name fields
        $acl->return_value = array(
            'lead_source' => true,
            'first_name' => true,
            'last_name' => true,
        );
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($acl);

        $lead = SugarTestLeadUtilities::createLead();

        $activitiesApi = new TestActivitiesApi();
        $actual = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, $lead);

        $this->assertEquals($expectedDataChanges, $actual['records'][0]['data'], "Expected Activities Records with all data for Changed Fields");
    }

    /**
     * @covers ActivitiesApi::checkParentPreviewEnabled
     */
    public function testCheckParentPreviewEnabled_CheckAlreadyPerformedForRecord_ReturnCachedResults()
    {
        $activitiesApi = new TestActivitiesApi();
        $cachedResults = array(
            'Foo.123' => array(
                'preview_enabled' => false,
                'preview_disabled_reason' => 'Bar!!'
            )
        );
        $activitiesApi->setPreviewCheckResults($cachedResults);

        $actualResult = $activitiesApi->exec_checkParentPreviewEnabled($this->api->user, 'Foo', '123');

        $this->assertEquals($cachedResults['Foo.123'], $actualResult, 'Expected result to be pulled from the cached results');
    }

    /**
     * @covers ActivitiesApi::checkParentPreviewEnabled
     */
    public function testCheckParentPreviewEnabled_UserHasAccess_ReturnPreviewEnabledAndEmptyReason()
    {
        $activitiesApi = new TestActivitiesApi();
        $cachedResults = array(
            'Foo.123' => array(
                'preview_enabled' => false,
                'preview_disabled_reason' => 'Bar!!'
            )
        );
        $activitiesApi->setPreviewCheckResults($cachedResults);
        $beanList = array(
            'Foo' => new TestCheckAccessBean()
        );
        $activitiesApi->setBeanList($beanList);

        $expectedResult = array(
            'preview_enabled' => true,
            'preview_disabled_reason' => ''
        );

        $actualResult = $activitiesApi->exec_checkParentPreviewEnabled($this->api->user, 'Foo', '456');

        $this->assertEquals($expectedResult, $actualResult, 'Expected result to be preview enabled with empty reason');
    }

    /**
     * @covers ActivitiesApi::checkParentPreviewEnabled
     */
    public function testCheckParentPreviewEnabled_UserNoAccess_ReturnPreviewEnabledAndEmptyReason()
    {
        $activitiesApi = new TestActivitiesApi();
        $cachedResults = array(
            'Foo.123' => array(
                'preview_enabled' => false,
                'preview_disabled_reason' => 'Bar!!'
            )
        );
        $activitiesApi->setPreviewCheckResults($cachedResults);
        $mockBean = new TestCheckAccessBean();
        $mockBean->checkUserAccessResult = false;
        $beanList = array(
            'Foo' => $mockBean
        );
        $activitiesApi->setBeanList($beanList);

        $expectedResult = array(
            'preview_enabled' => false,
            'preview_disabled_reason' => 'LBL_PREVIEW_DISABLED_DELETED_OR_NO_ACCESS'
        );

        $actualResult = $activitiesApi->exec_checkParentPreviewEnabled($this->api->user, 'Foo', '789');

        $this->assertEquals($expectedResult, $actualResult, 'Expected result to not be preview enabled with correct reason');
    }

    /**
     * @covers ActivitiesApi::checkParentPreviewEnabled
     */
    public function testCheckParentPreviewEnabled_RecordDeleted_ReturnPreviewEnabledAndEmptyReason()
    {
        //full functional test for this to ensure that checkUserAccess returns false for deleted records
        $lead = SugarTestLeadUtilities::createLead();
        $lead->mark_deleted($lead->id);

        $expectedResult = array(
            'preview_enabled' => false,
            'preview_disabled_reason' => 'LBL_PREVIEW_DISABLED_DELETED_OR_NO_ACCESS'
        );

        $activitiesApi = new TestActivitiesApi();
        $actualResult = $activitiesApi->exec_checkParentPreviewEnabled($this->api->user, 'Leads', $lead->id);

        $this->assertEquals($expectedResult, $actualResult, 'Expected result to not be preview enabled with correct reason');
    }
}

class TestActivitiesApi extends ActivitiesApi
{
    public function exec_formatResult(ServiceBase $api, array $args, SugarQuery $query, SugarBean $bean = null)
    {
        return $this->formatResult($api, $args, $query, $bean);
    }
    public function exec_checkParentPreviewEnabled($user, $module, $id)
    {
        return $this->checkParentPreviewEnabled($user, $module, $id);
    }
    public function setBeanList($beanList)
    {
        self::$beanList = $beanList;
    }
    public function setPreviewCheckResults($previewCheckResults)
    {
        self::$previewCheckResults = $previewCheckResults;
    }
}

class TestSugarACLStatic extends SugarACLStatic
{
    public $return_value = null;

    public function checkFieldList($module, $field_list, $action, $context)
    {
        return $this->return_value;
    }
}

class TestCheckAccessBean
{
    public $checkUserAccessResult = true;

    public function checkUserAccess($user) {
        return $this->checkUserAccessResult;
    }
}
