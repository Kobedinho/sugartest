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
class PMSEEndEventTest extends PHPUnit_Framework_TestCase {

    /**
     * @var PMSEElement
     */
    protected $endEvent;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * Test the end event run method if there are no open threads so the action
     * should be to close (complete) the process.
     */
    public function testRunCloseCase()
    {
        $this->endEvent = $this->getMockBuilder('PMSEEndEvent')
            ->setMethods(array('countNumberOpenThreads'))
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('closeThreadByCaseIndex', 'closeCase'))
            ->getMock();

        $count = 1;
       
        $this->endEvent->expects($this->once())
            ->method('countNumberOpenThreads')
            ->will($this->returnValue($count));
        
        $caseFlowHandlerMock->expects($this->once())
            ->method('closeCase');
        
        $bean = new stdClass();

        $this->endEvent->setCaseFlowHandler($caseFlowHandlerMock);

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 1
        );

        $this->endEvent->run($flowData, $bean, '');
    }
    
    /**
     * Test the end event run method if there is at least one open thread so 
     * the action should be to close the current flow and thread.
     */
    public function testRunCloseThread()
    {
        $this->endEvent = $this->getMockBuilder('PMSEEndEvent')
            ->setMethods(array('countNumberOpenThreads'))
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('closeThreadByCaseIndex', 'closeCase'))
            ->getMock();

        $count = 2;
       
        $this->endEvent->expects($this->once())
            ->method('countNumberOpenThreads')
            ->will($this->returnValue($count));
        
        $caseFlowHandlerMock->expects($this->once())
            ->method('closeThreadByCaseIndex');
        
        $bean = new stdClass();

        $this->endEvent->setCaseFlowHandler($caseFlowHandlerMock);

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 1
        );

        $this->endEvent->run($flowData, $bean, '');
    }
    
    /**
     * Test the method that counts the number of open threads.
     */
//    public function testCountNumberOpenThreads()
//    {
//        $this->endEvent = $this->getMockBuilder('PMSEEndEvent')
//            ->setMethods(NULL)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $sugarBeanMock = $this->getMockBuilder('SugarBean')
//            ->disableOriginalConstructor()
//            ->setMethods(NULL)
//            ->getMock();
//
//        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
//            ->setMethods(array('retrieveSugarQueryObject', 'retrieveBean'))
//            ->getMock();
//        $this->endEvent->setCaseFlowHandler($caseFlowHandlerMock);
//
//        $caseFlowHandlerMock->expects($this->exactly(1))
//            ->method('retrieveBean')
//            ->will($this->returnValue($sugarBeanMock));
//
//        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
//            ->disableOriginalConstructor()
//            ->setMethods(array('from','select','fieldRaw','where','equals','execute'))
//            ->getMock();
//
//        $sugarQueryMock->expects($this->once())
//            ->method('from')
//            ->will($this->returnValue($sugarQueryMock));
//        $sugarQueryMock->expects($this->once())
//            ->method('select')
//            ->will($this->returnValue($sugarQueryMock));
//        $sugarQueryMock->expects($this->once())
//            ->method('fieldRaw')
//            ->will($this->returnValue($sugarQueryMock));
//        $sugarQueryMock->expects($this->exactly(2))
//            ->method('where')
//            ->will($this->returnValue($sugarQueryMock));
//        $sugarQueryMock->expects($this->exactly(2))
//            ->method('equals')
//            ->will($this->returnValue($sugarQueryMock));
//
//        $resultArray = array(
//            array('open'=>1)
//        );
//
//        $sugarQueryMock->expects($this->once())
//            ->method('execute')
//            ->will($this->returnValue($resultArray));
//
//        $caseFlowHandlerMock->expects($this->once())
//            ->method('retrieveSugarQueryObject')
//            ->will($this->returnValue($sugarQueryMock));
//
//        $flowData = array(
//            'cas_id' => 1,
//            'cas_index' => 2,
//            'cas_previous' => 1
//        );
//
//        $result = $this->endEvent->countNumberOpenThreads($flowData);
//        $this->assertEquals(1, $result);
//    }
}
