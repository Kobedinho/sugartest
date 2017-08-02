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
class PMSECrmDataWrapperTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var PMSECrmDataWrapper
     */
    protected $object;
    protected $beanFactory;
    protected $adamBeanFactory;

    protected $processDefinitionBean;
    protected $activityDefinitionBean;
    protected $dynaformBean;
    protected $projectBean;
    protected $processBean;
    protected $activityBean;
    protected $ruleSetBean;
    protected $teamsBean;
    protected $usersBean;
    protected $emailTemplateBean;
    protected $inboxBean;

    protected $sugarQueryMock;

    protected $teams;
    protected $users;

    protected $beanList;
    protected $db;

    protected $originals = array();

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->originals['current_user'] = $GLOBALS['current_user'];
        $this->originals['db'] = $GLOBALS['db'];

        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->beanList = array(
            'ACLRoles' => 'ACLRole',
            'ACLActions' => 'ACLAction',
            'ACLFields' => 'ACLField',
            'Leads' => 'Lead',
            'Cases' => 'aCase',
            'Bugs' => 'Bug',
            'ProspectLists' => 'ProspectList',
            'Prospects' => 'Prospect',
            'Project' => 'Project',
            'ProjectTask' => 'ProjectTask',
            'Campaigns' => 'Campaign',
            'EmailMarketing' => 'EmailMarketing',
            'CampaignLog' => 'CampaignLog',
            'CampaignTrackers' => 'CampaignTracker',
            'Releases' => 'Release',
            'Groups' => 'Group',
            'EmailMan' => 'EmailMan',
            'Schedulers' => 'Scheduler',
            'SchedulersJobs' => 'SchedulersJob',
            'Contacts' => 'Contact',
            'Accounts' => 'Account',
            'DynamicFields' => 'DynamicField',
            'EditCustomFields' => 'FieldsMetaData',
            'Opportunities' => 'Opportunity',
            'EmailTemplates' => 'EmailTemplate',
            'Notes' => 'Note',
            'Calls' => 'Call',
            'Emails' => 'Email',
            'Meetings' => 'Meeting',
            'Tasks' => 'Task',
            'Users' => 'User',
            'Currencies' => 'Currency',
            'Trackers' => 'Tracker',
            'Connectors' => 'Connectors',
            'TrackerSessions' => 'TrackerSession',
            'TrackerPerfs' => 'TrackerPerf',
            'TrackerQueries' => 'TrackerQuery',
            'Import_1' => 'ImportMap',
            'Import_2' => 'UsersLastImport',
            'Versions' => 'Version',
            'Administration' => 'Administration',
            'vCals' => 'vCal',
            'CustomFields' => 'CustomFields',
            'Documents' => 'Document',
            'DocumentRevisions' => 'DocumentRevision',
            'Roles' => 'Role',
            'Audit' => 'Audit',
            'InboundEmail' => 'InboundEmail',
            'SavedSearch' => 'SavedSearch',
            'UserPreferences' => 'UserPreference',
            'MergeRecords' => 'MergeRecord',
            'EmailAddresses' => 'EmailAddress',
            'EmailText' => 'EmailText',
            'Relationships' => 'Relationship',
            'Employees' => 'Employee',
            'Reports' => 'SavedReport',
            'Reports_1' => 'SavedReport',
            'Teams' => 'Team',
            'TeamMemberships' => 'TeamMembership',
            'TeamSets' => 'TeamSet',
            'TeamSetModules' => 'TeamSetModule',
            'Quotes' => 'Quote',
            'Products' => 'Product',
            'ProductBundles' => 'ProductBundle',
            'ProductBundleNotes' => 'ProductBundleNote',
            'ProductTemplates' => 'ProductTemplate',
            'ProductTypes' => 'ProductType',
            'ProductCategories' => 'ProductCategory',
            'Manufacturers' => 'Manufacturer',
            'Shippers' => 'Shipper',
            'TaxRates' => 'TaxRate',
            'TeamNotices' => 'TeamNotice',
            'TimePeriods' => 'TimePeriod',
            'AnnualTimePeriods' => 'AnnualTimePeriod',
            'QuarterTimePeriods' => 'QuarterTimePeriod',
            'Quarter544TimePeriods' => 'Quarter544TimePeriod',
            'Quarter445TimePeriods' => 'Quarter445TimePeriod',
            'Quarter454TimePeriods' => 'Quarter454TimePeriod',
            'MonthTimePeriods' => 'MonthTimePeriod',
            'Forecasts' => 'Forecast',
            'ForecastWorksheets' => 'ForecastWorksheet',
            'ForecastManagerWorksheets' => 'ForecastManagerWorksheet',
            'ForecastSchedule' => 'ForecastSchedule',
            'Worksheet' => 'Worksheet',
            'ForecastOpportunities' => 'ForecastOpportunities',
            'Quotas' => 'Quota',
            'WorkFlow' => 'WorkFlow',
            'WorkFlowTriggerShells' => 'WorkFlowTriggerShell',
            'WorkFlowAlertShells' => 'WorkFlowAlertShell',
            'WorkFlowAlerts' => 'WorkFlowAlert',
            'WorkFlowActionShells' => 'WorkFlowActionShell',
            'WorkFlowActions' => 'WorkFlowAction',
            'Expressions' => 'Expression',
            'Contracts' => 'Contract',
            'KBDocuments' => 'KBDocument',
            'KBDocumentRevisions' => 'KBDocumentRevision',
            'KBTags' => 'KBTag',
            'KBDocumentKBTags' => 'KBDocumentKBTag',
            'KBContents' => 'KBContent',
            'ContractTypes' => 'ContractType',
            'Holidays' => 'Holiday',
            'CustomQueries' => 'CustomQuery',
            'DataSets' => 'DataSet',
            'ReportMaker' => 'ReportMaker',
            'SugarFeed' => 'SugarFeed',
            'Notifications' => 'Notifications',
            'EAPM' => 'EAPM',
            'OAuthKeys' => 'OAuthKey',
            'OAuthTokens' => 'OAuthToken',
            'SugarFavorites' => 'SugarFavorites',
            'PdfManager' => 'PdfManager',
            'ProcessMaker' => 'BpmnProject',
        );

        $this->beanFactory = $this->getMockBuilder("BeanFactory")
                ->setMethods(array('getBean'))
                ->getMock();
        $this->teams = $this->getMockBuilder('Teams')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('teamsAreSupported', 'getList', 'getDisplayName'))
                ->getMock();
        $this->users = $this->getMockBuilder('Users')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('get_full_list'))
                ->getMock();
        $this->processDefinitionBean = $this->getMockBuilder('pmse_BpmProcessDefinition')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'getSelectRows', 'retrieve'))
                ->getMock();
        $this->activityDefinitionBean = $this->getMockBuilder('SugarBean')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'getSelectRows'))
                ->getMock();
        $this->dynaformBean = $this->getMockBuilder('pmse_BpmDynaForm')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'getSelectRows', 'get_full_list'))
                ->getMock();
        $this->projectBean = $this->getMockBuilder('pmse_BpmnProject')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'getSelectRows', 'retrieve'))
                ->getMock();
        $this->processBean = $this->getMockBuilder('pmse_BpmnProcess')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'getSelectRows'))
                ->getMock();
        $this->ruleSetBean = $this->getMockBuilder('pmse_BpmRuleSet')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'getSelectRows', 'get_full_list'))
                ->getMock();
        $this->emailTemplateBean = $this->getMockBuilder('pmse_BpmEmailTemplate')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'getSelectRows', 'get_full_list'))
                ->getMock();
        $this->inboxBean = $this->getMockBuilder('pmse_BpmInbox')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'getSelectRows'))
                ->getMock();
        $this->teamsBean = $this->getMockBuilder('Teams')
                ->disableAutoload()
                ->disableORiginalConstructor()
                ->setMethods(array('get_full_list'))
                ->getMock();
        $this->usersBean = $this->getMockBuilder('Users')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('get_full_list'))
                ->getMock();
        $this->sugarQueryMock = $this->getMockBuilder('SugarQuery')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(
                array('select', 'from', 'joinTable', 'on', 'equalsField', 'where', 'execute', 'equals')
            )
            ->getMock();
        $this->sugarQueryMock->expects($this->any())
            ->method('joinTable')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->any())
            ->method('on')
            ->willReturnSelf();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        foreach($this->originals as $varname => $value) {
            $GLOBALS[$varname] = $value;
        }
        parent::tearDown();
    }

    /**
     * @covers PMSECrmDataWrapper::_get
     * @todo   Implement test_get().
     */
    public function test_get()
    {

    }

    /**
     * @covers PMSECrmDataWrapper::invalidRequest
     * @todo   Implement testInvalidRequest().
     */
    public function testInvalidRequest()
    {
//        $this->object = new PMSECrmDataWrapper();

        $expected = array('success' => false, 'message' => 'Invalid Request');
        $result = $this->object->invalidRequest();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveEmails
     * @todo   Implement testAddRelatedRecord().
     */
    public function testRetrieveEmailsWithEmptyFilter() {
//        $this->object = new PMSECrmDataWrapper();

        $expectedResult = array('success' => false, 'message' => 'Invalid Request');

        $result = $this->object->retrieveEmails('');
        $this->assertEquals($expectedResult, $result);

    }

    /**
     * @covers PMSECrmDataWrapper::retrieveEmails
     * @todo   Implement testAddRelatedRecord().
     */
    public function testRetrieveEmailsWithFilter()
    {
        $sampleModule = 'Opportunities';
        // setting up the inbound email bean
        $inboundEmailBean = $this->getMockBuilder('InboundEmail')
                ->setMethods(array('email2init'))
                ->getMock();

        $dbHandler = $this->getMockBuilder('db')
                ->setMethods(array('limitQuery', 'fetchByAssoc'))
                ->getMock();

        //fictional rowdata returned from the database
        $testRows = array(
            array('first_name' => 'Aldo', 'last_name'=>'Rayne', 'email_address'=>'arayne@example.com', 'id'=>null),
            array('first_name' => 'Dominic', 'last_name'=>'Margarete', 'email_address'=>'dmargarete@example.com', 'id'=>'2'),
            array('first_name' => 'Bridgitte', 'last_name'=>'Hammershmack', 'email_address'=>'bhammer@example.com', 'id'=>'3'),
        );
        // expect just one query call
        $dbHandler->expects($this->exactly(1))
                ->method('limitQuery')
                ->will($this->returnValue($testRows));

        //expect
        $dbHandler->expects($this->any())
                ->method('fetchByAssoc')
                ->will($this->onConsecutiveCalls($testRows[0], $testRows[1], $testRows[2]));

        $inboundEmailBean->db = $dbHandler;
        $this->object->setInboundEmailBean($inboundEmailBean);

        // setting up the email bean
        $emailBean = $this->getMockBuilder('Email')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('email2init'))
                ->getMock();
        $this->object->setEmailBean($emailBean);

        $expectedResult = array(
            array ('fullName' => 'Aldo Rayne', 'emailAddress' => 'arayne@example.com', 'id'=>null),
            array ('fullName' => 'Dominic Margarete', 'emailAddress' => 'dmargarete@example.com', 'id'=>'2'),
            array ('fullName' => 'Bridgitte Hammershmack', 'emailAddress' => 'bhammer@example.com', 'id'=>'3'),
        );

        $result = $this->object->retrieveEmails($sampleModule);
        $this->assertEquals($expectedResult, $result);

    }

    /**
     * @covers PMSECrmDataWrapper::retrieveDynaforms
     */
    public function testRetrieveDynaforms()
    {
        $this->projectBean->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue(true));

        $this->processBean->expects($this->any())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $this->dynaformBean->expects($this->any())
            ->method('get_full_list')
//            ->with($this->equalTo('bpm_dynamic_forms.pro_id=1'));
            ->will($this->returnValue(array(
                    (object) array("dyn_uid" => "abcdeff", "name" => "Test DynaForm"),
                    (object) array("dyn_uid" => "abcdefg", "name" => "Test DynaForm 01"),
                    (object) array("dyn_uid" => "abcdefh", "name" => "Test DynaForm 02"),
                    (object) array("dyn_uid" => "abcdefi", "name" => "Test DynaForm 03"),
                    (object) array("dyn_uid" => "abcdefj", "name" => "Test DynaForm 04"),
                )
            ));

        $expectedResult = array(
            array("value" => "abcdeff", "text" => "Test DynaForm"),
            array("value" => "abcdefg", "text" => "Test DynaForm 01"),
            array("value" => "abcdefh", "text" => "Test DynaForm 02"),
            array("value" => "abcdefi", "text" => "Test DynaForm 03"),
            array("value" => "abcdefj", "text" => "Test DynaForm 04"),
        );

        $this->projectBean->id = '1';
        $this->processBean->id = '1';

        $this->object->setDynaformBean($this->dynaformBean);
        $this->object->setProjectBean($this->projectBean);
        $this->object->setProcessBean($this->processBean);
        $someFilter = 'filter';
        $result = $this->object->retrieveDynaforms($someFilter);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveActivities
     */
    public function testRetrieveActivitiesEmptyFilter()
    {
        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $queryAndMock = $this->createPartialMock('queryAnd', array('addRaw'));

        $whereMock = $this->createPartialMock('where', array('queryAnd'));
        $whereMock->expects($this->any())
            ->method('queryAnd')
            ->will($this->returnValue($queryAndMock)
        );

        $this->sugarQueryMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(array(
                    array("act_uid" => "abcdeff", "name" => "Test Activity"),
                    array("act_uid" => "abcdefg", "name" => "Test Activity 01"),
                    array("act_uid" => "abcdefh", "name" => "Test Activity 02"),
                    array("act_uid" => "abcdefi", "name" => "Test Activity 03"),
                    array("act_uid" => "abcdefj", "name" => "Test Activity 04"),
                ))
            );

        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($whereMock)
        );

        $expectedResult = array(
            array("value" => "abcdeff", "text" => "Test Activity"),
            array("value" => "abcdefg", "text" => "Test Activity 01"),
            array("value" => "abcdefh", "text" => "Test Activity 02"),
            array("value" => "abcdefi", "text" => "Test Activity 03"),
            array("value" => "abcdefj", "text" => "Test Activity 04")
        );
        $activityBeanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        $this->object->setActivityBean($activityBeanMock);
        $result = $this->object->retrieveActivities();
        $this->assertEquals($expectedResult, $result);
    }

    public function testRetrieveActivitiesUserFilter()
    {

        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $this->sugarQueryMock = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(
                array('queryAnd', 'addRaw', 'select', 'from', 'where', 'execute', 'joinTable', 'on', 'equalsField')
            )
            ->getMock();

        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('where')
            ->willReturnSelf();

        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('queryAnd')
            ->willReturnSelf();

        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('addRaw')
            ->willReturnSelf();

        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('joinTable')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('on')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('equalsField')
            ->willReturnSelf();

        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $queryAndMock = $this->createPartialMock('queryAnd', array('addRaw'));

        $whereMock = $this->createPartialMock('where', array('queryAnd'));
        $whereMock->expects($this->any())
            ->method('queryAnd')
            ->will($this->returnValue($queryAndMock)
        );

        $this->sugarQueryMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(array(
                    array("act_uid" => "abcdeff", "name" => "Test Activity"),
                    array("act_uid" => "abcdefg", "name" => "Test Activity 01"),
                    array("act_uid" => "abcdefh", "name" => "Test Activity 02"),
                    array("act_uid" => "abcdefi", "name" => "Test Activity 03"),
                    array("act_uid" => "abcdefj", "name" => "Test Activity 04"),
                ))
            );

        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($whereMock)
        );

        $expectedResult = array(
            array("value" => "abcdeff", "text" => "Test Activity"),
            array("value" => "abcdefg", "text" => "Test Activity 01"),
            array("value" => "abcdefh", "text" => "Test Activity 02"),
            array("value" => "abcdefi", "text" => "Test Activity 03"),
            array("value" => "abcdefj", "text" => "Test Activity 04"),
        );
        $activityBeanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        $this->object->setActivityBean($activityBeanMock);
        $result = $this->object->retrieveActivities('user');
//        var_dump($result);
        $this->assertEquals($expectedResult, $result);
    }

    public function testRetrieveActivitiesScriptFilter()
    {

        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $this->sugarQueryMock = $this->getMockBuilder('SugarQuery')
                ->disableOriginalConstructor()
                ->setMethods(
                        array(
                            'queryAnd',
                            'addRaw',
                            'select',
                            'from',
                            'where',
                            'execute',
                            'joinTable',
                            'on',
                            'equalsField',
                        )
                    )
                ->getMock();

        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('where')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('queryAnd')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('addRaw')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('joinTable')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('on')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->atLeastOnce())
            ->method('equalsField')
            ->willReturnSelf();

        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $queryAndMock = $this->createPartialMock('queryAnd', array('addRaw'));

        $whereMock = $this->createPartialMock('where', array('queryAnd'));
        $whereMock->expects($this->any())
            ->method('queryAnd')
            ->will($this->returnValue($queryAndMock)
        );

        $this->sugarQueryMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(array(
                    array("act_uid" => "abcdeff", "name" => "Test Activity"),
                    array("act_uid" => "abcdefg", "name" => "Test Activity 01"),
                    array("act_uid" => "abcdefh", "name" => "Test Activity 02"),
                    array("act_uid" => "abcdefi", "name" => "Test Activity 03"),
                    array("act_uid" => "abcdefj", "name" => "Test Activity 04"),
                ))
            );

        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($whereMock)
        );

        $expectedResult = array(
            array("value" => "abcdeff", "text" => "Test Activity"),
            array("value" => "abcdefg", "text" => "Test Activity 01"),
            array("value" => "abcdefh", "text" => "Test Activity 02"),
            array("value" => "abcdefi", "text" => "Test Activity 03"),
            array("value" => "abcdefj", "text" => "Test Activity 04"),
        );
        $activityBeanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        $this->object->setActivityBean($activityBeanMock);
        $result = $this->object->retrieveActivities('script');
//        var_dump($result);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::_put
     * @todo   Implement test_put().
     */
    public function test_put()
    {

    }

    /**
     * @covers PMSECrmDataWrapper::_post
     * @todo   Implement test_post().
     */
    public function test_post()
    {

    }

    /**
     * @covers PMSECrmDataWrapper::_delete
     * @todo   Implement test_delete().
     */
    public function test_delete()
    {

    }

    /**
     * @covers PMSECrmDataWrapper::retrieveRuleSets
     */
    public function testRetrieveRuleSets()
    {
        $this->projectBean->expects($this->any())
            ->method('retrieve')
            ->will($this->returnValue(true));

        $this->processDefinitionBean->expects($this->any())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(
                array(
                    json_decode('{"pro_module":"leads"}')
                )
            ));

        $this->ruleSetBean->expects($this->any())
            ->method('get_full_list')
//            ->with($this->equalTo('Nombre'));
            ->will($this->returnValue(array(
                    (object) array("id" => "abcdeff", "name" => "Test RuleSet"),
                    (object) array("id" => "abcdefg", "name" => "Test RuleSet 01"),
                    (object) array("id" => "abcdefh", "name" => "Test RuleSet 02"),
                    (object) array("id" => "abcdefi", "name" => "Test RuleSet 03"),
                    (object) array("id" => "abcdefj", "name" => "Test RuleSet 04"),
                )
            ));

        $expectedResult = array(
            array("value" => "abcdeff", "text" => "Test RuleSet"),
            array("value" => "abcdefg", "text" => "Test RuleSet 01"),
            array("value" => "abcdefh", "text" => "Test RuleSet 02"),
            array("value" => "abcdefi", "text" => "Test RuleSet 03"),
            array("value" => "abcdefj", "text" => "Test RuleSet 04"),
        );


        $this->projectBean->id = '1';
        $this->processDefinitionBean->pro_module = 'Leads';

        $this->object->setBeanList($this->beanList);
        $this->object->setProcessDefinition($this->processDefinitionBean);
        $this->object->setProjectBean($this->projectBean);
        $this->object->setRuleSetBean($this->ruleSetBean);
        $result = $this->object->retrieveRuleSets('');
        $this->beanList = array('leads' => 'Leads');
        $this->object->setBeanList($this->beanList);
        $result = $this->object->retrieveRuleSets('');
//        var_dump($result);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveBusinessRules
     */
    public function testRetrieveBusinessRules()
    {
        $filter = '';
        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $queryAndMock = $this->createPartialMock('queryAnd', array('addRaw'));

        $whereMock = $this->createPartialMock('where', array('queryAnd'));
        $whereMock->expects($this->any())
            ->method('queryAnd')
            ->will($this->returnValue($queryAndMock)
        );

        $selectMock = $this->createPartialMock('select', array('fieldRaw'));
        $selectMock->expects($this->any())
            ->method('fieldRaw');

        $this->sugarQueryMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(array(
                    array("id" => "abcdeff", "name" => "Test Activity"),
                    array("id" => "abcdefg", "name" => "Test Activity 01"),
                    array("id" => "abcdefh", "name" => "Test Activity 02"),
                    array("id" => "abcdefi", "name" => "Test Activity 03"),
                    array("id" => "abcdefj", "name" => "Test Activity 04")
                ))
            );

        $this->sugarQueryMock->select = $selectMock;

        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($whereMock)
        );

        $expected = array(
            array("value" => "abcdeff", "text" => "Test Activity"),
            array("value" => "abcdefg", "text" => "Test Activity 01"),
            array("value" => "abcdefh", "text" => "Test Activity 02"),
            array("value" => "abcdefi", "text" => "Test Activity 03"),
            array("value" => "abcdefj", "text" => "Test Activity 04"),
        );

        $this->projectBean->expects($this->any())
            ->method('retrieve')
            ->with($filter)
            ->will($this->returnValue(true));

        $this->projectBean->id = '1';

        $this->object->setProcessDefinition($this->processDefinitionBean);
        $this->object->setActivityDefinitionBean($this->activityDefinitionBean);
        $this->object->setProjectBean($this->projectBean);
        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $result = $this->object->retrieveBusinessRules($filter);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveEmailTemplates
     * @todo   Implement testRetrieveEmailTemplates().
     */
    public function testRetrieveEmailTemplates()
    {

        $res= array(
            array("value" => "abcdeff", "text" => "Test Email Template"),
            array("value" => "abcdefg", "text" => "Test Email Template 01"),
            array("value" => "abcdefh", "text" => "Test Email Template 02"),
            array("value" => "abcdefi", "text" => "Test Email Template 03"),
            array("value" => "abcdefj", "text" => "Test Email Template 04"),
        );

        $emailBeanMock = $this->getMockBuilder('pmse_Emails_Templates')
                ->disableAutoload()
                ->setMethods(array('get_full_list'))
                ->getMock();
        $testModule = 'Leads';

        $emailBeanMock->expects($this->any())
            ->method('get_full_list')
            ->with('', "base_module = '$testModule'" )
            ->will($this->returnValue(array(
                    (object) array("id" => "abcdeff", "name" => "Test Email Template"),
                    (object) array("id" => "abcdefg", "name" => "Test Email Template 01"),
                    (object) array("id" => "abcdefh", "name" => "Test Email Template 02"),
                    (object) array("id" => "abcdefi", "name" => "Test Email Template 03"),
                    (object) array("id" => "abcdefj", "name" => "Test Email Template 04"),
                )
            ));
        $this->object->setEmailTemplateBean($emailBeanMock);
        $result = $this->object->retrieveEmailTemplates($testModule);
        $this->assertEquals($res, $result);
    }

    public function testRetrieveEmailTemplatesWithoutModule()
    {

        $emailBeanMock = $this->createPartialMock('pmse_Emails_Templates', array('get_full_list'));
        $testModule = null;

        $emailBeanMock->expects($this->any())
            ->method('get_full_list')
            ->with('', "base_module = '$testModule'" )
            ->will($this->returnValue(array(
                    (object) array("id" => "abcdeff", "name" => "Test Email Template"),
                    (object) array("id" => "abcdefg", "name" => "Test Email Template 01"),
                    (object) array("id" => "abcdefh", "name" => "Test Email Template 02"),
                    (object) array("id" => "abcdefi", "name" => "Test Email Template 03"),
                    (object) array("id" => "abcdefj", "name" => "Test Email Template 04"),
                )
            ));
        $this->object->setEmailTemplateBean($emailBeanMock);
        $result = $this->object->retrieveEmailTemplates($testModule);
        $this->assertEquals(array(), $result);
    }

    /**
     * @covers PMSECrmDataWrapper::getBeanModuleName
     */
    public function testGetBeanModuleName()
    {

        $this->object->setBeanList($this->beanList);
        $result = $this->object->getBeanModuleName('Account');
//        var_dump($result);
        $this->assertEquals('Accounts', $result);

        $this->beanList = array('Leads' => 'Leads');
        $this->object->setBeanList($this->beanList);
        $result = $this->object->getBeanModuleName('Leads');
//        var_dump($result);
        $this->assertEquals('Leads', $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateProjectName
     */
    public function testValidateProjectNameIfNull()
    {
        $this->projectBean->expects($this->any())
                ->method('retrieve_by_string_fields')
                ->will($this->returnValue(null));

        $this->object->setProjectBean($this->projectBean);

        $expected = array('result'=>true, 'success'=>true);
        $result = $this->object->validateProjectName('Test Lead');

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateProjectName
     */
    public function testValidateProjectName()
    {
        $someObject = new stdClass();

        $this->projectBean->expects($this->any())
                ->method('retrieve_by_string_fields')
                ->will($this->returnValue($someObject));

        $this->object->setProjectBean($this->projectBean);

        $expected = array('result'=>false, 'success'=>true, 'message' => 'LBL_PMSE_MESSAGE_THEPROCESSNAMEALREADYEXISTS');
        $result = $this->object->validateProjectName('Test Lead');

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateEmailTemplateName
     */
    public function testValidateEmailTemplateName()
    {
        $res = new stdClass();
        $res->success = true;
        $res->result = true;

        $this->object->setEmailTemplateBean($this->emailTemplateBean);

        $result = $this->object->validateEmailTemplateName('Test Lead', '1');
//        var_dump($result);
        $this->assertEquals($res, $result);

        $this->emailTemplateBean->expects($this->any())
            ->method('get_full_list')
            ->will($this->returnValue(
                array(
                    "rowList" => array(
                        array("pro_id" => "abcdeff", "pro_name" => "Test Project Name"),
                    ),
                    "totalRows" => 1,
                    "currentOffset" => 0
                )
            ));
        $res->message = 'LBL_PMSE_MESSAGE_THEEMAILTEMPLATENAMEALREADYEXISTS';
        $res->result = false;
        $result = $this->object->validateEmailTemplateName('Test Lead', '1');
//        var_dump($result);
        $this->assertEquals($res, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateBusinessRuleName
     */
    public function testValidateBusinessRuleName()
    {
        $res = array();
        $res['success'] = true;

        $this->ruleSetBean->expects($this->any())
            ->method('get_full_list')
            ->will($this->returnValue(
                array(
                    "rowList" => array(
                        array("pro_id" => "abcdeff", "pro_name" => "Test Project Name"),
                    ),
                    "totalRows" => 1,
                    "currentOffset" => 0
                )
            ));

        $this->object->setRuleSetBean($this->ruleSetBean);

        $res['message'] = 'LBL_PMSE_MESSAGE_BUSINESSRULENAMEALREADYEXISTS';
        $res['result'] = false;

        $result = $this->object->validateBusinessRuleName('Test Lead', '1');
//        var_dump($result);
        $this->assertEquals($res, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::defaultUsersList
     */
    public function testDefaultUsersList()
    {
        $expected = array(
            array ('value' => "current_user", 'text' =>"Current User"),
            array ('value' => "supervisor", 'text' => "Supervisor"),
            array ('value' => "owner", 'text' => "Record Owner"),
        );

        $result = $this->object->defaultUsersList();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::rolesList
     */
    public function testRolesList()
    {
        $expected = array(
            array('value' => 'is_admin', 'text' => translate('LBL_PMSE_FORM_OPTION_ADMINISTRATOR', 'pmse_Project')),
        );
        $result = $this->object->rolesList();
        $this->assertArraySubset($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveDateFields
     * @todo   Implement testRetrieveDateFields().
     */
    public function testRetrieveDateFields()
    {
        $res = array();
        $res['name'] = 'Leads';
        $res['search'] = 'Leads';
        $res['success']= true;
        $res['result'] = array(
            array('value' => 'current_date_time', 'text' => 'Current Date Time'),
            array('value' => 'field', 'text' => 'some_field')
        );

        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
                ->disableOriginalConstructor()
                ->setMethods(
                    array(
                        'getModuleFilter',
                        'getRelationshipData',
                    )
                )
                ->getMock();

        $moduleFilter = new stdClass();
        $moduleFilter->field_defs = array(
            array(
                'vname' => 'some_field',
                'name' => 'field',
                'type' => 'date'
            )
        );

        $this->object->expects($this->once())
                ->method('getModuleFilter')
                ->will($this->returnValue($moduleFilter));

        $this->object->setBeanList($this->beanList);
        $result = $this->object->retrieveDateFields('Leads');
//        var_dump($result);
        $this->assertEquals($res, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveDateFields
     * @todo   Implement testRetrieveDateFields().
     */
    public function testRetrieveDateFieldsRelatedBean()
    {
        $res = array();
        $res['name'] = 'Leads';
        $res['search'] = 'Leads';
        $res['success']= true;
        $res['result'] = array(
            array('value' => 'current_date_time', 'text' => 'Current Date Time'),
            array('value' => 'field', 'text' => 'some_field')
        );



        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
                ->disableOriginalConstructor()
                ->setMethods(
                    array(
                        'getModuleFilter',
                        'getRelationshipData',
                    )
                )
                ->getMock();

        $this->object->setBeanList(array(
            'Meetings' => 'Meetings'
        ));

        $moduleFilter = new stdClass();
        $moduleFilter->field_defs = array(
            array(
                'vname' => 'some_field',
                'name' => 'field',
                'type' => 'date'
            )
        );

        $this->object->expects($this->once())
                ->method('getModuleFilter')
                ->will($this->returnValue($moduleFilter));

        $relatioship = array('rhs_module' => 'Leads');

        $this->object->expects($this->once())
                ->method('getRelationshipData')
                ->will($this->returnValue($relatioship));

        $result = $this->object->retrieveDateFields('Leads');
//        var_dump($result);
        $this->assertEquals($res, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateReclaimCase
     */
    public function testValidateReclaimCase()
    {
        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $queryAndMock = $this->createPartialMock('queryAnd', array('addRaw'));

        $whereMock = $this->createPartialMock('where', array('queryAnd'));
        $whereMock->expects($this->any())
            ->method('queryAnd')
            ->will($this->returnValue($queryAndMock)
        );

        $this->sugarQueryMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(array(
                    array("cas_start_date" => "")
                ))
            );

        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($whereMock)
        );

        $casId = 1;
        $casIndex = 1;

        $this->object->setSugarQueryObject($this->sugarQueryMock);
        $inboxBeanMock = $this->getMockBuilder('SugarBean')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve'))
                ->getMock();
        $this->object->setInboxBean($inboxBeanMock);
        $result = $this->object->validateReclaimCase($casId, $casIndex);

        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
    }

    public function retrieveTeamsDataProvider()
    {
        return array(
            array('public'),
            array('private'),
        );
    }

    /**
     * @dataProvider retrieveTeamsDataProvider
     */
    public function testRetrieveTeams($filter)
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();
        $this->object->setSugarQueryObject($this->sugarQueryMock);
        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->any())
            ->method('equals')
            ->willReturnSelf();

        $teamMock = $this->getMockBuilder('Team')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->sugarQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(array(array('id' => 'team01', 'name' => 'Team #1'))));

        $this->object->setTeamsBean($teamMock);
        $result = $this->object->retrieveTeams($filter);
        $this->assertCount(1, $result);
    }

    public function testRetrieveUsers()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();

        $filter = 'Arm';

        $usersMock = $this->getMockBuilder('Users')
            ->disableOriginalConstructor()
            ->setMethods(array('get_full_list'))
            ->getMock();

        $usersMock->id = 'user01';
        $usersMock->first_name = 'Armin';
        $usersMock->last_name = 'Freulich';

        $usersMock->expects($this->once())
            ->method('get_full_list')
            ->will($this->returnValue(array($usersMock)));

        $teamsMock = $this->getMockBuilder('Teams')
            ->disableOriginalConstructor()
            ->setMethods(array('getDisplayName'))
            ->getMock();

        $teamsMock->expects($this->any())
                ->method('getDiplayName')
                ->will($this->returnValue('Armin Freulic'));

        $this->object->setUsersBean($usersMock);
        $this->object->setTeamsBean($teamsMock);
        $this->object->retrieveUsers($filter);
    }

    public function testGetTargetAndRelatedFieldsSuccess()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields','retrieveRelatedBeans'))
            ->getMock();

        $this->object->expects($this->at(0))
            ->method('retrieveFields')
            ->will($this->returnValue(
                array(
                    'success' => true,
                    'result' => array('fields')
                )
            ));

        $this->object->expects($this->once())
            ->method('retrieveRelatedBeans')
            ->will($this->returnValue(
                array(
                    'success' => true,
                    'result' => array(
                        array(
                            'value' => 'some value',
                            'fields' => array()
                        )
                    )
                )
            ));

        $this->object->expects($this->at(0))
            ->method('retrieveFields')
            ->will($this->returnValue(
                array(
                    'success' => true,
                    'result' => array('fields')
                )
            ));

        $filter = 'some filter';
        $result = $this->object->getTargetAndRelatedFields($filter);
        $this->assertInternalType('array', $result);
    }

    public function testGetTargetAndRelatedFieldsFailure()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields','retrieveRelatedBeans'))
            ->getMock();

        $this->object->expects($this->at(0))
            ->method('retrieveFields')
            ->will($this->returnValue(
                array(
                    'success' => false
                )
            ));

        $this->object->expects($this->once())
            ->method('retrieveRelatedBeans')
            ->will($this->returnValue(
                array(
                    'success' => false,
                )
            ));

        $this->object->expects($this->at(0))
            ->method('retrieveFields')
            ->will($this->returnValue(
                array(
                    'success' => true,
                    'result' => array('fields')
                )
            ));

        $filter = 'some filter';
        $result = $this->object->getTargetAndRelatedFields($filter);
        $this->assertInternalType('array', $result);
    }

    public function testGetAjaxRelationships()
    {
        $relationshipMock = $this->getMockBuilder('Relationship')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationshipList', 'get', 'getDefinition'))
            ->getMock();

        $relList = array(
            'leads_notes',
            'leads_meetings',
            'leads_tasks',
            'leads_opportunities',
            'leads_cases',
        );

        $relationshipMock->expects($this->once())
            ->method('getRelationshipList')
            ->will($this->returnValue($relList));

        $relationshipMock->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $def01 = array(
            'lhs_module' => 'leads',
            'rhs_module' => 'notes',
            'relationship_type' => 'one-to-one',
            'relationship_type_render' => '',
            'is_custom' => false,
            'from_studio' => false,
        );

        $relationshipMock->expects($this->at(2))
            ->method('getDefinition')
            ->will($this->returnValue($def01));

        $def02 = array(
            'lhs_module' => 'leads',
            'rhs_module' => 'meetings',
            'relationship_type' => 'one-to-many',
            'relationship_type_render' => '',
            'is_custom' => false,
            'from_studio' => false,
        );

        $relationshipMock->expects($this->at(4))
            ->method('getDefinition')
            ->will($this->returnValue($def02));

        $def03 = array(
            'lhs_module' => 'leads',
            'rhs_module' => 'tasks',
            'relationship_type' => 'many-to-one',
            'relationship_type_render' => '',
            'is_custom' => false,
            'from_studio' => false,
        );

        $relationshipMock->expects($this->at(6))
            ->method('getDefinition')
            ->will($this->returnValue($def03));

        $def04 = array(
            'lhs_module' => 'leads',
            'rhs_module' => 'opportunities',
            'relationship_type' => 'many-to-many',
            'relationship_type_render' => '',
            'is_custom' => true,
            'from_studio' => true,
        );

        $relationshipMock->expects($this->at(8))
            ->method('getDefinition')
            ->will($this->returnValue($def04));

        $def05 = array(
            'lhs_module' => 'leads',
            'rhs_module' => 'cases',
            'relationship_type' => 'another-type',
            'relationship_type_render' => '',
            'is_custom' => true,
            'from_studio' => true,
        );

        $relationshipMock->expects($this->at(10))
            ->method('getDefinition')
            ->will($this->returnValue($def05));

        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields','retrieveRelatedBeans'))
            ->getMock();

        $this->object->getAjaxRelationships($relationshipMock);
    }


    public function testRetrieveRelatedModulesFilterFound()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields', 'getModuleFilter', 'getAjaxRelationships'))
            ->getMock();

        $moduleObj = new stdClass();

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleObj));

        $ajaxRelationships = array(
            array(
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Leads',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-many',
                'relationship_name' => 'project_project_tasks',
                'name' => 'some relationship'
            ),
            array(
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Meetings',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-one',
                'relationship_name' => 'meetings_project_tasks',
                'name' => 'some relationship'
            ),
            array(
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Opportunities',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-one',
                'relationship_name' => 'opportunities_project_tasks',
                'name' => 'some relationship'
            )
        );

        $this->object->expects($this->once())
            ->method('getAjaxRelationships')
            ->will($this->returnValue($ajaxRelationships));

        $filter = 'ProjectTask';

        global $beanList;
        $beanList = array('Leads' => array(), 'ProjectTask' => array());

        $result = $this->object->retrieveRelatedModules($filter);
        $this->assertCount(4, $result);
    }

    public function testRetrieveRelatedModulesFilterNotFound()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields', 'getModuleFilter', 'getAjaxRelationships'))
            ->getMock();

        $moduleObj = new stdClass();

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleObj));

        $ajaxRelationships = array(
            array(
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Leads',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-many',
                'name' => 'some relationship'
            ),
            array(
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Meetings',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-one',
                'name' => 'some relationship'
            ),
            array(
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Opportunities',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-one',
                'name' => 'some relationship'
            )
        );

        $this->object->expects($this->once())
            ->method('getAjaxRelationships')
            ->will($this->returnValue($ajaxRelationships));

        $filter = 'ProjectTask';

        global $beanList;
        $beanList = array('Leads', 'ProjectTask');

        $result = $this->object->retrieveRelatedModules($filter);
        $this->assertCount(1, $result);
    }

    public function testUpdateProcessDefinitions()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('getProcessDefinition', 'getProjectBean', 'getProcessBean'))
            ->getMock();

        $this->object->setObservers(array());

        $dynaformMock = $this->getMockBuilder('PMSEDynaForm')
            ->disableOriginalConstructor()
            ->setMethods(array('generateDefaultDynaform'))
            ->getMock();

        $this->object->setDefaultDynaform($dynaformMock);

        $processDefMock = $this->getMockBuilder('pmse_BpmProcessDefinition')
                ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(array('save', 'retrieve_by_string_fields'))
            ->getMock();

        $processDefMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $processDefMock->id = 'pro01';
        $processDefMock->pro_module = 'Opportunities';
        $processDefMock->name = 'some name';

        $this->object->expects($this->once())
            ->method('getProcessDefinition')
            ->will($this->returnValue($processDefMock));

        $processMock = $this->getMockBuilder('pmse_bpmProcess')
                ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(array('save', 'retrieve_by_string_fields'))
            ->getMock();

        $processMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $processMock->id = 'pro01';
        $processMock->name = 'some name';

        $this->object->expects($this->once())
            ->method('getProcessBean')
            ->will($this->returnValue($processMock));

        $projectMock = $this->getMockBuilder('pmse_bpmProject')
                ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(array('save', 'retrieve'))
            ->getMock();

        $projectMock->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue(true));

        $projectMock->id = 'prj01';
        $projectMock->name = 'some name';

        $this->object->expects($this->once())
            ->method('getProjectBean')
            ->will($this->returnValue($projectMock));

        $args = array(
            'filter' => 'Leads',
            'name' => 'Some Name',
            'description' => 'Some Description',
            'pro_module' => 'Leads',
            'pro_locked_variables' => '[]',
            'pro_terminate_variables' => '[]'
        );
        $this->object->updateProcessDefinitions($args);
    }

    public function testClearAccordingProcessDefinitionsBusinessRule()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields'))
            ->getMock();

        global $db;

        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc', 'quoted'))
                ->getMock();

        $act01 = array(
            'id' => 'act01',
            'act_script_type' => 'BUSINESS_RULE'
        );

        $db->expects($this->at(5))
                ->method('fetchByAssoc')
                ->will($this->returnValue($act01));

        $db->expects($this->at(6))
                ->method('fetchByAssoc')
                ->will($this->returnValue(false));

        $act02 = array(
            'id' => 'act01',
            'act_script_type' => 'BUSINESS_RULE'
        );

        $db->expects($this->at(8))
                ->method('fetchByAssoc')
                ->will($this->returnValue($act02));
        $db->expects($this->at(9))
                ->method('fetchByAssoc')
                ->will($this->returnValue(false));

        $args = array(
            'pro_old_module' => 'Leads',
            'pro_new_module' => 'Leads',
            'pro_module' => 'Leads',
            'filter' => 'Leads'
        );

        $this->object->clearAccordingProcessDefinitions($args);
    }
    public function testClearAccordingProcessDefinitionsAssignTeam()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields'))
            ->getMock();

        global $db;

        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc', 'quoted'))
                ->getMock();

        $act01 = array(
            'id' => 'act01',
            'act_script_type' => 'ASSIGN_TEAM'
        );

        $db->expects($this->at(5))
                ->method('fetchByAssoc')
                ->will($this->returnValue($act01));

        $db->expects($this->at(6))
                ->method('fetchByAssoc')
                ->will($this->returnValue(false));

        $act02 = array(
            'id' => 'act01',
            'act_script_type' => 'ASSIGN_TEAM'
        );

        $db->expects($this->at(8))
                ->method('fetchByAssoc')
                ->will($this->returnValue($act02));
        $db->expects($this->at(9))
                ->method('fetchByAssoc')
                ->will($this->returnValue(false));

        $args = array(
            'pro_old_module' => 'Leads',
            'pro_new_module' => 'Leads',
            'pro_module' => 'Leads',
            'filter' => 'Leads'
        );

        $this->object->clearAccordingProcessDefinitions($args);
    }
    public function testClearAccordingProcessDefinitionsChangeField()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields'))
            ->getMock();

        $result = new stdClass();
        $result->result = array();

        $this->object->expects($this->any())
            ->method('retrieveFields')
            ->will($this->returnValue($result));

        global $db;

        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc', 'quoted'))
                ->getMock();

        $act01 = array(
            'id' => 'act01',
            'act_script_type' => 'CHANGE_FIELD'
        );

        $db->expects($this->at(5))
                ->method('fetchByAssoc')
                ->will($this->returnValue($act01));

        $db->expects($this->at(6))
                ->method('fetchByAssoc')
                ->will($this->returnValue(false));

        $act02 = array(
            'id' => 'act01',
            'act_script_type' => 'CHANGE_FIELD'
        );

        $db->expects($this->at(8))
                ->method('fetchByAssoc')
                ->will($this->returnValue($act02));
        $db->expects($this->at(9))
                ->method('fetchByAssoc')
                ->will($this->returnValue(false));

        $args = array(
            'pro_old_module' => 'Leads',
            'pro_new_module' => 'Leads',
            'pro_module' => 'Leads',
            'filter' => 'Leads'
        );

        $this->object->clearAccordingProcessDefinitions($args);
    }
    public function testClearAccordingProcessDefinitionsAssignUser()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields'))
            ->getMock();

        global $db;

        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc', 'quoted'))
                ->getMock();

        $act01 = array(
            'id' => 'act01',
            'act_script_type' => 'ASSIGN_USER'
        );

        $db->expects($this->at(5))
                ->method('fetchByAssoc')
                ->will($this->returnValue($act01));

        $db->expects($this->at(6))
                ->method('fetchByAssoc')
                ->will($this->returnValue(false));

        $act02 = array(
            'id' => 'act01',
            'act_script_type' => 'ASSIGN_USER'
        );

        $db->expects($this->at(8))
                ->method('fetchByAssoc')
                ->will($this->returnValue($act02));
        $db->expects($this->at(9))
                ->method('fetchByAssoc')
                ->will($this->returnValue(false));

        $args = array(
            'pro_old_module' => 'Leads',
            'pro_new_module' => 'Leads',
            'pro_module' => 'Leads',
            'filter' => 'Leads'
        );

        $this->object->clearAccordingProcessDefinitions($args);
    }

    /**
     *
     * @global type $db
     */
    public function testClearAccordingProcessDefinitionsThrowException()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields'))
            ->getMock();

        global $db;

        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc', 'quoted'))
                ->getMock();

        $exception = new Exception('Some Message');

        $db->expects($this->at(5))
                ->method('fetchByAssoc')
                ->will($this->throwException($exception));

        $args = array(
            'pro_old_module' => 'Leads',
            'pro_new_module' => 'Leads',
            'pro_module' => 'Leads',
            'filter' => 'Leads'
        );

        $result = $this->object->clearAccordingProcessDefinitions($args);

        $this->assertEquals('Some Message', $result->error);
    }

    public function testClearAccordingProcessDefinitionsEmptyModule()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveFields'))
            ->getMock();

        global $db;

        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc', 'quoted'))
                ->getMock();

        $args = array(
            'pro_old_module' => 'Leads',
            'filter' => 'Leads'
        );

        $this->object->clearAccordingProcessDefinitions($args);
    }

    public function testAddRelatedRecordWithoutFields()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'getRelationshipData',
                        'getModuleFilter',
                        'isValidStudioField',
                        'fieldTodo',
                        'returnArrayModules',
                        'dataFieldPersonalized',
                        'gatewayModulesMethod'
                    )
                )
            ->getMock();

        $this->object->expects($this->once())
            ->method('getRelationshipData')
            ->will($this->returnValue(array(
                'rhs_module' => 'All'
            )));

        global $beanList;
        $beanList = array();

        $this->object->addRelatedRecord("Leads");
    }

    public function testAddRelatedRecordWithFields()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'getRelationshipData',
                        'getModuleFilter',
                        'isValidStudioField',
                        'fieldTodo',
                        'returnArrayModules',
                        'dataFieldPersonalized',
                        'gatewayModulesMethod'
                    )
                )
            ->getMock();

        $moduleBeanMock = $this->getMockBuilder('ModuleBean')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();

        $moduleBeanMock->field_defs = array(
            array(
                'name' => 'id',
                'vname' => 'id',
                'type' => 'string',
                'required' => true,
                'len' => 32
            ),
            array(
                'name' => 'name',
                'vname' => 'name',
                'type' => 'string',
                'required' => true,
                'len' => 32
            ),
            array(
                'name' => 'list',
                'vname' => 'list',
                'type' => 'enum',
                'options' => 'listOptions',
                'required' => true,
                'len' => 32
            ),
            array(
                'name' => 'list2',
                'vname' => 'list2',
                'type' => 'enum',
                'required' => true,
                'len' => 32
            ),
        );

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleBeanMock));

        $this->object->expects($this->any())
            ->method('isValidStudioField')
            ->will($this->returnValue(true));

        $this->object->expects($this->any())
            ->method('fieldTodo')
            ->will($this->returnValue(false));

        global $beanList, $app_list_strings;
        $beanList = array('Notes' => array());
        $app_list_strings = array(
            'listOptions' => array()
        );

        $args = array(
            'retrieveId' => true
        );

        $this->object->addRelatedRecord("Notes", $args);
    }

    public function testAddRelatedRecordWithFieldDatePersonalized()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'getRelationshipData',
                        'getModuleFilter',
                        'isValidStudioField',
                        'fieldTodo',
                        'returnArrayModules',
                        'dataFieldPersonalized',
                        'gatewayModulesMethod'
                    )
                )
            ->getMock();

        $moduleBeanMock = $this->getMockBuilder('ModuleBean')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();

        $moduleBeanMock->field_defs = array(
            array(
                'name' => 'id',
                'vname' => 'id',
                'type' => 'string',
                'required' => true,
                'len' => 32
            ),
            array(
                'name' => 'name',
                'vname' => 'name',
                'type' => 'string',
                'required' => true,
                'len' => 32
            ),
            array(
                'name' => 'list',
                'vname' => 'list',
                'type' => 'enum',
                'options' => 'listOptions',
                'required' => true,
                'len' => 32
            ),
            array(
                'name' => 'list2',
                'vname' => 'list2',
                'type' => 'enum',
                'required' => true,
                'len' => 32
            ),
        );

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleBeanMock));

        $this->object->expects($this->any())
            ->method('dataFieldPersonalized')
            ->will($this->returnArgument(0));

        $this->object->expects($this->any())
            ->method('isValidStudioField')
            ->will($this->returnValue(true));

        $this->object->expects($this->any())
            ->method('fieldTodo')
            ->will($this->returnValue(false));

        global $beanList, $app_list_strings;
        $beanList = array('Notes' => array());
        $app_list_strings = array(
            'listOptions' => array()
        );

        $args = array(
            'retrieveId' => true
        );

        $this->object->addRelatedRecord("Notes", $args);
    }

    public function testGetRelatedSearchModules()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'retrieveModules',
                    )
                )
            ->getMock();

        $this->object->expects($this->once())
            ->method('retrieveModules')
            ->will($this->returnValue('SomeResponse'));

        $filter = 'modules';
        $args = array();

        $result = $this->object->getRelatedSearch($filter, $args);
        $this->assertEquals('SomeResponse', $result);
    }

    public function testGetRelatedSearchFields()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'retrieveModules',
                    )
                )
            ->getMock();

        $filter = 'fields';
        $args = array();

        $result = $this->object->getRelatedSearch($filter, $args);
        $this->assertEquals(array(), $result);
    }
}
