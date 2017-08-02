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

require_once 'tests/{old}/SugarTestDatabaseMock.php';
require_once 'tests/{old}/SugarTestReflection.php';

class SugarApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $mock;

    static public $monitorList;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$monitorList = TrackerManager::getInstance()->getDisabledMonitors();

        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        ApiHelper::$moduleHelpers = array();
        TrackerManager::getInstance()->setDisabledMonitors(self::$monitorList);

        $_FILES = array();
        unset($_SERVER['CONTENT_LENGTH']);

        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function setUp() {
        $this->mock = new SugarApiMock();
        $this->contact = SugarTestContactUtilities::createContact();
        // We can override the module helpers with mocks.
        ApiHelper::$moduleHelpers = array();
    }

    public function tearDown() {
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testLoadBeanById_BeanExists_Success() {
        $this->mock = new SugarApiMock();

        $args = array(
            'module' => 'Contacts',
            'record' => $this->contact->id
        );

       $api = new SugarApiTestServiceMock();
       $bean=$this->mock->callLoadBean($api, $args);

       $this->assertTrue($bean instanceof Contact);
       $this->assertEquals($this->contact->id, $bean->id, "Unexpected Contact Loaded");
    }

    public function testLoadBeanById_BeanNotExists_NotFound() {
        $this->mock = new SugarApiMock();

        $args = array(
            'module' => 'Contacts',
            'record' => '12345'
        );

        $api = new SugarApiTestServiceMock();
        $this->setExpectedException('SugarApiExceptionNotFound');
        $bean=$this->mock->callLoadBean($api, $args);
    }

    public function testLoadBean_CreateTempBean_Success() {
        $this->mock = new SugarApiMock();

        $args = array( /* Note: No "record" element */
            'module' => 'Contacts',
        );

        $api = new SugarApiTestServiceMock();
        $this->setExpectedException('SugarApiExceptionMissingParameter');
        $bean=$this->mock->callLoadBean($api, $args);
    }

    public function testFormatBeanCallsTrackView()
    {
        $this->markTestIncomplete("SugarApi needs a user to pass along to other objects, and user is not getting passed along. Sending to FRM for fix.");
        
        if ( !SugarTestReflection::isSupported() ) {
            $this->markTestSkipped("Need a newer version of PHP, 5.3.2 is the minimum for this test");
        }


        $apiMock = $this->createPartialMock('SugarApi', array('htmlDecodeReturn', 'trackAction'));
        $apiMock->expects($this->any())
                ->method('htmlDecodeReturn');

        $apiMock->expects($this->once())
                ->method('trackAction');

        $fakeBean = $this->createMock('SugarBean');
        $fakeBean->id = 'abcd';
        $fakeBean->module_dir = 'fakeBean';

        $apiMock->api = $this->createMock('RestService');

        $helperMock = $this->getMockBuilder('SugarBeanApiHelper')
            ->setMethods(array('formatForApi'))
            ->setConstructorArgs(array($apiMock->api));
        $helperMock->expects($this->any())
                   ->method('formatForApi')
                   ->will($this->returnValue(
                       array('never gonna'=>
                             array('give you up',
                                   'let you down',
                                   'run around',
                                   'desert you'))));
        ApiHelper::$moduleHelpers['fakeBean'] = $helperMock;

        // Call it once when it should track the view
        SugarTestReflection::callProtectedMethod($apiMock,'formatBean',array($apiMock->api,array('viewed'=>true), $fakeBean));

        // And once when it shouldn't
        SugarTestReflection::callProtectedMethod($apiMock,'formatBean',array($apiMock->api,array(), $fakeBean));

        // No asserts, they are handled by the mock's ->expects()
    }

    /*
     * @covers SugarApi::trackAction
     */
    public function testTrackAction()
    {
        $monitorMock = $this->getMockBuilder('Monitor')
            ->disableOriginalConstructor()
            ->getMock(array('setValue'));
        $monitorMock
            ->expects($this->any())
            ->method('setValue');

        $managerMock = $this->getMockBuilder('TrackerManager')
            ->disableOriginalConstructor()
            ->getMock(array('getMonitor','saveMonitor'));
        $managerMock
            ->expects($this->once())
            ->method('saveMonitor');
        
        $sugarApi = $this->createPartialMock('SugarApi', array('getTrackerManager'));
        $sugarApi
            ->expects($this->any())
            ->method('getTrackerManager')
            ->will($this->returnValue($managerMock));
        
        $sugarApi->api = $this->createMock('RestService');
        $sugarApi->api->user = $this->createPartialMock('User', array('getPrivateTeamID'));
        $sugarApi->api->user
            ->expects($this->any())
            ->method('getPrivateTeamID')
            ->will($this->returnValue('1'));
        $fakeBean = $this->createPartialMock('SugarBean', array('get_summary_text'));
        $fakeBean->id = 'abcd';
        $fakeBean->module_dir = 'fakeBean';
        $fakeBean->expects($this->any())
            ->method('get_summary_text')
            ->will($this->returnValue('Rickroll'));
        
        
        $sugarApi->action = 'unittest';
        
        // Emulate the tracker being disabled, then enabled
        $managerMock
            ->expects($this->any())
            ->method('getMonitor')
            ->will($this->onConsecutiveCalls(null,$monitorMock,$monitorMock,$monitorMock,$monitorMock));
        
        $sugarApi->trackAction($fakeBean);

        // This one should actually save
        $sugarApi->trackAction($fakeBean);

        // Try it again, but this time with a new bean with id
        $fakeBean->new_with_id = true;
        $sugarApi->trackAction($fakeBean);

        // And one last time but this time with an empty bean id
        unset($fakeBean->new_with_id);
        unset($fakeBean->id);
        $sugarApi->trackAction($fakeBean);

        // No asserts, handled through the saveMonitor ->once() expectation above
    }

    /**
     * @dataProvider lotsOData
     */
    public function testHtmlEntityDecode($array, $expected, $message)
    {
        $this->mock->htmlEntityDecodeStuff($array);
        $this->assertSame($array, $expected, $message);
    }

    public function lotsOData()
    {
        return array(
            array(array("bool" => true), array("bool" => true), "True came out wrong"),
            array(array("bool" => false), array("bool" => false), "False came out wrong"),
            array(array("string" => 'Test'), array("string" => 'Test'), "String came out wrong"),
            array(array("number" => 12345), array("number" => 12345), "Number came out wrong"),
            array(
                array("html" => htmlentities("I'll \"walk\" the <b>dog</b> now")),
                array("html" => "I'll \"walk\" the <b>dog</b> now"),
                "HTML came out wrong"
            ),
            array(
                array("html" => array("nested_result" => array("data" => "def &lt; abc &gt; xyz"))),
                array("html" => array("nested_result" => array("data" => "def < abc > xyz"))),
                "HTML came out wrong"
            ),
        );
    }

    /**
     * @dataProvider checkPostRequestBodyProvider
     */
    public function testCheckPostRequestBody($contentLength, $postMaxSize, $expectedException)
    {
        $api = $this->getMockBuilder('SugarApi')
            ->setMethods(array('getPostMaxSize'))
            ->getMock();
        $api->expects($this->any())
            ->method('getPostMaxSize')
            ->will($this->returnValue($postMaxSize));

        $_FILES = array();
        $_SERVER['CONTENT_LENGTH'] = $contentLength;
        $this->setExpectedException($expectedException);
        SugarTestReflection::callProtectedMethod($api, 'checkPostRequestBody');
    }

    public static function checkPostRequestBodyProvider()
    {
        return array(
            array(null, null, 'SugarApiExceptionMissingParameter'),
            array(1024, 1023, 'SugarApiExceptionRequestTooLarge'),
        );
    }

    /**
     * @dataProvider checkPutRequestBodyProvider
     */
    public function testCheckPutRequestBody($length, $contentLength, $expectedException)
    {
        $api = $this->getMockForAbstractClass('SugarApi');

        $_SERVER['CONTENT_LENGTH'] = $contentLength;
        $this->setExpectedException($expectedException);
        SugarTestReflection::callProtectedMethod($api, 'checkPutRequestBody', array($length));
    }

    public static function checkPutRequestBodyProvider()
    {
        return array(
            array(0, null, 'SugarApiExceptionMissingParameter'),
            array(1023, 1024, 'SugarApiExceptionRequestTooLarge'),
        );
    }

    /**
     * @dataProvider providerTestGetFieldsFromArgs
     * @covers SugarApi::getFieldsFromArgs
     * @group unit
     */
    public function testGetFieldsFromArgs($module, $fieldDefs, $fieldList, $args, $view, $expected)
    {
        if ($module) {
            $seed = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->getMock();
            $seed->module_name = $module;
            $seed->field_defs = $fieldDefs;
        } else {
            $seed = null;
        }

        $service = new SugarApiTestServiceMock();

        $sugarApi = $this->getMockBuilder('SugarApiMock')
            ->setMethods(array('getMetaDataManager'))
            ->getMock();

        $mm = $this->getMockBuilder('MetaDataManager')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $mm->expects($this->any())
            ->method('getModuleViewFields')
            ->will($this->returnValue($fieldList));

        $sugarApi->expects($this->any())
            ->method('getMetaDataManager')
            ->will($this->returnValue($mm));

        $this->assertEquals(
            $expected,
            $sugarApi->getFieldsFromArgs($service, $args, $seed, $view)
        );
    }

    public function providerTestGetFieldsFromArgs()
    {
        return array(

            // fields argument only
            array(
                'Accounts',
                array(), // field defs
                array(), // view def
                array(   // arguments
                    'fields' => 'name,website',
                ),
                'view',  // view
                array(   // expected
                    'name',
                    'website',
                ),
            ),

            // view argument only
            array(
                'Accounts',
                array(), // field defs
                array( // view def
                    'name',
                    'website',
                ),
                array( // arguments
                    'xxx' => 'record',
                ),
                'xxx', // view
                array( // expected
                    'name',
                    'website',
                ),
            ),

            // fields/view argument merge
            array(
                'Accounts',
                array(), // field defs
                array( // view def
                    'phone',
                    'fax',
                ),
                array( // arguments
                    'fields' => 'name,website',
                    'view' => 'record',
                ),
                'view', // view
                array(  // expected
                    'name',
                    'website',
                    'phone',
                    'fax',
                ),
            ),

            // nothing ...
            array(
                'Accounts',
                array(), // field defs
                array(), // view def
                array(), // arguments
                null,    // view
                array(), // expected
            ),

            // fields/view with invalid module
            array(
                null,
                array(), // field defs
                array( // view def
                    'bogus',
                ),
                array(   // arguments
                    'fields' => 'name,website',
                    'view' => 'record',
                ),
                'view', // view
                array(  // expected
                    'name',
                    'website',
                ),
            ),

            // relate and parent field
            array(
                'Accounts',
                array(  // field defs
                    'case_name' => array(
                        'name' => 'case_name',
                        'type' => 'relate',
                        'id_name' => 'case_id',
                    ),
                    'parent_name' => array(
                        'name' => 'parent_name',
                        'type' => 'parent',
                        'id_name' => 'parent_id',
                        'type_name' => 'parent_type',
                    ),
                    'website' => array(
                        'name' => 'website',
                        'type' => 'varchar',
                    ),
                ),
                array(  // view def
                    'name',
                    'case_name',
                    'parent_name',
                    'website',
                ),
                array(  // arguments
                    'view' => 'record',
                    'fields' => 'phone,fax',
                ),
                'view', // view
                array(  // expected
                    'phone',
                    'fax',
                    'name',
                    'case_name',
                    'parent_name',
                    'website',
                    'case_id',
                    'parent_id',
                    'parent_type',
                ),
            ),
            // url field
            array(
                'Leads',
                array(  // field defs
                    'name' => array(
                        'name' => 'name',
                        'type' => 'fullname',
                    ),
                    'url_c' => array(
                        'name' => 'url_c',
                        'type' => 'url',
                        'default' => 'test/{name}'
                    ),
                ),
                array(), // view def
                array( // arguments
                    'fields' => 'my_favorite,converted,url_c',
                    'view' => 'list',
                ),
                'view', // view
                array(  // expected
                    'my_favorite',
                    'converted',
                    'url_c',
                    'name',
                ),
            ),

        );
    }

    /**
     * @dataProvider getOrderByFromArgsSuccessProvider
     */
    public function testGetOrderByFromArgsSuccess(array $args, array $expected)
    {
        $actual = $this->getOrderByFromArgs($args);
        $this->assertEquals($expected, $actual);
    }

    public static function getOrderByFromArgsSuccessProvider()
    {
        return array(
            'not-specified' => array(
                array(),
                array(),
            ),
            'specified' => array(
                array(
                    'order_by' => 'a:asc,b:desc,c,d:whatever',
                ),
                array(
                    'a' => true,
                    'b' => false,
                    'c' => true,
                    'd' => true,
                ),
            ),
        );
    }

    /**
     * @dataProvider getOrderByFromArgsFailureProvider
     */
    public function testGetOrderByFromArgsFailure(array $args, $expectedException)
    {
        /** @var SugarBean|PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $bean->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(false));
        $bean->field_defs = array('name' => array());

        $this->setExpectedException($expectedException);
        $this->getOrderByFromArgs($args, $bean);
    }

    public static function getOrderByFromArgsFailureProvider()
    {
        return array(
            'field-not-found' => array(
                array(
                    'order_by' => 'not-existing-field',
                ),
                'SugarApiExceptionInvalidParameter',
            ),
            'field-no-access' => array(
                array(
                    'order_by' => 'name',
                ),
                'SugarApiExceptionNotAuthorized',
            ),
        );
    }

    private function getOrderByFromArgs(array $args, SugarBean $bean = null)
    {
        $api = $this->getMockForAbstractClass('SugarApi');
        return SugarTestReflection::callProtectedMethod($api, 'getOrderByFromArgs', array($args, $bean));
    }

    /**
     * @dataProvider normalizeFieldsSuccessProvider
     */
    public function testNormalizeFieldsSuccess($input, $expectedFields, $expectedDisplayParams)
    {
        $fields = $this->normalizeFields($input, $displayParams);
        $this->assertEquals($expectedFields, $fields);
        $this->assertEquals($expectedDisplayParams, $displayParams);
    }

    public static function normalizeFieldsSuccessProvider()
    {
        return array(
            'from-string' => array(
                'id,name',
                array('id', 'name'),
                array(),
            ),
            'from-array' => array(
                array('first_name', 'last_name'),
                array('first_name', 'last_name'),
                array(),
            ),
            'from-string-with-display-params' => array(
                'id,{"name":"opportunities","fields":["id","name","sales_status"],"order_by":"date_closed:desc"}',
                array('id', 'opportunities'),
                array(
                    'opportunities' => array(
                        'fields' => array('id', 'name', 'sales_status'),
                        'order_by' => 'date_closed:desc',
                    ),
                ),
            ),
            'from-array-with-display-params' => array(
                array(
                    'id', array(
                        'name' => 'contacts',
                        'fields' => array('first_name', 'last_name'),
                        'order_by' => 'last_name',
                    ),
                ),
                array('id', 'contacts'),
                array(
                    'contacts' => array(
                        'fields' => array('first_name', 'last_name'),
                        'order_by' => 'last_name',
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider normalizeFieldsFailureProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testNormalizeFieldsFailure($fields)
    {
        $this->normalizeFields($fields, $displayParams);
    }

    public static function normalizeFieldsFailureProvider()
    {
        return array(
            'non-array-or-string' => array(false),
            'name-not-specified' => array(
                array(
                    array('order_by' => 'name'),
                ),
            ),
        );
    }

    private function normalizeFields($fields, &$displayParams)
    {
        $api = $this->getMockForAbstractClass('SugarApi');
        return SugarTestReflection::callProtectedMethod($api, 'normalizeFields', array($fields, &$displayParams));
    }

    /**
     * @dataProvider parseFieldsSuccessProvider
     */
    public function testParseFieldsSuccess($fields, array $expected)
    {
        $actual = $this->parseFields($fields);
        $this->assertEquals($expected, $actual);
    }

    public static function parseFieldsSuccessProvider()
    {
        return array(
            'normal' => array(
                'name,{"name":"opportunities","fields":["id","name","sales_status"]}',
                array(
                    'name',
                    array(
                        'name' => 'opportunities',
                        'fields' => array('id', 'name', 'sales_status'),
                    ),
                ),
            ),
            'whitespaces' => array(
                'id , name',
                array('id', 'name'),
            ),
        );
    }

    /**
     * @dataProvider parseFieldsFailureProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testParseFieldsFailure($fields)
    {
        $this->parseFields($fields);
    }

    public static function parseFieldsFailureProvider()
    {
        return array(
            'invalid-json' => array(
                '{"name":',
            ),
        );
    }

    private function parseFields($fields)
    {
        $api = $this->getMockForAbstractClass('SugarApi');
        return SugarTestReflection::callProtectedMethod($api, 'parseFields', array($fields));
    }
}


// need to make sure ServiceBase is included when extending it to avoid a fatal error

class SugarApiMock extends SugarApi
{
    public function htmlEntityDecodeStuff(&$data)
    {
        return parent::htmlDecodeReturn($data);
    }

    public function callLoadBean(ServiceBase $api, $args)
    {
        return parent::loadBean($api, $args);
    }

    public function getFieldsFromArgs(
        ServiceBase $api,
        array $args,
        SugarBean $bean = null,
        $viewName = 'view',
        &$displayParams = array()
    ) {
        return parent::getFieldsFromArgs($api, $args, $bean, $viewName, $displayParams);
    }
}

class SugarApiTestServiceMock extends ServiceBase
{
    public function execute() {}

    protected function handleException(Exception $exception) {}
}
