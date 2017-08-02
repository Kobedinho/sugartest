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
class PMSETerminateEventTest extends PHPUnit_Framework_TestCase {
    /**
     * @var PMSEElement
     */
    protected $roundRobin;

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
     *
     */
    public function testRun()
    {
        $this->endEvent = $this->getMockBuilder('PMSETerminateEvent')
            ->setMethods(array('prepareResponse', 'closeCase'))
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('closeThreadByCaseIndex', 'closeCase', 'terminateCaseFlow', 'setCloseStatusForThisThread', 'closeThreadByThreadIndex'))
            ->getMock();


        $dbMock = $this->getMockBuilder('DBHandler')
            ->setMethods(array('Query', 'fetchByAssoc'))
            ->getMock();

        $dbMock->expects($this->exactly(1))
            ->method('Query')
            ->will($this->returnValue(array()));

        $dbMock->expects($this->at(1))
            ->method('fetchByAssoc')
            ->with(array())
            ->will($this->returnValue(array('cas_thread_index' => 1)));

        $bean = new stdClass();
        $bean->db = $dbMock;

        $this->endEvent->setCaseFlowHandler($caseFlowHandlerMock);

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2
        );

        $this->endEvent->run($flowData, $bean, '');
    }
}
 