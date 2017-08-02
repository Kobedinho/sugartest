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
class PMSEConvergingParallelGatewayTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PMSEElement
     */
    protected $convergingParallelGateway;

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
    
    public function testRunIncompleteFlows()
    {
        $this->convergingParallelGateway = $this->getMockBuilder('PMSEConvergingParallelGateway')
            ->setMethods(array('retrievePreviousFlows', 'prepareResponse'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $passedFlows = array(
            array('id' => '1234567')
        );
        
        $allFlows = array(
            array('id' => '1234567'),
            array('id' => '3982749'),
            array('id' => '0987654')
        );
        
        $this->convergingParallelGateway->expects($this->at(0))
            ->method('retrievePreviousFlows')
            ->will($this->returnValue($passedFlows));
        
        $this->convergingParallelGateway->expects($this->at(1))
            ->method('retrievePreviousFlows')
            ->will($this->returnValue($allFlows));
        
        $flowData = array('bpmn_id' => '9872398ue23', 'cas_id'=>1);
        $bean = null;
        
        $this->convergingParallelGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'WAIT', 'NONE', array());

        $this->convergingParallelGateway->run($flowData, $bean);
    }

    public function testRunCompletedFlows()
    {
        $this->convergingParallelGateway = $this->getMockBuilder('PMSEConvergingParallelGateway')
            ->setMethods(array('retrievePreviousFlows', 'prepareResponse'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $passedFlows = array(
            array('id' => '1234567'),
            array('id' => '3982749'),
            array('id' => '0987654')
        );
        
        $allFlows = array(
            array('id' => '1234567'),
            array('id' => '3982749'),
            array('id' => '0987654')
        );
        
        $this->convergingParallelGateway->expects($this->at(0))
            ->method('retrievePreviousFlows')
            ->will($this->returnValue($passedFlows));
        
        $this->convergingParallelGateway->expects($this->at(1))
            ->method('retrievePreviousFlows')
            ->will($this->returnValue($allFlows));
        
        $flowData = array('bpmn_id' => '9872398ue23', 'cas_id'=>1);
        $bean = null;                
        
        $this->convergingParallelGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'ROUTE', 'CREATE', array());
        
        $this->convergingParallelGateway->run($flowData, $bean);
    }

}
