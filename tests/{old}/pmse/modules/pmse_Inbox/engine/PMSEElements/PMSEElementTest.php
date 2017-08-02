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
class PMSEElementTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PMSEElement
     */
    protected $pmseElement;

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
    
    public function testGetNextShapeElements()
    {
        $this->pmseElement = $this->getMockBuilder('PMSEElement')            
            ->setMethods(array('retrieveSugarQueryObject'))
            ->disableOriginalConstructor()
            ->getMock();

        $sugarBeanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();
        
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('select', 'from', 'where', 'joinTable', 'on', 'equalsField', 'execute'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $whereMock = $this->getMockBuilder('WhereObject')
            ->setMethods(array('queryAnd'))
            ->getMock();
        
        $queryAndMock = $this->getMockBuilder('QueryAnd')
            ->setMethods(array('addRaw'))
            ->getMock();

        $whereMock->expects($this->exactly(1))
            ->method('queryAnd')
            ->will($this->returnValue($queryAndMock));
                
        
        $sugarQuery->expects($this->exactly(1))
            ->method('where')
            ->will($this->returnValue($whereMock));
        $sugarQuery->expects($this->any())
            ->method('joinTable')
            ->willReturnSelf();
        $sugarQuery->expects($this->any())
            ->method('on')
            ->willReturnSelf();

        $sugarQuery->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue(array(
                array('key' => 'element_content')
            )));
        
        $this->pmseElement->expects($this->exactly(1))
            ->method('retrieveSugarQueryObject')
            ->will($this->returnValue($sugarQuery));
        
        $flowData =array(
            array('flo_element_dest' => 1),
            array('flo_element_dest' => 2),
            array('flo_element_dest' => 3)
        );

        $caseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('retrieveFollowingElements', 'retrieveBean'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $caseFlowHandler->expects($this->exactly(1))
            ->method('retrieveBean')
            ->will($this->returnValue($sugarBeanMock));
        
        $followingElements = array(
            array('bpmn_id' => '12345')
        );
        
        $caseFlowHandler->expects($this->once())
            ->method('retrieveFollowingElements')
            ->will($this->returnValue($followingElements));
        
        $this->pmseElement->setCaseFlowHandler($caseFlowHandler);
        
        $elements = $this->pmseElement->getNextShapeElements($flowData);
        
        $this->assertInternalType('array', $elements);
        
    }
    //put your code here
}
