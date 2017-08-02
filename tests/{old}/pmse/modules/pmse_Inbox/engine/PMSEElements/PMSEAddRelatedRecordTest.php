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
class PMSEAddRelatedRecordTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PMSEElement
     */
    protected $addRelatedRecord;
    protected $loggerMock;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'debug', 'warning'))
            ->getMock();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }
    
    /**
     * 
     */
    public function testRunFixedDate()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('save'))
            ->getMock();
        $beanMock->module_name = 'Calls';
        $beanMock->id = '8an9n0r2jd9j923cm89kyk32tb2in83';
        $beanMock->db = new stdClass();
        $beanMock->description = 'Some description';
        $beanMock->field_defs = array(
            'description' => array()
        );

//        Queue of fields to be added
        $assignedField = new stdClass();
        $assignedField->field = 'assigned_user_id';
        $assignedField->type = 'user';
        $assignedField->value = '1';

        $dateField = new stdClass();
        $dateField->field = 'birthdate';
        $dateField->type = 'Date';
        $dateField->value[0] = array('expType' => 'CONSTANT', 'expSubtype' => 'date', 'expValue' => '2015-12-06');

        $lastNameField = new stdClass();
        $lastNameField->field = 'last_name';
        $lastNameField->type = 'TextField';
        $lastNameField->value = 'New Contact';

//        Process definition
        $definitionMock = array(
            'id' => 'q2389djq9238jd93489234df9g5k',
            'pro_id' => 'sami89w93fm9w38fw',
            'act_field_module' => 'contacts',
            'pro_module' => 'Calls',
            'act_fields' => json_encode(array($lastNameField, $assignedField, $dateField))
        );

//        Process Flow
        $flowData = array(
            'bpmn_id' => 'o1289d89823dj23d892',
            'cas_id' => 1,
            'cas_index' => 2,
            'id' => '9238d3d234udj89234jd'
        );
        $this->addRelatedRecord = $this->getMockBuilder('PMSEAddRelatedRecord')
            ->setMethods(array('retrieveDefinitionData', 'retrieveHistoryData','getCustomUser'))
            ->getMock();

        $this->addRelatedRecord->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveBean'))
            ->getMock();

        $caseFlowHandlerMock->expects($this->at(0))
            ->method('retrieveBean')
            ->with('pmse_BpmActivityDefinition')
            ->will($this->returnValue((object) $definitionMock));

        $caseFlowHandlerMock->expects($this->at(1))
            ->method('retrieveBean')
            ->with('pmse_BpmProcessDefinition')
            ->will($this->returnValue((object) $definitionMock));

        $beanHandler = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationshipData', 'getCustomUser', 'calculateDueDate', 'processValueExpression', 'mergeBeanInTemplate'))
            ->getMock();

        $this->addRelatedRecord->expects($this->once())
            ->method('getCustomUser')
            ->will ($this->returnValue("1"));

        $beanHandler->expects($this->at(0))
            ->method('mergeBeanInTemplate')
            ->with($beanMock, "New Contact")
            ->will ($this->returnValue("New Contact"));

        $beanHandler->expects($this->at(2))
            ->method('mergeBeanInTemplate')
            ->with($beanMock, "1")
            ->will ($this->returnValue("1"));

        $beanHandler->expects($this->any())
            ->method('processValueExpression')
            ->will ($this->returnValue($dateField->value[0]['expValue']));

        $pmseRelatedModule = $this->getMockBuilder('PMSERelatedModule')
            ->disableOriginalConstructor()
            ->setMethods(array('addRelatedRecord'))
            ->getMock();

        $pmseRelatedModule->expects($this->any())
            ->method('addRelatedRecord')
            ->with($beanMock, "contacts", array('last_name' => 'New Contact', 'assigned_user_id' => "1", 'birthdate' => '2015-12-06'))
            ->will($this->returnValue(true));

        $this->addRelatedRecord->setLogger($this->loggerMock);
        $this->addRelatedRecord->setBeanHandler($beanHandler);
        $this->addRelatedRecord->setCaseFlowHandler($caseFlowHandlerMock);
        $this->addRelatedRecord->setPARelatedModule($pmseRelatedModule);

        $this->addRelatedRecord->run($flowData, $beanMock);

    }

    /**
     *
     */
    public function testRunFixedDatetime()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('save'))
            ->getMock();
        $beanMock->module_name = 'Calls';
        $beanMock->id = '8an9n0r2jd9j923cm89kyk32tb2in83';
        $beanMock->db = new stdClass();
        $beanMock->description = 'Some description';
        $beanMock->field_defs = array(
            'description' => array()
        );

//        Queue of fields to be added
        $assignedField = new stdClass();
        $assignedField->field = 'assigned_user_id';
        $assignedField->type = 'user';
        $assignedField->value = '1';

        $dateField = new stdClass();
        $dateField->field = 'birthdate';
        $dateField->type = 'Datetime';
        $dateField->value[0] = array('expType' => 'CONSTANT', 'expSubtype' => 'datetime', 'expValue' => '2015-12-06 00:00:00');

        $lastNameField = new stdClass();
        $lastNameField->field = 'last_name';
        $lastNameField->type = 'TextField';
        $lastNameField->value = 'New Contact';

//        Process definition
        $definitionMock = array(
            'id' => 'q2389djq9238jd93489234df9g5k',
            'pro_id' => 'sami89w93fm9w38fw',
            'act_field_module' => 'contacts',
            'pro_module' => 'Calls',
            'act_fields' => json_encode(array($lastNameField, $assignedField, $dateField))
        );

//        Process Flow
        $flowData = array(
            'bpmn_id' => 'o1289d89823dj23d892',
            'cas_id' => 1,
            'cas_index' => 2,
            'id' => '9238d3d234udj89234jd'
        );
        $this->addRelatedRecord = $this->getMockBuilder('PMSEAddRelatedRecord')
            ->setMethods(array('retrieveDefinitionData', 'retrieveHistoryData','getCustomUser'))
            ->getMock();

        $this->addRelatedRecord->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveBean'))
            ->getMock();

        $caseFlowHandlerMock->expects($this->at(0))
            ->method('retrieveBean')
            ->with('pmse_BpmActivityDefinition')
            ->will($this->returnValue((object) $definitionMock));

        $caseFlowHandlerMock->expects($this->at(1))
            ->method('retrieveBean')
            ->with('pmse_BpmProcessDefinition')
            ->will($this->returnValue((object) $definitionMock));

        $beanHandler = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationshipData', 'getCustomUser', 'calculateDueDate', 'processValueExpression', 'mergeBeanInTemplate'))
            ->getMock();

        $this->addRelatedRecord->expects($this->once())
            ->method('getCustomUser')
            ->will ($this->returnValue("1"));

        $beanHandler->expects($this->at(0))
            ->method('mergeBeanInTemplate')
            ->with($beanMock, "New Contact")
            ->will ($this->returnValue("New Contact"));

        $beanHandler->expects($this->at(2))
            ->method('mergeBeanInTemplate')
            ->with($beanMock, "1")
            ->will ($this->returnValue("1"));

        $beanHandler->expects($this->any())
            ->method('processValueExpression')
            ->will ($this->returnValue($dateField->value[0]['expValue']));

        $pmseRelatedModule = $this->getMockBuilder('PMSERelatedModule')
            ->disableOriginalConstructor()
            ->setMethods(array('addRelatedRecord'))
            ->getMock();

        $pmseRelatedModule->expects($this->any())
            ->method('addRelatedRecord')
            ->with($beanMock, "contacts", array('last_name' => 'New Contact', 'assigned_user_id' => "1", 'birthdate' => '2015-12-06 00:00:00'))
            ->will($this->returnValue(true));

        $this->addRelatedRecord->setLogger($this->loggerMock);
        $this->addRelatedRecord->setBeanHandler($beanHandler);
        $this->addRelatedRecord->setCaseFlowHandler($caseFlowHandlerMock);
        $this->addRelatedRecord->setPARelatedModule($pmseRelatedModule);

        $this->addRelatedRecord->run($flowData, $beanMock);

    }

}
