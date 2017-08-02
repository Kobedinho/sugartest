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
class PMSEEventTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var type 
     */
    protected $caseFlowHandlerMock;
    
    /**
     *
     * @var type 
     */
    protected $flowMock;
    
    /**
     *
     * @var type 
     */
    protected $caseFlowMock;
    
    /**
     *
     * @var type 
     */
    protected $gatewayMock;
    
    /**
     * @var PMSEElement
     */
    protected $event;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->flowMock = $this->getMockBuilder('pmse_BpmnFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('get_list', 'retrieve_by_string_fields'))
                ->getMock();
        
        $this->caseFlowMock = $this->getMockBuilder('pmse_BpmFlow')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('get_list', 'retrieve_by_string_fields'))
                ->getMock();

        $this->caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('closeThreadByThreadIndex', 'retrieveBean', 'closeFlow'))
                ->getMock();
        
        $this->gatewayMock = $this->getMockBuilder('pmse_BpmnGateway')
                ->disableAutoload()
                ->setMethods(array('retrieve_by_string_fields', 'get_list'))
                ->getMock();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    
    public function testCheckIfUsesAnEventBasedGatewayIfFound()
    {
        $this->event = $this->getMockBuilder('PMSEEvent')
            ->setMethods(array('retrieveBean'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $beanCase = new stdClass();
        $beanCase->cas_id = 1;
        $beanCase->cas_index = 1;
        $beanCase->bpmn_type = 'bpmnFlow';
        $beanCase->bpmn_id = 'j89s239s823d';

        $resultCaseMock = array(
            'list' => array(
                $beanCase
            )
        );

        $this->caseFlowMock->expects($this->any())
                ->method('get_list')
                ->will($this->returnValue($resultCaseMock));

        $flowBean = new stdClass();
        $flowBean->flo_element_origin_type = 'bpmnGateway';
        $flowBean->flo_element_origin = 'abc890';
        $resultFlowMock = array(
            'list' => array(
                $flowBean
            )
        );

        $this->flowMock->expects($this->any())
                ->method('get_list')
                ->will($this->returnValue($resultFlowMock));

        
        $gatewayBean = new stdClass();
        $gatewayBean->gat_type = 'EVENTBASED';
        
        $this->caseFlowHandlerMock->expects($this->at(0))
                ->method('retrieveBean')
                ->with('pmse_BpmFlow')
                ->will($this->returnValue($this->caseFlowMock));

        $this->caseFlowHandlerMock->expects($this->at(1))
                ->method('retrieveBean')
                ->with('pmse_BpmnFlow')
                ->will($this->returnValue($this->flowMock));        

        $this->caseFlowHandlerMock->expects($this->at(2))
                ->method('retrieveBean')
                ->with('pmse_BpmnGateway', 'abc890')
                ->will($this->returnValue($gatewayBean));

        $this->event->setCaseFlowHandler($this->caseFlowHandlerMock);

        $casID = 1;
        $casIndexPrevious = 1;
        
        $result = $this->event->checkIfUsesAnEventBasedGateway($casID, $casIndexPrevious);
        $expected = true;

        $this->assertEquals($expected, $result);
    }
    
    public function testCheckIfUsesAnEventBasedGatewayIfNotFound()
    {
        $this->event = $this->getMockBuilder('PMSEEvent')
            ->setMethods(array('retrieveBean'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $beanCase = new stdClass();
        $beanCase->cas_id = 1;
        $beanCase->cas_index = 1;
        $beanCase->bpmn_type = 'bpmnFlow';
        $beanCase->bpmn_id = 'j89s239s823d';

        $resultCaseMock = array(
            'list' => array(
                $beanCase
            )
        );

        $this->caseFlowMock->expects($this->any())
                ->method('get_list')
                ->will($this->returnValue($resultCaseMock));

        $flowBean = new stdClass();
        $flowBean->flo_element_origin_type = 'bpmnGateway';
        $flowBean->flo_element_origin = 'abc890';
        $resultFlowMock = array(
            'list' => array(
                $flowBean
            )
        );

        $this->flowMock->expects($this->any())
                ->method('get_list')
                ->will($this->returnValue($resultFlowMock));

        
        $gatewayBean = new stdClass();
        $gatewayBean->gat_type = 'EXCLUSIVE';
        
        $this->caseFlowHandlerMock->expects($this->at(0))
                ->method('retrieveBean')
                ->with('pmse_BpmFlow')
                ->will($this->returnValue($this->caseFlowMock));

        $this->caseFlowHandlerMock->expects($this->at(1))
                ->method('retrieveBean')
                ->with('pmse_BpmnFlow')
                ->will($this->returnValue($this->flowMock));        

        $this->caseFlowHandlerMock->expects($this->at(2))
                ->method('retrieveBean')
                ->with('pmse_BpmnGateway', 'abc890')
                ->will($this->returnValue($gatewayBean));

        $this->event->setCaseFlowHandler($this->caseFlowHandlerMock);

        $casID = 1;
        $casIndexPrevious = 1;
        
        $result = $this->event->checkIfUsesAnEventBasedGateway($casID, $casIndexPrevious);
        $expected = false;

        $this->assertEquals($expected, $result);
    }
//    
    public function testCheckIfExistEventBased()
    {

        $this->event = $this->getMockBuilder('PMSEEvent')
            ->setMethods(NULL)
            ->disableOriginalConstructor()
            ->getMock();
        
        $beanCase = new stdClass();
        $beanCase->cas_id = 1;
        $beanCase->cas_index = 1;
        $beanCase->cas_previous = 0;
        $beanCase->bpmn_type = 'bpmnFlow';
        $beanCase->bpmn_id = 'j89s239s823d';

        $resultCaseMock = array(
            'list' => array(
                $beanCase
            )
        );

        $this->caseFlowMock->expects($this->atLeastOnce())
                ->method('get_list')
                ->will($this->returnValue($resultCaseMock));
        
        $this->caseFlowMock->expects($this->atLeastOnce())
                ->method('retrieve_by_string_fields')
                ->will($this->returnValue($beanCase));
        
        $this->caseFlowMock->cas_id = 1;
        $this->caseFlowMock->cas_index = 1;
        $this->caseFlowMock->cas_previous = 0;
        $this->caseFlowMock->bpmn_type = 'bpmnFlow';
        $this->caseFlowMock->bpmn_id = 'j89s239s823d';
        
        $this->caseFlowHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($this->caseFlowMock));
        
        $dbHandlerMock = $this->getMockBuilder('DBHandler')
            ->setMethods(array('Query', 'fetchByAssoc'))
            ->getMock();
        
        $dbHandlerMock->expects($this->at(0))
            ->method('Query')            
            ->will($this->returnValue(array()));
        
        $rowThread = array(
            'cas_index' => 1,
            'cas_thread_index' => 2,
            'cas_thread_parent' => 1,
            'cas_flow_index' => 1
        );

        $dbHandlerMock->expects($this->at(1))
            ->method('fetchByAssoc')
            ->with(array())
            ->will($this->returnValue($rowThread));

        $dbHandlerMock->expects($this->at(2))
            ->method('Query')
            ->will($this->returnValue(array()));
        
        $dbHandlerMock->expects($this->at(3))
            ->method('fetchByAssoc')
            ->with(array())
            ->will($this->returnValue(array()));
            
        $dbHandlerMock->expects($this->at(4))
            ->method('Query')
            ->will($this->returnValue(array()));
        
        $row = array(
            'cas_index' => 2,
            'cas_thread_index' => 2,
            'cas_thread_parent' => 1,
            'cas_flow_index' => 1
        );
        
        $dbHandlerMock->expects($this->at(5))
            ->method('fetchByAssoc')
            ->with(array())
            ->will($this->returnValue($row));
        
        $this->caseFlowHandlerMock->expects($this->exactly(2))
            ->method('closeThreadByThreadIndex');
                
        $this->event->setCaseFlowHandler($this->caseFlowHandlerMock);
        $this->event->setDbHandler($dbHandlerMock);
        
        $casId = 1;
        $casIndex = 5;
        $isEventBased = true;
        
        $expected = true;
        $result = $this->event->checkIfExistEventBased($casId, $casIndex, $isEventBased);
        $this->assertEquals ($expected, $result);        
    }
    
}
