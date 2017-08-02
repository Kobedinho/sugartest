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
class PMSEChangeFieldTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var type 
     */
    protected $loggerMock;

    /**
     * @var PMSEElement
     */
    protected $changeField;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
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
        
    }
    
    
    public function testRunDefault()
    {
        $field = new stdClass();
        $field->field = 'description';
        $field->type = 'string';
        $field->value = 'Some Value';
                
        $definitionMock = array(
            'id' => 'q2389djq9238jd93489234df9g5k',
            'pro_id' => 'sami89w93fm9w38fw',
            'act_field_module' => 'Leads',
            'act_fields' => json_encode(array($field))
        );
        
        $flowData = array(
            'bpmn_id' => 'o1289d89823dj23d892',
            'cas_id' => 1,
            'cas_index' => 2,
            'id' => '9238d3d234udj89234jd'
        );
        
        $this->changeField = $this->getMockBuilder('PMSEChangeField')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveDefinitionData', 'retrieveHistoryData'))
            ->getMock();
        
        $this->changeField->setLogger($this->loggerMock);
        
        $historyMock = $this->getMockBuilder('PMSEHistory')
            ->setMethods(array('savePostdata', 'savePredata', 'getLog'))
            ->getMock();

        $this->changeField->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));
        
        $this->changeField->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyMock));
        
        $caseHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('retrieveBean', 'saveFormAction'))
            ->getMock();
        
         $relationshipMock = $this->getMockBuilder('Relationship')
            ->setMethods(array('get_full_list', 'retrieve_by_sides'))
            ->getMock();
        
//        $caseHandler->expects($this->at(0))
//            ->method('retrieveBean')
//            ->will($this->returnValue($relationshipMock));
        
        $beanRelatedMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('retrieve_by_string_fields', 'get_full_list'))
            ->getMock();
        $beanRelatedMock->id = 'auiejwq8euiqweheiqw';
        $beanRelatedMock->description = 'Some description';
        
//        $caseHandler->expects($this->at(1))
//            ->method('retrieveBean')
//            ->will($this->returnValue($beanRelatedMock));
        
        $beanHandler = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationshipData', 'calculateDueDate', 'processValueExpression', 'mergeBeanInTemplate'))
            ->getMock();
        
        $relatedDataMock = array(
            'lhs_module' => 'Leads',
            'rhs_module' => 'Notes'
        );

        $beanHandler->expects($this->any())
            ->method('getRelationshipData')
            ->will ($this->returnValue($relatedDataMock));
        
        $beanHandler->expects($this->any())
            ->method('doesPrimaryEmailExists')
            ->will($this->returnValue(true));
        
        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('save'))
            ->getMock();
        
//        $caseHandler->expects($this->at(2))
//            ->method('retrieveBean')
//            ->will($this->returnValue($beanMock));
        
        $beanMock->module_name = 'Notes';
        $beanMock->id = '8an9n0r2jd9j923cm89kyk32tb2in83';
        $beanMock->db = new stdClass();
        $beanMock->description = 'Some description';
        $beanMock->field_defs = array(
            'description' => array()
        );
        
        
        
        
        $userMock = new stdClass();
        $userMock->id = 'dfi9j9382ujd9238df23';
        
        $beanList = array(
            'Leads' => array(),
            'Notes' => array()
        );
        
        $this->changeField->setBeanHandler($beanHandler);
        $this->changeField->setCaseFlowHandler($caseHandler);
        $this->changeField->setCurrentUser($userMock);
        $this->changeField->setBeanList($beanList);
        
        $this->changeField->run($flowData, $beanMock, '');
        
    }
    
    public function testRunNotModifiedFields()
    {
        $field = new stdClass();
        $field->field = 'description';
        $field->type = 'string';
        $field->value = 'Some Value';
                
        $definitionMock = array(
            'id' => 'q2389djq9238jd93489234df9g5k',
            'pro_id' => 'sami89w93fm9w38fw',
            'act_field_module' => 'Leads',
            'act_fields' => json_encode(array($field))
        );
        
        $flowData = array(
            'bpmn_id' => 'o1289d89823dj23d892',
            'cas_id' => 1,
            'cas_index' => 2,
            'id' => '9238d3d234udj89234jd'
        );
        
        $this->changeField = $this->getMockBuilder('PMSEChangeField')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveDefinitionData', 'retrieveHistoryData'))
            ->getMock();
        
        $this->changeField->setLogger($this->loggerMock);
        
        $historyMock = $this->getMockBuilder('PMSEHistory')
            ->setMethods(array('savePostdata', 'savePredata', 'getLog'))
            ->getMock();

        $this->changeField->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));
        
        $this->changeField->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyMock));
        
        $caseHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveBean', 'saveFormAction'))
            ->getMock();
        
        $beanHandler = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationshipData', 'calculateDueDate', 'processValueExpression', 'mergeBeanInTemplate'))
            ->getMock();
        
        $relatedDataMock = array(
            'lhs_module' => 'Some Module not in bean list',
            'rhs_module' => 'Another Module not in bean list'
        );

        $beanHandler->expects($this->any())
            ->method('getRelationshipData')
            ->will ($this->returnValue($relatedDataMock));
        
        $beanHandler->expects($this->any())
            ->method('doesPrimaryEmailExists')
            ->will($this->returnValue(true));
        
        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('save'))
            ->getMock();

        $beanMock->module_name = 'Notes';
        $beanMock->id = '8an9n0r2jd9j923cm89kyk32tb2in83';
        $beanMock->db = new stdClass();
        $beanMock->description = 'Some description';
        $beanMock->field_defs = array(
            'description' => array()
        );
                
        $userMock = new stdClass();
        $userMock->id = 'dfi9j9382ujd9238df23';
        
        $beanList = array(
            'Leads' => array(),
            'Notes' => array()
        );
        
        $this->changeField->setBeanHandler($beanHandler);
        $this->changeField->setCaseFlowHandler($caseHandler);
        $this->changeField->setCurrentUser($userMock);
        $this->changeField->setBeanList($beanList);
        
        $this->changeField->run($flowData, $beanMock, '');
        
    }
    
    public function testRunWithMultipleFieldTypes()
    {
        $firstField = new stdClass();
        $firstField->field = 'description';
        $firstField->type = 'String';
        $firstField->value = 'Some Value';
        
        $secondField = new stdClass();
        $secondField->field = 'rating';
        $secondField->type = 'Integer';
        $secondField->value = 1;
        
        $thirdField = new stdClass();
        $thirdField->field = 'date';
        $thirdField->type = 'Datetime';
        $thirdField->value = 281823719723;
                
        $definitionMock = array(
            'id' => 'q2389djq9238jd93489234df9g5k',
            'pro_id' => 'sami89w93fm9w38fw',
            'act_field_module' => 'Leads',
            'act_fields' => json_encode(array($firstField, $secondField, $thirdField))
        );
        
        $flowData = array(
            'bpmn_id' => 'o1289d89823dj23d892',
            'cas_id' => 1,
            'cas_index' => 2,
            'id' => '9238d3d234udj89234jd'
        );
        
        $this->changeField = $this->getMockBuilder('PMSEChangeField')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveDefinitionData', 'retrieveHistoryData'))
            ->getMock();
        
        $this->changeField->setLogger($this->loggerMock);
        
        $historyMock = $this->getMockBuilder('PMSEHistory')
            ->setMethods(array('savePostdata', 'savePredata', 'getLog'))
            ->getMock();

        $this->changeField->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));
        
        $this->changeField->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyMock));
        
        $caseHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('retrieveBean', 'saveFormAction'))
            ->getMock();
        
         $relationshipMock = $this->getMockBuilder('Relationship')
            ->setMethods(array('get_full_list', 'retrieve_by_sides'))
            ->getMock();
        
//        $caseHandler->expects($this->at(0))
//            ->method('retrieveBean')
//            ->will($this->returnValue($relationshipMock));
        
        $beanRelatedMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('retrieve_by_string_fields', 'get_full_list'))
            ->getMock();
        $beanRelatedMock->id = 'auiejwq8euiqweheiqw';
        $beanRelatedMock->description = 'Some description';
        
//        $caseHandler->expects($this->at(1))
//            ->method('retrieveBean')
//            ->will($this->returnValue($beanRelatedMock));
        
        $beanHandler = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationshipData', 'calculateDueDate', 'processValueExpression', 'mergeBeanInTemplate'))
            ->getMock();
        
        $relatedDataMock = array(
            'lhs_module' => 'Leads',
            'rhs_module' => 'Notes'
        );

        $beanHandler->expects($this->any())
            ->method('getRelationshipData')
            ->will ($this->returnValue($relatedDataMock));
        
        $beanHandler->expects($this->any())
            ->method('doesPrimaryEmailExists')
            ->will($this->returnValue(true));
        
        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('save'))
            ->getMock();
        
//        $caseHandler->expects($this->at(2))
//            ->method('retrieveBean')
//            ->will($this->returnValue($beanMock));
        
        $beanMock->module_name = 'Notes';
        $beanMock->id = '8an9n0r2jd9j923cm89kyk32tb2in83';
        $beanMock->db = new stdClass();
        $beanMock->rating = 2;
        $beanMock->date = 897329847298134;
        $beanMock->description = 'Original description';
        $beanMock->field_defs = array(
            'description' => array(),
            'rating' => array(),
            'date' => array()
        );
        
        $userMock = new stdClass();
        $userMock->id = 'dfi9j9382ujd9238df23';
        
        $beanList = array(
            'Leads' => array(),
            'Notes' => array()
        );
        
        $this->changeField->setBeanHandler($beanHandler);
        $this->changeField->setCaseFlowHandler($caseHandler);
        $this->changeField->setCurrentUser($userMock);
        $this->changeField->setBeanList($beanList);
        
        $this->changeField->run($flowData, $beanMock, '');
        
    }
     
    public function testRunWithNoValidBeanList()
    {
        $firstField = new stdClass();
        $firstField->field = 'description';
        $firstField->type = 'String';
        $firstField->value = 'Some Value';
        
        $secondField = new stdClass();
        $secondField->field = 'rating';
        $secondField->type = 'Integer';
        $secondField->value = 1;
        
        $thirdField = new stdClass();
        $thirdField->field = 'date';
        $thirdField->type = 'Datetime';
        $thirdField->value = 281823719723;
                
        $definitionMock = array(
            'id' => 'q2389djq9238jd93489234df9g5k',
            'pro_id' => 'sami89w93fm9w38fw',
            'act_field_module' => 'Leads',
            'act_fields' => json_encode(array($firstField, $secondField, $thirdField))
        );

        $flowData = array(
            'bpmn_id' => 'o1289d89823dj23d892',
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_sugar_module' => 'Notes',
            'id' => '9238d3d234udj89234jd'
        );
        
        $this->changeField = $this->getMockBuilder('PMSEChangeField')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveDefinitionData', 'retrieveHistoryData'))
            ->getMock();
        
        $this->changeField->setLogger($this->loggerMock);
        
        $historyMock = $this->getMockBuilder('PMSEHistory')
            ->setMethods(array('savePostdata', 'savePredata', 'getLog'))
            ->getMock();

        $this->changeField->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));
        
        $this->changeField->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyMock));
        
        $caseHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('retrieveBean', 'saveFormAction'))
            ->getMock();
        
         $relationshipMock = $this->getMockBuilder('Relationship')
            ->setMethods(array('get_full_list', 'retrieve_by_sides'))
            ->getMock();
        
//        $caseHandler->expects($this->at(0))
//            ->method('retrieveBean')
//            ->will($this->returnValue($relationshipMock));
        
        $beanRelatedMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('retrieve_by_string_fields', 'get_full_list'))
            ->getMock();
        
        $beanRelatedMock->expects($this->any())
            ->method('get_full_list')
            ->will($this->returnValue(array($beanRelatedMock)));
                
        
        //$beanRelatedMock->id = 'auiejwq8euiqweheiqw';
        $beanRelatedMock->description = 'Some description';
        
//        $caseHandler->expects($this->at(0))
//            ->method('retrieveBean')
//            ->will($this->returnValue($beanRelatedMock));
        
        $beanHandler = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelationshipData', 'calculateDueDate', 'processValueExpression', 'mergeBeanInTemplate'))
            ->getMock();
        
        $relatedDataMock = array(
            'lhs_module' => 'Leads',
            'rhs_module' => 'Notes'
        );

        $beanHandler->expects($this->any())
            ->method('getRelationshipData')
            ->will ($this->returnValue($relatedDataMock));
        
        $beanHandler->expects($this->any())
            ->method('doesPrimaryEmailExists')
            ->will($this->returnValue(true));
        
        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('save'))
            ->getMock();
        
        /*$caseHandler->expects($this->at(2))
            ->method('retrieveBean')
            ->will($this->returnValue($beanMock));
        */
        $beanMock->module_name = 'Notes';
        $beanMock->id = '8an9n0r2jd9j923cm89kyk32tb2in83';
        $beanMock->db = new stdClass();
        $beanMock->rating = 2;
        $beanMock->date = 897329847298134;
        $beanMock->description = 'Original description';
        $beanMock->field_defs = array(
            'description' => array(),
            'rating' => array(),
            'date' => array()
        );
        
        $userMock = new stdClass();
        $userMock->id = 'dfi9j9382ujd9238df23';
        
        $beanList = array(
            'Leads' => array(),
            'Notes' => array()
        );
        
        $this->changeField->setBeanHandler($beanHandler);
        $this->changeField->setCaseFlowHandler($caseHandler);
        $this->changeField->setCurrentUser($userMock);
        $this->changeField->setBeanList($beanList);
        $this->changeField->run($flowData, $beanMock, '');
        
    }
}
