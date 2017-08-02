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
class PMSEConvergingExclusiveGatewayTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PMSEElement
     */
    protected $convergingExclusiveGateway;

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
    
    public function testRunWithNoPreviousFlows()
    {
        $this->convergingExclusiveGateway = $this->getMockBuilder('PMSEConvergingExclusiveGateway')
            ->setMethods(array('retrievePreviousFlows', 'prepareResponse'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->convergingExclusiveGateway->expects($this->exactly(1))
            ->method('retrievePreviousFlows')
            ->will($this->returnValue(array()));
        
        $flowData = array('bpmn_id' => '9872398ue23', 'cas_id' => 1);
        $bean = null;
        
        $this->convergingExclusiveGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'WAIT', 'NONE', array());
        
        $this->convergingExclusiveGateway->run($flowData, $bean);
    }

    public function testRun()
    {
        $this->convergingExclusiveGateway = $this->getMockBuilder('PMSEConvergingExclusiveGateway')
            ->setMethods(array('retrievePreviousFlows', 'prepareResponse'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $previousFlows = array(
            array('id' => '1234567'),
        );
        
        $flowData = array('bpmn_id' => '9872398ue23', 'cas_id' => 1);
        $bean = null;
        
        $this->convergingExclusiveGateway->expects($this->exactly(1))
            ->method('retrievePreviousFlows')
            ->will($this->returnValue($previousFlows));
        
        $this->convergingExclusiveGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'ROUTE', 'CREATE', array());
        
        $this->convergingExclusiveGateway->run($flowData, $bean);
    }
    //put your code here
}
