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

use Sugarcrm\Sugarcrm\ProcessManager;

/**
 * @covers PMSEActivityDefinitionWrapper
 */
$beanList = array('Leads'=>'Notes');
class PMSEActivityDefinitionWrapperTest extends PHPUnit_Framework_TestCase
{
    protected $actDefWrapper;
    protected $fixtureArray;
    protected $arguments;
    protected $newId = '';
    protected $mocActDef;
    protected $mocActivity;
    protected $activityWrapper;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mocActDef = $this->getMockBuilder('pmse_BpmActivityDefinition')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->getMock();
        $this->mocActivity = $this->getMockBuilder('pmse_BpmnActivity')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->getMock();
        $this->mocProcessDefinition = $this->getMockBuilder('pmse_BpmProcessDefinition')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $this->mocBeanFactory = $this->getMockBuilder('pmse_ADAMBeanFactory')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->getMock();
        $this->mocRelationship = $this->getMockBuilder('DeployedRelationships')
                ->disableAutoload()
                ->disableOriginalConstructor()
                ->getMock();

        $this->actDefWrapper = ProcessManager\Factory::getPMSEObject('PMSEActivityDefinitionWrapper');
        $this->actDefWrapper->setActivityDefinition($this->mocActDef);
        $this->actDefWrapper->setActivity($this->mocActivity);
        $this->actDefWrapper->setProcessDefinition($this->mocProcessDefinition);
        $this->actDefWrapper->setFactory($this->mocBeanFactory);
    }

    /** CRUD tests * */
    public function test_Get()
    {
        $mocActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();

        $mocProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();

        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();

        $this->actDefWrapper = $this->getMockBuilder('PMSEActivityDefinitionWrapper')
            ->setMethods(array('getDefaultReadOnlyFields', 'getReadOnlyFields', 'getRelatedModules'))
            ->getMock();

        $this->actDefWrapper->expects($this->any())
            ->method('getDefaultReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getRelatedModules')
            ->will($this->returnValue('none'));

        $mockActivity->id = 1;
        $mockActivity->prj_id = 1;
        $mockActivity->prj_uid = '2193798123';
        $mockActivity->fetched_row = array(
            'id' => 1,
            'name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity)
        );

        $mocActDef->pro_id = 1;
        $mocActDef->prj_uid = '2193798123';
        $mocActDef->fetched_row = array(
            'act_id' => 1,
            'act_uid' => '2193798123',
        );

        $mocActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mocActDef)
        );

        $mocProDef->pro_id = 1;
        $mocProDef->prj_uid = '2193798123';
        $mocProDef->pro_module = 'Leads';
        $mocProDef->fetched_row = array(
            'pro_id' => 1,
            'prj_uid' => '2193798123',
        );

        $mocProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mocProDef)
        );

        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setActivityDefinition($mocActDef);
        $this->actDefWrapper->setProcessDefinition($mocProDef);

        $arguments = array('record' => 1, 'module' => 'Leads');
        $objectx = new stdClass();
        $objectx->id = 1;
        $res = $this->actDefWrapper->_get($arguments);
    }
    
    public function test_GetWithParameters()
    {
        $mocActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();

        $mocProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();

        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();

        $this->actDefWrapper = $this->getMockBuilder('PMSEActivityDefinitionWrapper')
            ->setMethods(array('getDefaultReadOnlyFields', 'getReadOnlyFields', 'getRelatedModules'))
            ->getMock();

        $this->actDefWrapper->expects($this->any())
            ->method('getDefaultReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getRelatedModules')
            ->will($this->returnValue('none'));

        $mockActivity->id = 1;
        $mockActivity->prj_id = 1;
        $mockActivity->prj_uid = '2193798123';
        $mockActivity->fetched_row = array(
            'id' => 1,
            'name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity));

        $mocActDef->pro_id = 1;
        $mocActDef->prj_uid = '2193798123';
        $mocActDef->fetched_row = array(
            'act_id' => 1,
            'act_uid' => '2193798123',
            'act_readonly_fields' => 'something',
            'act_required_fields' => 'something',
            'act_expected_time' => 'something',
            'act_related_modules' => 'Leads'
        );

        $mocActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mocActDef)
        );

        $mocProDef->pro_id = 1;
        $mocProDef->prj_uid = '2193798123';
        $mocProDef->pro_module = 'Leads';
        $mocProDef->fetched_row = array(
            'pro_id' => 1,
            'prj_uid' => '2193798123',
        );

        $mocProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mocProDef)
        );

        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setActivityDefinition($mocActDef);
        $this->actDefWrapper->setProcessDefinition($mocProDef);

        $arguments = array('record' => 1, 'module' => 'Leads');
        $objectx = new stdClass();
        $objectx->id = 1;
        $res = $this->actDefWrapper->_get($arguments);
    }

    public function test_Post()
    {
        $mockActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields', 'save'))
            ->getMock();
        
        $mockActDef->pro_id = 1;
        $mockActDef->prj_uid = '2193798123';
        $mockActDef->act_assignment_method = 'static';
        $mockActDef->in_save = false;
        $mockActDef->fetched_row = array(
            'act_id' => 1,
            'act_uid' => '2193798123',
        );

        $mockActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActDef)
        );
        
        $mockActDef->expects($this->any())
            ->method('save')
            ->will($this->returnValue(1)
        );

        $mockProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();
        
        $mockProDef->pro_id = 1;
        $mockProDef->prj_uid = '219379123';
        $mockProDef->pro_module = 'Leads';
        $mockProDef->fetched_row = array(
            'pro_id' => 1,
            'prj_uid' => '219379123',
        );

        $mockProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockProDef)
        );
        
        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields', 'getPrimaryFieldName', 'getIndices'))
            ->getMock();
        
        $mockActivity->id = 1;
        $mockActivity->prj_id = 1;
        $mockActivity->prj_uid = '219379123';
        $mockActivity->fetched_row = array(
            'id' => 1,
            'name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity)
        );
        $mockActivity->expects($this->any())
            ->method("getPrimaryFieldName")
            ->will($this->returnValue('act_id')
        );

        $this->actDefWrapper = $this->getMockBuilder('PMSEActivityDefinitionWrapper')
            ->setMethods(array('getDefaultReadOnlyFields', 'getReadOnlyFields', 'getRelatedModules'))
            ->getMock();

        $this->actDefWrapper->expects($this->any())
            ->method('getDefaultReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getRelatedModules')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setActivityDefinition($mockActDef);
        $this->actDefWrapper->setProcessDefinition($mockProDef);

        $arguments = array('attribute_1' => 'one', 'attribute_2' => 'two', 'act_readonly_fields' => 'some text', 'act_uid' => '2193798123');

        $res = $this->actDefWrapper->_post($arguments);
    }
    
    public function test_PostNoStatic()
    {
        $mockActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields', 'save'))
            ->getMock();
        
        $mockActDef->pro_id = 1;
        $mockActDef->prj_uid = '2193798123';
        $mockActDef->act_assignment_method = 'no-static';
        $mockActDef->in_save = false;
        $mockActDef->fetched_row = array(
            'act_id' => 1,
            'act_uid' => '2193798123',
        );

        $mockActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActDef)
        );
        
        $mockActDef->expects($this->any())
            ->method('save')
            ->will($this->returnValue(1)
        );

        $mockProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();
        
        $mockProDef->pro_id = 1;
        $mockProDef->prj_uid = '219379123';
        $mockProDef->pro_module = 'Leads';
        $mockProDef->fetched_row = array(
            'pro_id' => 1,
            'prj_uid' => '219379123',
        );

        $mockProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockProDef)
        );
        
        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields', 'getPrimaryFieldName', 'getIndices'))
            ->getMock();
        
        $mockActivity->id = 1;
        $mockActivity->prj_id = 1;
        $mockActivity->prj_uid = '219379123';
        $mockActivity->fetched_row = array(
            'id' => 1,
            'name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity)
        );
        $mockActivity->expects($this->any())
            ->method("getPrimaryFieldName")
            ->will($this->returnValue('act_id')
        );

        $this->actDefWrapper = $this->getMockBuilder('PMSEActivityDefinitionWrapper')
            ->setMethods(array('getDefaultReadOnlyFields', 'getReadOnlyFields', 'getRelatedModules'))
            ->getMock();

        $this->actDefWrapper->expects($this->any())
            ->method('getDefaultReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getRelatedModules')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setActivityDefinition($mockActDef);
        $this->actDefWrapper->setProcessDefinition($mockProDef);

        $arguments = array('attribute_1' => 'one', 'attribute_2' => 'two', 'act_readonly_fields' => 'some text', 'act_uid' => '2193798123');

        $res = $this->actDefWrapper->_post($arguments);
    }
    
    public function test_Put()
    {
        $mockActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields', 'save'))
            ->getMock();
        $mockActDef->id = 'act01';
        $mockActDef->pro_id = 1;
        $mockActDef->prj_uid = '2193798123';
        $mockActDef->act_assignment_method = 'no-static';
        $mockActDef->act_type = 'SCRIPTTASK';
        $mockActDef->in_save = false;
        $mockActDef->fetched_row = array(
            'act_id' => "act01",
            'act_uid' => '2193798123',
        );

        $mockActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActDef)
        );
        
        $mockActDef->expects($this->any())
            ->method('save')
            ->will($this->returnValue(1)
        );

        $mockProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();
        $mockProDef->pro_id = 1;
        $mockProDef->prj_uid = '219379123';
        $mockProDef->pro_module = 'Leads';
        $mockProDef->fetched_row = array(
            'id' => 1,
            'prj_uid' => '219379123',
        );
        $mockProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockProDef)
        );
        
        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields', 'getPrimaryFieldName'))
            ->getMock();

        $mockActivity->id = "act01";
        $mockActivity->prj_id = 1;
        $mockActivity->prj_uid = '219379123';
        $mockActivity->act_type = 'TASK';
        $mockActivity->fetched_row = array(
            'id' => "act01",
            'act_name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity)
        );
        $mockActivity->expects($this->any())
            ->method("getPrimaryFieldName")
            ->will($this->returnValue('act_id')
        );

        $this->actDefWrapper = $this->getMockBuilder('PMSEActivityDefinitionWrapper')
            ->setMethods(array('getDefaultReadOnlyFields', 'getReadOnlyFields', 'getRelatedModules'))
            ->getMock();

        $this->actDefWrapper->expects($this->any())
            ->method('getDefaultReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getRelatedModules')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setActivityDefinition($mockActDef);
        $this->actDefWrapper->setProcessDefinition($mockProDef);

        $arguments = array(
            'record'=>'273728823',
            'data' => array(
                'attribute_1' => 'one', 
                'attribute_2' => 'two', 
                'act_type' => 'TASK', 
                'act_readonly_fields' => 'some text', 
                'act_uid' => '2193798123', 
                'id'=>'273728823'
            )
        );

        $res = $this->actDefWrapper->_put($arguments);
    }
    
    public function test_PutUserTaskWithStaticAssignment()
    {
        $mockActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields', 'save'))
            ->getMock();
        $mockActDef->id = 'act01';
        $mockActDef->pro_id = 1;
        $mockActDef->prj_uid = '2193798123';
        $mockActDef->act_assignment_method = 'static';
        $mockActDef->act_type = 'SCRIPTTASK';
        $mockActDef->in_save = false;
        $mockActDef->fetched_row = array(
            'act_id' => "act01",
            'act_uid' => '2193798123',
        );

        $mockActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActDef)
        );
        
        $mockActDef->expects($this->any())
            ->method('save')
            ->will($this->returnValue(1)
        );

        $mockProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();
        $mockProDef->pro_id = 1;
        $mockProDef->prj_uid = '219379123';
        $mockProDef->pro_module = 'Leads';
        $mockProDef->fetched_row = array(
            'pro_id' => 1,
            'prj_uid' => '219379123',
        );
        $mockProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockProDef)
        );
        
        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields', 'getPrimaryFieldName'))
            ->getMock();

        $mockActivity->id = "act01";
        $mockActivity->prj_id = 1;
        $mockActivity->act_type = 'USER';
        $mockActivity->prj_uid = '219379123';
        $mockActivity->fetched_row = array(
            'id' => "act01",
            'act_name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity)
        );
        $mockActivity->expects($this->any())
            ->method("getPrimaryFieldName")
            ->will($this->returnValue('act_id')
        );

        $this->actDefWrapper = $this->getMockBuilder('PMSEActivityDefinitionWrapper')
            ->setMethods(array('getDefaultReadOnlyFields', 'getReadOnlyFields', 'getRelatedModules'))
            ->getMock();

        $this->actDefWrapper->expects($this->any())
            ->method('getDefaultReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getRelatedModules')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setActivityDefinition($mockActDef);
        $this->actDefWrapper->setProcessDefinition($mockProDef);

        $arguments = array(
            'record'=>'273728823',
            'data' => array(
                'attribute_1' => 'one', 
                'attribute_2' => 'two', 
                'act_readonly_fields' => 'some text', 
                'act_type' => 'TASK', 
                'act_uid' => '2193798123', 
                'id'=>'273728823'
            )
        );

        $res = $this->actDefWrapper->_put($arguments);
    }
    
    public function test_PutUserTaskWithNonStaticAssignment()
    {
        $mockActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields', 'save'))
            ->getMock();
        $mockActDef->id = 'act01';
        $mockActDef->pro_id = 1;
        $mockActDef->prj_uid = '2193798123';
        $mockActDef->act_assignment_method = 'balanced';
        $mockActDef->act_type = 'SCRIPTTASK';
        $mockActDef->in_save = false;
        $mockActDef->fetched_row = array(
            'act_id' => "act01",
            'act_uid' => '2193798123',
        );

        $mockActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActDef)
        );
        
        $mockActDef->expects($this->any())
            ->method('save')
            ->will($this->returnValue(1)
        );

        $mockProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();
        $mockProDef->pro_id = 1;
        $mockProDef->prj_uid = '219379123';
        $mockProDef->pro_module = 'Leads';
        $mockProDef->fetched_row = array(
            'pro_id' => 1,
            'prj_uid' => '219379123',
        );
        $mockProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockProDef)
        );
        
        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields', 'getPrimaryFieldName'))
            ->getMock();

        $mockActivity->id = "act01";
        $mockActivity->prj_id = 1;
        $mockActivity->act_type = 'USER';
        $mockActivity->prj_uid = '219379123';
        $mockActivity->fetched_row = array(
            'id' => "act01",
            'act_name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity)
        );
        $mockActivity->expects($this->any())
            ->method("getPrimaryFieldName")
            ->will($this->returnValue('act_id')
        );

        $this->actDefWrapper = $this->getMockBuilder('PMSEActivityDefinitionWrapper')
            ->setMethods(array('getDefaultReadOnlyFields', 'getReadOnlyFields', 'getRelatedModules'))
            ->getMock();

        $this->actDefWrapper->expects($this->any())
            ->method('getDefaultReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getReadOnlyFields')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->expects($this->any())
            ->method('getRelatedModules')
            ->will($this->returnValue('none'));

        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setActivityDefinition($mockActDef);
        $this->actDefWrapper->setProcessDefinition($mockProDef);

        $arguments = array(
            'record'=>'273728823',
            'data' => array(
                'attribute_1' => 'one', 
                'attribute_2' => 'two', 
                'act_readonly_fields' => 'some text', 
                'act_type' => 'TASK', 
                'act_uid' => '2193798123', 
                'id'=>'273728823'
            )
        );

        $res = $this->actDefWrapper->_put($arguments);
    }
    
    public function testSearchModules()
    {
        $relationshipName = 'lead_notes';
        
        $options = array(
            0 => array (
                'checked' => false,
            ),
            1 => array (
                'checked' => false,
            )            
        );
        $expectedOptions = array(
            0 => array (
                'checked' => true,
            ),
            1 => array (
                'checked' => true,
            )            
        );
        $json = new stdClass();
        $json->lead_notes = array('add','view');
        
        $res = $this->actDefWrapper->searchModules($relationshipName, $options, $json);
        $this->assertEquals($expectedOptions, $res);
    }
    
    public function testGetDefaultReadOnlyFields()
    {
        $mockActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields', 'save'))
            ->getMock();
        $mockActDef->pro_id = 1;
        $mockActDef->prj_uid = '2193798123';
        $mockActDef->act_assignment_method = 'static';
        $mockActDef->act_type = 'SCRIPTTASK';
        $mockActDef->in_save = false;
        $mockActDef->fetched_row = array(
            'act_id' => 1,
            'act_uid' => '2193798123',
        );

        $mockActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActDef)
        );
        
        $mockProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();
        $mockProDef->pro_id = 1;
        $mockProDef->prj_uid = '219379123';
        $mockProDef->pro_module = 'Leads';
        $mockProDef->fetched_row = array(
            'pro_id' => 1,
            'prj_uid' => '219379123',
        );
        
        $mockProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockProDef)
        );
        
        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields', 'getPrimaryFieldName'))
            ->getMock();

        $mockActivity->prj_id = 1;
        $mockActivity->prj_uid = '219379123';
        $mockActivity->fetched_row = array(
            'act_id' => 1,
            'act_name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity)
        );
        $mockActivity->expects($this->any())
            ->method("getPrimaryFieldName")
            ->will($this->returnValue('act_id')
        );
        
        $beanStub = new stdClass();
        $beanStub->field_defs = array(
            array(
                'name' => 'id',
                'vname' => 'id',
            ),
            array(
                'name' => 'name',
                'vname' => 'lead_name',
            ),
            array(
                'name' => 'last_name',
                'vname' => 'last_name',
            )
        );
        
        $mockFactory = $this->getMockBuilder('ADAMBeanFactory')
            ->setMethods(array('getBean'))
            ->getMock();
        
        $mockFactory->expects($this->any())
            ->method('getBean')
            ->will($this->returnValue($beanStub));
        
        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setFactory($mockFactory);
        $this->actDefWrapper->setActivityDefinition($mockActDef);
        $this->actDefWrapper->setProcessDefinition($mockProDef);
        $this->actDefWrapper->getDefaultReadOnlyFields();
    }
    
    public function testGetDefaultRequiredFields()
    {
        $mockActDef = $this->getMockBuilder('BpmActivityDefinition')
            ->setMethods(array('retrieve_by_string_fields', 'save'))
            ->getMock();
        $mockActDef->pro_id = 1;
        $mockActDef->prj_uid = '2193798123';
        $mockActDef->act_assignment_method = 'static';
        $mockActDef->act_type = 'SCRIPTTASK';
        $mockActDef->in_save = false;
        $mockActDef->fetched_row = array(
            'act_id' => 1,
            'act_uid' => '2193798123',
        );

        $mockActDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActDef)
        );
        
        $mockProDef = $this->getMockBuilder('BpmProcessDefinition')
            ->setMethods(array('retrieve_by_string_fields'))
            ->getMock();
        $mockProDef->pro_id = 1;
        $mockProDef->prj_uid = '219379123';
        $mockProDef->pro_module = 'Leads';
        $mockProDef->fetched_row = array(
            'pro_id' => 1,
            'prj_uid' => '219379123',
        );
        
        $mockProDef->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockProDef)
        );
        
        $mockActivity = $this->getMockBuilder('BpmnActivity')
            ->setMethods(array('retrieve_by_string_fields', 'getPrimaryFieldName'))
            ->getMock();

        $mockActivity->prj_id = 1;
        $mockActivity->prj_uid = '219379123';
        $mockActivity->fetched_row = array(
            'act_id' => 1,
            'act_name' => 'activity',
        );

        $mockActivity->act_id = 1;
        $mockActivity->expects($this->any())
            ->method("retrieve_by_string_fields")
            ->with($this->isType('array'))
            ->will($this->returnValue($mockActivity)
        );
        $mockActivity->expects($this->any())
            ->method("getPrimaryFieldName")
            ->will($this->returnValue('act_id')
        );
        
        $beanStub = new stdClass();
        $beanStub->field_defs = array(
            array (
                'name' => 'id',
                'vname' => 'id',
                'type' => 'bool',
                'required' => false,
            ),
            array (
                'name' => 'name',
                'vname' => 'lead_name',
                'type' => 'radioenum',
                'required' => false,
            ),
            array (
                'name' => 'last_name',
                'vname' => 'last_name',
                'type' => 'string',
                'required' => false,
            )
        );
        
        $mockFactory = $this->getMockBuilder('ADAMBeanFactory')
            ->setMethods(array('getBean'))
            ->getMock();
        
        $mockFactory->expects($this->any())
            ->method('getBean')
            ->will($this->returnValue($beanStub));
        
        $this->actDefWrapper->setActivity($mockActivity);
        $this->actDefWrapper->setFactory($mockFactory);
        $this->actDefWrapper->setActivityDefinition($mockActDef);
        $this->actDefWrapper->setProcessDefinition($mockProDef);
        $this->actDefWrapper->getDefaultRequiredFields();
    }
    
    public function testGetReadOnlyFields()
    {
        $readOnlyFields = array(
            'field1',
            'field3'
        );
        $fields = array(
            array('name'=>'field1', 'readonly'=>false),
            array('name'=>'field2', 'readonly'=>false),
            array('name'=>'field3', 'readonly'=>false),
            array('name'=>'field4', 'readonly'=>false),
            array('name'=>'field5', 'readonly'=>false)
        );
        $expectedReturn = $fields = array(
            array('name'=>'field1', 'readonly'=>true),
            array('name'=>'field3', 'readonly'=>true)
        );
        $result = $this->actDefWrapper->getReadOnlyFields($fields, $readOnlyFields);
        $this->assertEquals($expectedReturn, $result);
    }
    
    public function testGetRequiredFields()
    {
        $requiredFields = array(
            'field1',
            'field3'
        );

        $fields = array(
            array('name'=>'field1', 'required'=>true),
            array('name'=>'field2', 'required'=>true),
            array('name'=>'field3', 'required'=>true),
            array('name'=>'field4', 'required'=>true),
            array('name'=>'field5', 'required'=>true)
        );

        $expectedReturn = $fields = array(
            array('name'=>'field1', 'required'=>true),
            array('name'=>'field3', 'required'=>true)
        );

        $resultFields = $this->actDefWrapper->getRequiredFields($fields, $requiredFields);
        $this->assertEquals($expectedReturn, $resultFields);
    }
    
    public function testGetAjaxRelationships()
    {
        $relListStub = array('stub1', 'stub2', 'stub3', 'stub4', 'stub5');
        
        $relationships = $this->getMockBuilder('Relationships')
            ->setMethods(array('getRelationshipList', 'get'))
            ->disableOriginalConstructor()
            ->getMock();
        $relationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue($relListStub));
        
        $mockObject = $this->getMockBuilder('Relationships')
            ->setMethods(array('getRelationshipList', 'get', 'getDefinition'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $relStub1 = array('lhs_module'=>'Leads', 'rhs_module'=>'Opportunities', 'relationship_type'=>'one-to-one', 'is_custom'=>true);
        $relStub2 = array('lhs_module'=>'Leads', 'rhs_module'=>'Opportunities', 'relationship_type'=>'one-to-many', 'is_custom'=>false);
        $relStub3 = array('lhs_module'=>'Leads', 'rhs_module'=>'Opportunities', 'relationship_type'=>'many-to-one', 'is_custom'=>true);
        $relStub4 = array('lhs_module'=>'Leads', 'rhs_module'=>'Opportunities', 'relationship_type'=>'many-to-many', 'is_custom'=>false);
        $relStub5 = array('lhs_module'=>'Leads', 'rhs_module'=>'Opportunities', 'relationship_type'=>'something-else', 'is_custom'=>true, 'from_studio' => true);
            
        $mockObject->expects($this->at(0))
            ->method('getDefinition')
            ->will($this->returnValue($relStub1));
        
        $mockObject->expects($this->at(1))
            ->method('getDefinition')
            ->will($this->returnValue($relStub2));
        
        $mockObject->expects($this->at(2))
            ->method('getDefinition')
            ->will($this->returnValue($relStub3));
        
        $mockObject->expects($this->at(3))
            ->method('getDefinition')
            ->will($this->returnValue($relStub4));
        
        $mockObject->expects($this->at(4))
            ->method('getDefinition')
            ->will($this->returnValue($relStub5));
         
        $relationships->expects($this->any())
            ->method('get')
            ->will($this->returnValue($mockObject));
        
        $this->actDefWrapper->getAjaxRelationships($relationships);
    }
}
