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
class PMSECaseFlowHandlerTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected $originals = array();

    public function setUp() {
        parent::setUp();
        $this->originals['current_user'] = $GLOBALS['current_user'];
        $this->originals['db'] = $GLOBALS['db'];
    }

    public function tearDown() {
        foreach($this->originals as $varname => $value) {
            $GLOBALS[$varname] = $value;
        }
        parent::tearDown();
    }

    /**
     *
     *
     */
    public function testRetrieveFlowData()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveSugarQueryObject'))
                ->getMock();

        $flowMock = $this->getMockBuilder('SugarBean')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();

        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
                ->disableOriginalConstructor()
                ->setMethods(array('select', 'from', 'where', 'queryAnd', 'addRaw', 'execute'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveSugarQueryObject')
                ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->once())
                ->method('where')
                ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->once())
                ->method('queryAnd')
                ->will($this->returnValue($sugarQueryMock));

        $expectedArray = array(
            'result01',
            'result02'
        );

        $sugarQueryMock->expects($this->once())
                ->method('execute')
                ->will($this->returnValue($expectedArray));

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2
        );

        $caseFlowHandlerMock->setBpmFlow($flowMock);
        $result = $caseFlowHandlerMock->retrieveFlowData($flowData);

        $this->assertEquals($expectedArray[0], $result);
    }

    public function testRetrieveMaxIndex()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveSugarQueryObject'))
                ->getMock();

        $flowMock = $this->getMockBuilder('SugarBean')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();

        $db = new SugarTestDatabaseMock();
        $caseFlowHandlerMock->setDb($db);
        $db->addQuerySpy('max_index','/MAX.*max_index.*FROM/i', array(array('max_index' => 6)));

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2
        );

        $caseFlowHandlerMock->setBpmFlow($flowMock);
        $result = $caseFlowHandlerMock->retrieveMaxIndex($flowData);
        $this->assertEquals(6, $result);
        $this->assertEquals(1, $db->getQuerySpyRunCount('max_index'));
    }

    public function testRetrieveMaxIndexWithoutCases()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveSugarQueryObject'))
                ->getMock();

        $flowMock = $this->getMockBuilder('SugarBean')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();

        $db = new SugarTestDatabaseMock();
        $caseFlowHandlerMock->setDb($db);
        $db->addQuerySpy('max_index','/MAX.*max_index.*FROM/i', array());

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2
        );
        $caseFlowHandlerMock->setBpmFlow($flowMock);

        $result = $caseFlowHandlerMock->retrieveMaxIndex($flowData);
        $this->assertEquals(1, $result);
        $this->assertEquals(1, $db->getQuerySpyRunCount('max_index'));
    }

    public function testRetrieveMaxIndexEmptyFlowData()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveSugarQueryObject'))
                ->getMock();

        $flowData = array();

        $result = $caseFlowHandlerMock->retrieveMaxIndex($flowData);
        $this->assertEquals(0, $result);
    }

    public function testRetrieveFollowingElementsIfIsFlow()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'retrieveSugarQueryObject'))
                ->getMock();

        $flowMock = $this->getMockBuilder('SugarBean')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();

        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
                ->disableOriginalConstructor()
                ->setMethods(array('select', 'from', 'where', 'queryAnd', 'addRaw', 'execute'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveSugarQueryObject')
                ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->once())
                ->method('where')
                ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->once())
                ->method('queryAnd')
                ->will($this->returnValue($sugarQueryMock));

        $expectedArray = array(
            array('bpmn_id' => 'abc123', 'bpmn_type' => 'BpmnFlow')
        );

        $sugarQueryMock->expects($this->once())
                ->method('execute')
                ->will($this->returnValue($expectedArray));

        $flowData = array(
            'id' => 'abc123',
            'bpmn_type' => 'bpmnFlow',
            'bpmn_id' => 'asdf'
        );

        $caseFlowHandlerMock->expects($this->exactly(1))
                ->method('retrieveBean')
                ->will($this->returnValue($flowMock));
        $result = $caseFlowHandlerMock->retrieveFollowingElements($flowData);
        $this->assertInternalType('array', $result);
    }

    public function testRetrieveFollowingElementsIfIsNotFlow()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'retrieveSugarQueryObject'))
                ->getMock();

        $flowMock = $this->getMockBuilder('SugarBean')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();

        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
                ->disableOriginalConstructor()
                ->setMethods(array('select', 'from', 'where', 'queryAnd', 'addRaw', 'execute'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveSugarQueryObject')
                ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->once())
                ->method('where')
                ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->once())
                ->method('queryAnd')
                ->will($this->returnValue($sugarQueryMock));

        $expectedArray = array(
            array('bpmn_id' => 'abc123', 'bpmn_type' => 'BpmnActivity')
        );

        $sugarQueryMock->expects($this->once())
                ->method('execute')
                ->will($this->returnValue($expectedArray));

        $flowData = array(
            'id' => 'abc123',
            'bpmn_type' => 'BpmnActivity',
            'bpmn_id' => 'asdf'
        );

        $caseFlowHandlerMock->expects($this->exactly(1))
                ->method('retrieveBean')
                ->will($this->returnValue($flowMock));

        $result = $caseFlowHandlerMock->retrieveFollowingElements($flowData);
        $this->assertInternalType('array', $result);
    }

    public function testRetrieveData()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveFlowData', 'retrieveElementByType'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveFlowData')
                ->will($this->returnValue('Some flow data'));

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveElementByType')
                ->will($this->returnValue('Some element type'));

        $casId = 1;
        $casIndex = 1;
        $casThread = 1;

        $result = $caseFlowHandlerMock->retrieveData($casId, $casIndex, $casThread);
        $this->assertEquals('Some flow data', $result['flow_data']);
        $this->assertEquals('Some element type', $result['pmse_element']);
    }

    public function testPrepareFlowData()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveMaxIndex', 'processFlowData'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveMaxIndex');

        $processedData = 'Processed Data';

        $caseFlowHandlerMock->expects($this->once())
                ->method('processFlowData')
                ->will($this->returnValue($processedData));

        $flowData = array('cas_index' => 1);

        $result = $caseFlowHandlerMock->prepareFlowData($flowData);
        $this->assertEquals('Processed Data', $result);
    }

    public function testSaveFlowData()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('createThread', 'retrieveBean'))
                ->getMock();

        $flowBeanMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save', 'toArray'))
                ->getMock();

        $flowBeanMock->new_with_id = true;
        $flowBeanMock->cas_id = '';
        $flowBeanMock->cas_index = '';
        $flowBeanMock->bpmn_type = '';
        $flowBeanMock->bpmn_id = '';

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveBean')
                ->will($this->returnValue($flowBeanMock));

        $flowBeanMock->expects($this->once())
                ->method('toArray')
                ->will($this->returnValue('toArrayData'));

        $flowData = array(
            'id' => 'abc123',
            'cas_id' => 1,
            'cas_index' => 2,
            'bpmn_type' => 'BpmnActivity',
            'bpmn_id' => 'abc123'
        );

        $result = $caseFlowHandlerMock->saveFlowData($flowData);
        $this->assertEquals($result, 'toArrayData');
    }

    public function testSaveFlowDataWithThread()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('createThread', 'retrieveBean'))
                ->getMock();

        $flowBeanMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save', 'toArray'))
                ->getMock();

        $flowBeanMock->new_with_id = true;
        $flowBeanMock->cas_id = '';
        $flowBeanMock->cas_index = '';
        $flowBeanMock->bpmn_type = '';
        $flowBeanMock->bpmn_id = '';

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveBean')
                ->will($this->returnValue($flowBeanMock));

        $flowBeanMock->expects($this->once())
                ->method('toArray')
                ->will($this->returnValue('toArrayData'));

        $flowData = array(
            'id' => 'abc123',
            'cas_id' => 1,
            'cas_index' => 2,
            'bpmn_type' => 'BpmnActivity',
            'bpmn_id' => 'abc123'
        );

        $result = $caseFlowHandlerMock->saveFlowData($flowData, true, 'abc123');
        $this->assertEquals($result, 'toArrayData');
    }

    public function testProcessFlowData()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('createThread', 'retrieveBean'))
                ->getMock();

        $flowData = array(
            'id' => 'flo123',
            'cas_id' => 1,
            'max_index' => 2,
            'cas_current_index' => 3,
            'pro_id' => 'pro123',
            'bpmn_id' => 'act123',
            'bpmn_type' => 'BpmnActivity',
            'cas_user_id' => 'usr123',
            'cas_thread' => 1,
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'lead01',
            'rel_process_module' => 'Leads',
            'rel_element_relationship' => 'leads_notes',
            'rel_element_module' => 'Notes',
            'evn_criteria' => "{::notes::id::}=='SomeId'",
        );

        $result = $caseFlowHandlerMock->processFlowData($flowData);
        $this->assertTrue(!empty($result));
        $this->assertInternalType('array', $result);
    }

    public function testCreateThread()
    {
        global $db;
        $db = $this->getMockBuilder('DBHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('Query', 'fetchByAssoc'))
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'retrieveSugarQueryObject'))
                ->getMock();

        $threadMock = $this->getMockBuilder('SugarBean')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->atLeastOnce())
                ->method('retrieveBean')
                ->will($this->returnValue($threadMock));

        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
                ->disableOriginalConstructor()
                ->setMethods(array('select', 'from', 'where', 'queryAnd', 'addRaw', 'execute', 'equals'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->atLeastOnce())
                ->method('retrieveSugarQueryObject')
                ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->atLeastOnce())
                ->method('where')
                ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->atLeastOnce())
            ->method('equals')
            ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->atLeastOnce())
                ->method('queryAnd')
                ->will($this->returnValue($sugarQueryMock));

        $rowList = array(
            array('cas_thread_index' => 1, 'id' => 'abc001'),
            array('cas_thread_index' => 2, 'id' => 'abc002'),
            array('cas_thread_index' => 3, 'id' => 'abc003'),
            array('cas_thread_index' => 4, 'id' => 'abc004'),
            array('cas_thread_index' => 5, 'id' => 'abc005')
        );

        $sugarQueryMock->expects($this->atLeastOnce())
                ->method('execute')
                ->will($this->returnValue($rowList));

        $flowData = array('id' => 'abc0123', 'cas_id' => 1, 'cas_index' => 2, 'cas_thread' => 1);

        $caseFlowHandlerMock->createThread($flowData);
    }

    public function testClosePreviousFlow()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('closeFlow'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->once())
                ->method('closeFlow');

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2
        );

        $caseFlowHandlerMock->closePreviousFlow($flowData);
    }

    public function testCloseFlow()
    {
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'getBpmFlow', 'handleTerminatedFlowRelatedBeans'))
                ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('save', 'retrieve_by_string_fields'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->any())
                ->method('retrieveBean')
                ->with($this->equalTo('pmse_BpmFlow'))
                ->will($this->returnValue($flowMock));

        $caseFlowHandlerMock->expects($this->any())
                ->method('getBpmFlow')
                ->will($this->returnValue($flowMock));

        $caseFlowHandlerMock->expects($this->any())
                ->method('handleTerminatedFlowRelatedBeans')
                ->will($this->returnValue(true));

        $flowMock->expects($this->once())
                ->method('retrieve_by_string_fields');

        $flowMock->expects($this->once())
                ->method('save');

        $casId = 1;
        $casIndex = 2;

        $caseFlowHandlerMock->closeFlow($casId, $casIndex);
    }

    public function testCloseThreadByThreadIndex()
    {
        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('from', 'where', 'equals', 'execute'))
            ->getMock();

        $sugarQueryMock->expects($this->atLeastOnce())
            ->method('from')
            ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->atLeastOnce())
            ->method('where')
            ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->atLeastOnce())
            ->method('equals')
            ->will($this->returnValue($sugarQueryMock));

        $threadMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveBean', 'retrieveSugarQueryObject'))
            ->getMock();

        $caseFlowHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveSugarQueryObject')
            ->will($this->returnValue($sugarQueryMock));

        $caseFlowHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($threadMock));

        $casId = 1;
        $casThreadIndex = 2;

        $caseFlowHandlerMock->closeThreadByThreadIndex($casId, $casThreadIndex);
    }

    public function testCloseThreadByThreadIndexInexistent()
    {
        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('from', 'where', 'equals', 'execute'))
            ->getMock();

        $sugarQueryMock->expects($this->atLeastOnce())
            ->method('from')
            ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->atLeastOnce())
            ->method('where')
            ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->atLeastOnce())
            ->method('equals')
            ->will($this->returnValue($sugarQueryMock));

        $threadMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveBean', 'retrieveSugarQueryObject'))
            ->getMock();

        $caseFlowHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveSugarQueryObject')
            ->will($this->returnValue($sugarQueryMock));

        $caseFlowHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($threadMock));

        $casId = 1;
        $casThreadIndex = 2;

        $caseFlowHandlerMock->closeThreadByThreadIndex($casId, $casThreadIndex);
    }

    public function testCloseThreadByCaseIndex()
    {
        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc'))
                ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean'))
                ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields'))
                ->getMock();

        $flowMock->cas_thread = 1;

        $caseFlowHandlerMock->expects($this->once())
                ->method('retrieveBean')
                ->will($this->returnValue($flowMock));

        $casId = 1;
        $casIndex = 2;

        $caseFlowHandlerMock->closeThreadByCaseIndex($casId, $casIndex);
    }

    public function testCloseCase()
    {
        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('query', 'fetchByAssoc'))
                ->getMock();

        $db->expects($this->once())
                ->method('query');

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'getDb'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->any())
            ->method('getDb')
            ->will($this->returnValue($db));

        $casId = 1;
        $caseFlowHandlerMock->closeCase($casId);
    }

    public function testTerminateCaseFlow()
    {
        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc', 'quoted'))
                ->getMock();

        $db->expects($this->once())
                ->method('Query');

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->getMock();
        $flowMock->table_name = 'foo';

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'getDb', 'getBpmFlow', 'handleTerminatedFlowRelatedBeans'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->any())
                ->method('handleTerminatedFlowRelatedBeans')
                ->will($this->returnValue(true));

        $caseFlowHandlerMock->expects($this->any())
            ->method('getDb')
            ->will($this->returnValue($db));

        $caseFlowHandlerMock->expects($this->any())
                ->method('retrieveBean')
                ->with($this->equalTo('pmse_BpmFlow'))
                ->will($this->returnValue($flowMock));

        $caseFlowHandlerMock->expects($this->any())
                ->method('getBpmFlow')
                ->will($this->returnValue($flowMock));

        $casId = 1;
        $caseFlowHandlerMock->terminateCaseFlow($casId);
    }

    public function testSetCloseStatusForThisThread()
    {
        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc', 'quoted'))
                ->getMock();

        $db->expects($this->once())
                ->method('Query');

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->getMock();
        $flowMock->table_name = 'foo';

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'getDb', 'getBpmFlow', 'handleTerminatedFlowRelatedBeans'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->any())
            ->method('getDb')
            ->will($this->returnValue($db));

        $caseFlowHandlerMock->expects($this->any())
                ->method('handleTerminatedFlowRelatedBeans')
                ->will($this->returnValue(true));

        $caseFlowHandlerMock->expects($this->any())
                ->method('retrieveBean')
                ->with($this->equalTo('pmse_BpmFlow'))
                ->will($this->returnValue($flowMock));

        $caseFlowHandlerMock->expects($this->any())
                ->method('getBpmFlow')
                ->will($this->returnValue($flowMock));

        $casId = 1;
        $casThreadIndex = 1;
        $caseFlowHandlerMock->setCloseStatusForThisThread($casId, $casThreadIndex);
    }

    public function testSaveFormActionIfNotPreviousAction()
    {
        global $current_user;
        $current_user = new stdClass();
        $current_user->id = 'usr123';
        $current_user->user_name = 'admin';

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean'))
                ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields'))
                ->getMock();
        $flowMock->bpmn_id = 'flo123';
        $flowMock->pro_id = 'pro123';

        $caseFlowHandlerMock->expects($this->at(0))
                ->method('retrieveBean')
                ->will($this->returnValue($flowMock));

        $noteMock = $this->getMockBuilder('pmse_BpmNotes')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'save'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->at(1))
                ->method('retrieveBean')
                ->will($this->returnValue($noteMock));

        $formActionMock = $this->getMockBuilder('pmse_BpmFormAction')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'save'))
                ->getMock();

        $formActionMock->frm_action = '';

        $caseFlowHandlerMock->expects($this->at(2))
                ->method('retrieveBean')
                ->will($this->returnValue($formActionMock));

        $previousFormActionMock = $this->getMockBuilder('pmse_BpmFormAction')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'save'))
                ->getMock();

        $previousFormActionMock->frm_action = '';


        $caseFlowHandlerMock->expects($this->at(3))
                ->method('retrieveBean')
                ->will($this->returnValue($previousFormActionMock));

        $params = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'frm_action' => 'ROUTE',
            'not_type' => 'ELEMENT',
            'not_user_recipient_id' => 'usr980',
            'frm_comment' => 'some comment',
        );

        $caseFlowHandlerMock->saveFormAction($params);
    }

    public function testSaveFormActionIfPreviousActionExists()
    {
        global $current_user;
        $current_user = new stdClass();
        $current_user->id = 'usr123';
        $current_user->user_name = 'admin';

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean'))
                ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields'))
                ->getMock();
        $flowMock->bpmn_id = 'flo123';
        $flowMock->pro_id = 'pro123';

        $caseFlowHandlerMock->expects($this->at(0))
                ->method('retrieveBean')
                ->will($this->returnValue($flowMock));

        $noteMock = $this->getMockBuilder('pmse_BpmNotes')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'save'))
                ->getMock();

        $caseFlowHandlerMock->expects($this->at(1))
                ->method('retrieveBean')
                ->will($this->returnValue($noteMock));

        $formActionMock = $this->getMockBuilder('pmse_BpmFormAction')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'save'))
                ->getMock();

        $formActionMock->frm_action = '';

        $caseFlowHandlerMock->expects($this->at(2))
                ->method('retrieveBean')
                ->will($this->returnValue($formActionMock));

        $previousFormActionMock = $this->getMockBuilder('pmse_BpmFormAction')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields', 'save'))
                ->getMock();

        $previousFormActionMock->frm_action = '';
        $previousFormActionMock->frm_index = 2;
        $previousFormActionMock->fetched_row = array('frm_action' => 'ACCEPT', 'frm_index'=>2);


        $caseFlowHandlerMock->expects($this->at(3))
                ->method('retrieveBean')
                ->will($this->returnValue($previousFormActionMock));

        $params = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'frm_action' => 'ROUTE',
            'not_type' => 'ELEMENT',
            'not_user_recipient_id' => 'usr980',
            'frm_comment' => 'some comment',
        );

        $caseFlowHandlerMock->saveFormAction($params);
    }

}
