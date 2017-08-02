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
class PMSERelatedDependencyWrapperTest extends PHPUnit_Framework_TestCase 
{    
    protected $loggerMock;

    protected $relatedModuleMock;

    /**
     * Sets up the test data, for example, 
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('info', 'debug', 'warning', 'error'))
                ->getMock();

        $this->relatedModuleMock = $this->getMockBuilder('PMSERelatedModule')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelatedModuleName'))
            ->getMock();
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection. 
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    
    public function testProcessRelatedDependencies()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
                ->disableOriginalConstructor()
                ->setMethods(
                        array(
                            'processEventCriteria', 
                            'removeRelatedDependencies', 
                            'createRelatedDependencies'
                        )
                    )
                ->getMock();

        $this->loggerMock->expects($this->once())
                ->method('info');

        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $relatedDepWrapperMock->expects($this->once())
                ->method('processEventCriteria');
        $relatedDepWrapperMock->expects($this->once())
                ->method('removeRelatedDependencies');
        $relatedDepWrapperMock->expects($this->once())
                ->method('createRelatedDependencies');

        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $eventData = array('evn_criteria' => 'Some Criteria');

        $relatedDepWrapperMock->processRelatedDependencies($eventData);
    }
    
    public function testProcessEventCriteriaNonEmpty()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
                ->disableOriginalConstructor()
                ->setMethods(
                        array(
                            'removeRelatedDependencies',
                            'createRelatedDependencies',
                            'getBean',
                            'getRelatedElementModule'
                        )
                    )
                ->getMock();
        
        $processDefinitionMock = $this->getMockBuilder('psme_BpmProcessDefinition')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields'))
                ->getMock();
        
        $processDefinitionMock->pro_module = "Leads";
        $processDefinitionMock->pro_status = "ACTIVE";
        $processDefinitionMock->pro_locked_variables = "locked01, locked02";
        $processDefinitionMock->pro_terminate_variables = "terminate01, terminate02";
        
        $relatedDepWrapperMock->expects($this->atLeastOnce())
                ->method('getBean')
                ->will($this->returnValue($processDefinitionMock));

        $this->loggerMock->expects($this->once())
                ->method('debug');
        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $eventCriteria = '[]';

        $eventData = array(
            'id' => 'event01',
            'evn_behavior' => 'CATCH',
            'pro_id' => 'pro01',
            'evn_type' => 'START_EVENT',
            'rel_element_module' => 'Notes',
        );
        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $result = $relatedDepWrapperMock->processEventCriteria($eventCriteria, $eventData);
        
    }
    
    public function testProcessEventCriteriaEmpty()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
                ->disableOriginalConstructor()
                ->setMethods(
                        array(
                            'removeRelatedDependencies',
                            'createRelatedDependencies',
                            'getBean',
                            'getRelatedElementModule'
                        )
                    )
                ->getMock();
        
        $processDefinitionMock = $this->getMockBuilder('pmse_BpmProcessDefinition')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields'))
                ->getMock();
        
        $processDefinitionMock->pro_module = "Leads";
        $processDefinitionMock->pro_status = "ACTIVE";
        $processDefinitionMock->pro_locked_variables = "locked01, locked02";
        $processDefinitionMock->pro_terminate_variables = "terminate01, terminate02";
        
        $relatedDepWrapperMock->expects($this->atLeastOnce())
                ->method('getBean')
                ->will($this->returnValue($processDefinitionMock));

        $this->loggerMock->expects($this->once())
                ->method('debug');
        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $eventCriteria = '['
                . '{'
                    . '"expType" : "MODULE",'
                    . '"expModule" : "Leads"'
                . '},'
                . '{'
                    . '"expType" : "MODULE",'
                    . '"expModule" : "Leads"'
                . '}'
            . ']';

        $eventData = array(
            'id' => 'event01',
            'evn_behavior' => 'CATCH',
            'pro_id' => 'pro01',
            'evn_type' => 'START_EVENT',
            'rel_element_module' => 'Notes',
        );
        
        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $result = $relatedDepWrapperMock->processEventCriteria($eventCriteria, $eventData);
        
    }

    public function testProcessEventCriteriaThrowEvent()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
                ->disableOriginalConstructor()
                ->setMethods(
                        array(
                            'removeRelatedDependencies',
                            'createRelatedDependencies',
                            'getBean',
                            'getRelatedElementModule'
                        )
                    )
                ->getMock();
        
        $this->loggerMock->expects($this->once())
                ->method('debug');
        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $eventCriteria = '['
                . '{'
                    . '"expType" : "MODULE",'
                    . '"expModule" : "Leads"'
                . '},'
                . '{'
                    . '"expType" : "MODULE",'
                    . '"expModule" : "Leads"'
                . '}'
            . ']';

        $eventData = array(
            'id' => 'event01',
            'evn_behavior' => 'TRHOW',
            'pro_id' => 'pro01',
            'evn_type' => 'START_EVENT',
            'rel_element_module' => 'Notes',
        );

        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $result = $relatedDepWrapperMock->processEventCriteria($eventCriteria, $eventData);
        $this->assertEmpty($result);
    }
    
    public function testGetRelatedElementModuleIfModulesAreTheSame()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'removeRelatedDependencies',
                        'createRelatedDependencies',
                        'getBean'
                    )
                )
            ->getMock();
        
        $this->loggerMock->expects($this->once())
                ->method('debug');

        $tmpObject = new stdClass();
        $tmpObject->rel_process_module = 'Leads';
        $tmpCriteria = new stdClass();
        $tmpCriteria->expModule = 'Leads';
        
        $relatedDepWrapperMock->setLogger($this->loggerMock);

        $relatedDepWrapperMock->getRelatedElementModule($tmpObject, $tmpCriteria);
    }
    
    public function testGetRelatedElementModuleIfModulesAreDifferent()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'removeRelatedDependencies',
                        'createRelatedDependencies',
                        'getBean'
                    )
                )
            ->getMock();
        
        $this->loggerMock->expects($this->once())
                ->method('debug');
        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $relationshipMock = $this->getMockBuilder('Relationship')
                ->disableOriginalConstructor()
                ->setMethods(array('get_other_module'))
                ->getMock();
        $relationshipMock->db = new stdClass();

        $relatedDepWrapperMock->setRelationship($relationshipMock);
        
        $tmpObject = new stdClass();
        $tmpObject->rel_process_module = 'Leads';
        $tmpCriteria = new stdClass();
        $tmpCriteria->expModule = 'Notes';
        
        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $relatedDepWrapperMock->getRelatedElementModule($tmpObject, $tmpCriteria);
    }
    
    public function testRemoveRelatedDependencies()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'getRelatedDependency',
                        'getBean'
                    )
                )
            ->getMock();
        
        $this->loggerMock->expects($this->once())
                ->method('debug');
        
        $relatedDependencyMock = $this->getMockBuilder('Relationship')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_string_fields'))
                ->getMock();
        $relatedDependencyMock->db = new stdClass();

        $elementMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();
        $elementMock->deleted = 0;
        
        $relatedDependencyMock->expects($this->at(0))
                ->method('retrieve_by_string_fields')
                ->will($this->returnValue($elementMock));
        
        $relatedDependencyMock->expects($this->at(1))
                ->method('retrieve_by_string_fields')
                ->will($this->returnValue(false));
        
        $relatedDepWrapperMock->expects($this->once())
                ->method('getRelatedDependency')
                ->will($this->returnValue($relatedDependencyMock));
        
        $tmpObject = new stdClass();
        $tmpObject->rel_process_module = 'Leads';
        $tmpCriteria = new stdClass();
        $tmpCriteria->expModule = 'Notes';
        
        $relatedDepWrapperMock->setLogger($this->loggerMock);

        $eventData = array('id' => 'event01', 'pro_id' => 'pro01');
        $relatedDepWrapperMock->removeRelatedDependencies($eventData);
    }
    
    public function testCreateRelatedDependencies()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                    array(
                        'getRelatedDependency',
                        'getBean'
                    )
                )
            ->getMock();
        
        $this->loggerMock->expects($this->once())
                ->method('debug');
        
        $relatedDependencyMock = $this->getMockBuilder('Relationship')
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();

        $relatedDependencyMock->expects($this->atLeastOnce())
                ->method('save');
        
        $relatedDepWrapperMock->expects($this->atLeastOnce())
                ->method('getBean')
                ->will($this->returnValue($relatedDependencyMock));
        
        $relatedDepWrapperMock->setLogger($this->loggerMock);

        $resultData = array(
            array(
                'id' => 'event01', 
                'pro_id' => 'pro01'
            ),
            array(
                'id' => 'event02', 
                'pro_id' => 'pro02'
            )
        );
        $relatedDepWrapperMock->createRelatedDependencies($resultData);
    }
}
