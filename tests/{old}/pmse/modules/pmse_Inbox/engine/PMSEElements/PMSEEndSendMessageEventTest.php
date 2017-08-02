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
class PMSEEndSendMessageEventTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PMSEElement
     */
    protected $endSendMessageEvent;

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
        $this->endSendMessageEvent = $this->getMockBuilder('PMSEEndSendMessageEvent')
            ->setMethods(array('prepareResponse', 'closeCase', 'retrieveDefinitionData', 'countNumberOpenThreads'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->endSendMessageEvent->expects($this->once())
            ->method('countNumberOpenThreads')
            ->will($this->returnValue(2));

        $emailHandler = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('processEmailsFromJson', 'sendTemplateEmail'))
            ->getMock();
        
        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('closeThreadByCaseIndex', 'closeCase'))
            ->getMock();

        $emailHandler->expects($this->once())
            ->method('processEmailsFromJson');

        $emailHandler->expects($this->once())
            ->method('sendTemplateEmail');

        $bean = new stdClass();
        $this->endSendMessageEvent->setCaseFlowHandler($caseFlowHandlerMock);
        $this->endSendMessageEvent->setEmailHandler($emailHandler);

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 1,
            'bpmn_id' => 'deuh823dj23',
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'ajsdioajodisa2'
        );

        $this->endSendMessageEvent->run($flowData, $bean, '');
    }
}
 