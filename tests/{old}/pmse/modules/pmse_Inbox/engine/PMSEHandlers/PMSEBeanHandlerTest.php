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
class PMSEBeanHandlerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     *
     * @var PMSELogger 
     */
    protected $loggerMock;

    protected $originals = array();

    /**
     * Sets up the test data, for example, 
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        global $beanList, $db;

        $this->originals['beanList'] = $GLOBALS['beanList'];
        $this->originals['db'] = $GLOBALS['db'];

        $beanList = array('Opportunities'=>array(), 'Notes'=>array(), 'Leads'=>array(), 'Meetings'=>array());
        $db = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('warning', 'error', 'debug', 'info'))
                ->getMock();
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection. 
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        foreach($this->originals as $varname => $value) {
            $GLOBALS[$varname] = $value;
        }
    }
    
    public function testGetRelationshipDataInvalidModule()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'getRelationshipData'))
                ->getMock();
        
        $relationshipData = array('lhs_module'=>'Leads', 'rhs_module'=>'Accounts');
        
        $beanHandlerMock->expects($this->once())
                ->method('getRelationshipData')
                ->will($this->returnValue($relationshipData));

        $beanHandlerMock->setLogger($this->loggerMock);
        
        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array())
                ->getMock();
        $beanMock->id = 'beanId01';
        
        $flowDataMock = array('cas_sugar_module'=>'Accounts');

        $module = 'Leads';
        
        $beanHandlerMock->getRelatedModule($beanMock, $flowDataMock, $module);
    }
    
    public function testGetRelationshipDataNotRelated()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'getRelationshipData'))
                ->getMock();
        
        $relationShipData = array('lhs_module'=>'Notes', 'rhs_module'=>'Meetings');
        
        $beanHandlerMock->expects($this->once())
                ->method('getRelationshipData')
                ->will($this->returnValue($relationShipData));
        
        $beanHandlerMock->setLogger($this->loggerMock);
        
        $relationshipMock = $this->getMockBuilder('Relationship')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_sides'))
                ->getMock();
        
        $beanHandlerMock->expects($this->at(1))
                ->method('retrieveBean')
                ->will($this->returnValue($relationshipMock));
        
        $relatedBeanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array('get_full_list', 'retrieve_by_string_fields'))
                ->getMock();
        
        $beanHandlerMock->expects($this->at(2))
                ->method('retrieveBean')
                ->will($this->returnValue($relatedBeanMock));
        
        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array())
                ->getMock();
        $beanMock->id = 'beanId01';
        
        $flowDataMock = array('cas_sugar_module'=>'Meetings');

        $module = 'Leads';
        
        $result = $beanHandlerMock->getRelatedModule($beanMock, $flowDataMock, $module);
        $this->assertNull($result);
    }
    
    public function testGetRelationshipDataRelated()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'getRelationshipData'))
                ->getMock();
        
        $relationShipData = array('lhs_module'=>'Notes', 'rhs_module'=>'Meetings');
        
        $beanHandlerMock->expects($this->once())
                ->method('getRelationshipData')
                ->will($this->returnValue($relationShipData));
        
        $beanHandlerMock->setLogger($this->loggerMock);
        
        $relationshipMock = $this->getMockBuilder('Relationship')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_sides'))
                ->getMock();
        
        $beanHandlerMock->expects($this->at(1))
                ->method('retrieveBean')
                ->will($this->returnValue($relationshipMock));
        
        $relatedBeanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array('get_full_list', 'retrieve_by_string_fields'))
                ->getMock();
        
        $relatedBeanMock->id = 'relBeanId01';
        
        $beanHandlerMock->expects($this->at(2))
                ->method('retrieveBean')
                ->will($this->returnValue($relatedBeanMock));
        
        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array())
                ->getMock();
        $beanMock->id = 'beanId01';
        
        $flowDataMock = array('cas_sugar_module'=>'Meetings');

        $module = 'Meetings';
        
        $result = $beanHandlerMock->getRelatedModule($beanMock, $flowDataMock, $module);
        $this->assertNull($result);
    }
    
    public function testGetRelationshipDataRelatedMultipleRecords()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieveBean', 'getRelationshipData'))
                ->getMock();
        
        $relationShipData = array('lhs_module'=>'Notes', 'rhs_module'=>'Meetings');
        
        $beanHandlerMock->expects($this->once())
                ->method('getRelationshipData')
                ->will($this->returnValue($relationShipData));
        
        $beanHandlerMock->setLogger($this->loggerMock);
        
        $relationshipMock = $this->getMockBuilder('Relationship')
                ->disableOriginalConstructor()
                ->setMethods(array('retrieve_by_sides'))
                ->getMock();
        
        $beanHandlerMock->expects($this->at(1))
                ->method('retrieveBean')
                ->will($this->returnValue($relationshipMock));
        
        $relatedBeanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array('get_full_list', 'retrieve_by_string_fields'))
                ->getMock();
        
        $relatedBeanList = array(
            (object)array('id'=>'relatedBean01'),
            (object)array('id'=>'relatedBean02'),
            (object)array('id'=>'relatedBean03')
        );
        
        $relatedBeanMock->expects($this->once())
                ->method('get_full_list')
                ->will($this->returnValue($relatedBeanList));
        
        $relatedBeanMock->id = 'relBeanId01';
        
        $beanHandlerMock->expects($this->at(2))
                ->method('retrieveBean')
                //->with('Meetings', $relatedBeanList[2])
                ->will($this->returnValue($relatedBeanMock));
        
        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array())
                ->getMock();
        $beanMock->id = 'beanId01';
        
        $flowDataMock = array('cas_sugar_module'=>'Meetings');

        $module = 'Meetings';
        
        $result = $beanHandlerMock->getRelatedModule($beanMock, $flowDataMock, $module);
        $this->assertNull($result);
    }
    
    public function testGetRelationshipData()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        
        $dbMock = $this->getMockBuilder('DBHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('Query', 'fetchByAssoc'))
                ->getMock();
        
        $dbMock->expects($this->once())
                ->method('Query');
        
        $dbMock->expects($this->once())
                ->method('fetchByAssoc');
        
        $relationName = 'leads_notes';
        
        $beanHandlerMock->getRelationshipData($relationName, $dbMock);
        
    }
    
    public function testMergeBeanInTemplate()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('parseString', 'mergeTemplate'))
                ->getMock();
        $bean = new stdClass();
        $bean->module_dir = 'PATH/TO/MODULE';
        $template = array();
        
        $beanHandlerMock->expects($this->once())
                ->method('parseString');
        $beanHandlerMock->expects($this->once())
                ->method('mergeTemplate');
        
        $beanHandlerMock->mergeBeanInTemplate($bean, $template);
    }
    
    public function testMergeTemplateNotEvaluated()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('get_href_link'))
                ->getMock();
        
        $beanHandlerMock->setLogger($this->loggerMock);
        $beanMock = new stdClass();
        $beanMock->module_dir = 'Leads';
        $beanMock->id = 'lead01';
        $beanMock->first_name = 'Alvin';
        $beanMock->last_name = 'Squirrel';
        $beanMock->link_field = 'linked_somewhere';
        
        $beanMock->fetched_row = array(
            'id'=>'lead01', 
            'first_name'=>'Alvin', 
            'last_name'=>'Squirrel', 
            'link_field'=>'linked_somewhere'
        );
        
        $beanMock->field_defs = array(
            'id' => array('type' => 'field', 'name'=>'id'), 
            'first_name' => array('type' => 'field', 'name'=>'first_name'), 
            'last_name' => array('type' => 'field', 'name'=>'last_name'), 
            'link_field' => array('type' => 'field', 'name'=>'link_field')
        );
        
        $beanMock->field_defs = array(
            'id' => array('type' => 'string', 'dbType' => 'varchar'), 
            'first_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'last_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'link_field' => array('type' => 'string', 'dbType' => 'varchar')
        );
        
        $template = "[]";
        $componentArray = array(
            'Leads' => array(
                'id'=>array('value_type'=>'past', 'name'=>'id', 'original' =>''), 
                'first_name'=>array('value_type'=>'future', 'name'=>'first_name', 'original' =>''), 
                'last_name'=>array('value_type'=>'future', 'name'=> 'last_name', 'original' =>''),
                'link_field'=>array('value_type'=>'href_link', 'name'=> 'link_field', 'original' =>'')
            )
        );
        
        $beanHandlerMock->mergeTemplate($beanMock, $template, $componentArray, false);
    }
    
    public function testMergeTemplateEvaluated()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('get_href_link'))
                ->getMock();
        
        $beanHandlerMock->setLogger($this->loggerMock);

        $beanMock = new stdClass();
        $beanMock->module_dir = 'Leads';
        $beanMock->id = 'lead01';
        $beanMock->first_name = 'Alvin';
        $beanMock->last_name = 'Squirrel';
        $beanMock->order = 1;
        $beanMock->link_field = 'linked_somewhere';
        
        $beanMock->fetched_row = array (
            'id'=>'lead01', 
            'first_name'=>'Alvin', 
            'last_name'=>'Squirrel', 
            'order'=>1, 
            'link_field'=>'linked_somewhere'
        );
        
        $beanMock->field_defs = array (
            'id' => array('type' => 'field', 'name'=>'id'), 
            'first_name' => array('type' => 'field', 'name'=>'first_name'), 
            'last_name' => array('type' => 'field', 'name'=>'last_name'), 
            'order' => array('type' => 'field', 'name'=>'order'), 
            'link_field' => array('type' => 'field', 'name'=>'link_field')
        );
        
        $beanMock->field_defs = array (
            'id' => array('type' => 'string', 'dbType' => 'varchar'), 
            'first_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'last_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'order' => array('type' => 'double', 'dbType' => 'double'), 
            'link_field' => array('type' => 'string', 'dbType' => 'varchar')
        );
        
        $template = "[]";
        $componentArray = array (
            'Leads' => array (
                'id'=>array('value_type'=>'past', 'name'=>'id', 'original' =>'id', 'db_type' => 'varchar'), 
                'first_name'=>array('value_type'=>'future', 'name'=>'first_name', 'original' =>'first_name', 'db_type' => 'varchar'), 
                'last_name'=>array('value_type'=>'future', 'name'=> 'last_name', 'original' =>'last_name', 'db_type' => 'varchar'),
                'order'=>array('value_type'=>'future', 'name'=> 'order', 'original' =>'order', 'db_type' => 'double'),
                'link_field'=>array('value_type'=>'href_link', 'name'=> 'link_field', 'original' =>'link_field', 'db_type' => 'varchar')
            )
        );
        
        $beanHandlerMock->mergeTemplate($beanMock, $template, $componentArray, true);
    }

    public function testMergeTemplateEvaluatedCurrency()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('get_href_link'))
                ->getMock();
        
        $beanHandlerMock->setLogger($this->loggerMock);

        $beanMock = new stdClass();
        $beanMock->module_dir = 'Leads';
        $beanMock->id = 'lead01';
        $beanMock->first_name = 'Alvin';
        $beanMock->last_name = 'Squirrel';
        $beanMock->order = 1;
        $beanMock->link_field = 'linked_somewhere';
        
        $beanMock->fetched_row = array (
            'id'=>'lead01', 
            'first_name'=>'Alvin', 
            'last_name'=>'Squirrel', 
            'order'=>1, 
            'link_field'=>'linked_somewhere'
        );
        
        $beanMock->field_defs = array (
            'id' => array('type' => 'field', 'name'=>'id'), 
            'first_name' => array('type' => 'field', 'name'=>'first_name'), 
            'last_name' => array('type' => 'field', 'name'=>'last_name'), 
            'order' => array('type' => 'field', 'name'=>'order'), 
            'link_field' => array('type' => 'field', 'name'=>'link_field')
        );
        
        $beanMock->field_defs = array (
            'id' => array('type' => 'string', 'dbType' => 'varchar'), 
            'first_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'last_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'order' => array('type' => 'currency', 'dbType' => 'double'), 
            'link_field' => array('type' => 'string', 'dbType' => 'varchar')
        );
        
        $template = "[]";
        $componentArray = array (
            'Leads' => array (
                'id'=>array('value_type'=>'past', 'name'=>'id', 'original' =>'id', 'db_type' => 'varchar'), 
                'first_name'=>array('value_type'=>'future', 'name'=>'first_name', 'original' =>'first_name', 'db_type' => 'varchar'), 
                'last_name'=>array('value_type'=>'future', 'name'=> 'last_name', 'original' =>'last_name', 'db_type' => 'varchar'),
                'order'=>array('value_type'=>'future', 'name'=> 'order', 'original' =>'order', 'db_type' => 'double'),
                'link_field'=>array('value_type'=>'href_link', 'name'=> 'link_field', 'original' =>'link_field', 'db_type' => 'varchar')
            )
        );

        $beanHandlerMock->mergeTemplate($beanMock, $template, $componentArray, true);
    }
    
    public function testMergeTemplateRelated()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('get_href_link'))
                ->getMock();
        
        $beanHandlerMock->setLogger($this->loggerMock);

        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array('call_relationship_handler'))
                ->getMock();
        
        $relHandlerMock = $this->getMockBuilder('RelationshipHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('build_related_list'))
                ->getMock();
        
        $beanMock->expects($this->once())
                ->method('call_relationship_handler')
                ->will($this->returnValue($relHandlerMock));
        
        $beanMock->module_dir = 'Leads';
        $beanMock->id = 'lead01';
        $beanMock->first_name = 'Alvin';
        $beanMock->last_name = 'Squirrel';
        $beanMock->order = 1;
        $beanMock->link_field = 'linked_somewhere';
        $beanMock->db = new stdClass();
        
        $beanMock->fetched_row = array (
            'id'=>'lead01', 
            'first_name'=>'Alvin', 
            'last_name'=>'Squirrel', 
            'order'=>1, 
            'link_field'=>'linked_somewhere'
        );
        
        $beanMock->field_defs = array (
            'id' => array('type' => 'field', 'name'=>'id'), 
            'first_name' => array('type' => 'field', 'name'=>'first_name'), 
            'last_name' => array('type' => 'field', 'name'=>'last_name'), 
            'order' => array('type' => 'field', 'name'=>'order'), 
            'link_field' => array('type' => 'field', 'name'=>'link_field'),
            'Notes' => array('type' => 'Notes', 'relationship'=>'')
        );
        
        $beanMock->field_defs = array (
            'id' => array('type' => 'string', 'dbType' => 'varchar'), 
            'first_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'last_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'order' => array('type' => 'currency', 'dbType' => 'double'), 
            'link_field' => array('type' => 'string', 'dbType' => 'varchar'),
            'Notes' => array('type' => 'Notes', 'dbType' => 'string')
        );
        
        $template = "[]";
        $componentArray = array (
            'Notes' => array (
                'id'=>array('value_type'=>'past', 'name'=>'id', 'original' =>'id', 'db_type' => 'varchar'), 
                'subject'=>array('value_type'=>'future', 'name'=>'subject', 'original' =>'subject', 'db_type' => 'varchar'), 
                'description'=>array('value_type'=>'future', 'name'=> 'description', 'original' =>'description', 'db_type' => 'varchar'),
            )
        );

        $beanHandlerMock->mergeTemplate($beanMock, $template, $componentArray, false);
    }


    public function testMergeTemplateRelationshipEmpty()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('get_href_link'))
                ->getMock();
        
        $beanHandlerMock->setLogger($this->loggerMock);

        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array('call_relationship_handler'))
                ->getMock();
        
        $relHandlerMock = $this->getMockBuilder('RelationshipHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('build_related_list', 'process_by_rel_bean'))
                ->getMock();
        
        $noteMock = new stdClass();
        $noteMock->module_dir = 'Notes';

        $noteMock->id = 'note01';
        $noteMock->subject = 'Note';
        $noteMock->description = 'Some description';
        $noteMock->field_defs = array (
            'id' => array('type' => 'field', 'name'=>'id'), 
            'subject' => array('type' => 'field', 'name'=>'subject'), 
            'description' => array('type' => 'field', 'name'=>'description'), 
        );
        
        $relListMock = array($noteMock);
        
        $relHandlerMock->expects($this->once())
                ->method('build_related_list')
                ->will($this->returnValue($relListMock));
        
        $beanMock->expects($this->once())
                ->method('call_relationship_handler')
                ->will($this->returnValue($relHandlerMock));

        $beanMock->module_dir = 'Leads';
        $beanMock->id = 'lead01';
        $beanMock->first_name = 'Alvin';
        $beanMock->last_name = 'Squirrel';
        $beanMock->order = 1;
        $beanMock->link_field = 'linked_somewhere';
        $beanMock->db = new stdClass();

        $beanMock->fetched_row = array (
            'id'=>'lead01', 
            'first_name'=>'Alvin', 
            'last_name'=>'Squirrel', 
            'order'=>1, 
            'link_field'=>'linked_somewhere'
        );

        $beanMock->field_defs = array (
            'id' => array('type' => 'field', 'name'=>'id'), 
            'first_name' => array('type' => 'field', 'name'=>'first_name'), 
            'last_name' => array('type' => 'field', 'name'=>'last_name'), 
            'order' => array('type' => 'field', 'name'=>'order'), 
            'link_field' => array('type' => 'field', 'name'=>'link_field'),
            'Notes' => array('type' => 'href_link', 'relationship'=>'leads_notes')
        );

        $beanMock->field_defs = array (
            'id' => array('type' => 'string', 'dbType' => 'varchar'), 
            'first_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'last_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'order' => array('type' => 'currency', 'dbType' => 'double'), 
            'link_field' => array('type' => 'string', 'dbType' => 'varchar'),
            'Notes' => array('type' => 'href_link', 'dbType' => 'string')
        );

        $template = "[]";
        $componentArray = array (
            'Notes' => array (
                'id'=>array('value_type'=>'past', 'name'=>'id', 'original' =>'id', 'db_type' => 'varchar'), 
                'subject'=>array('value_type'=>'future', 'name'=>'subject', 'original' =>'subject', 'db_type' => 'varchar'), 
                'description'=>array('value_type'=>'future', 'name'=> 'description', 'original' =>'description', 'db_type' => 'varchar'),
                'link'=>array('value_type'=>'href_link', 'name'=> 'link', 'original' =>'link', 'db_type' => 'varchar'),
            )
        );

        $beanHandlerMock->mergeTemplate($beanMock, $template, $componentArray, false);
    }

    public function testMergeTemplateIfModuleNotRelated()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('get_href_link'))
                ->getMock();
        
        $beanHandlerMock->setLogger($this->loggerMock);

        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(array('call_relationship_handler'))
                ->getMock();
        
        $relHandlerMock = $this->getMockBuilder('RelationshipHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('build_related_list', 'process_by_rel_bean'))
                ->getMock();
        
        $noteMock = new stdClass();
        $noteMock->module_dir = 'Notes';

        $noteMock->id = 'note01';
        $noteMock->subject = 'Note';
        $noteMock->description = 'Some description';
        $noteMock->field_defs = array (
            'id' => array('type' => 'field', 'name'=>'id'), 
            'subject' => array('type' => 'field', 'name'=>'subject'), 
            'description' => array('type' => 'field', 'name'=>'description'), 
        );
        
        $relListMock = array($noteMock);
        
        $relHandlerMock->expects($this->once())
                ->method('build_related_list')
                ->will($this->returnValue($relListMock));
        
        $beanMock->expects($this->once())
                ->method('call_relationship_handler')
                ->will($this->returnValue($relHandlerMock));

        $beanMock->module_dir = 'Leads';
        $beanMock->id = 'lead01';
        $beanMock->first_name = 'Alvin';
        $beanMock->last_name = 'Squirrel';
        $beanMock->order = 1;
        $beanMock->link_field = 'linked_somewhere';
        $beanMock->db = new stdClass();

        $beanMock->fetched_row = array (
            'id'=>'lead01', 
            'first_name'=>'Alvin', 
            'last_name'=>'Squirrel', 
            'order'=>1, 
            'link_field'=>'linked_somewhere'
        );

        $beanMock->field_defs = array (
            'id' => array('type' => 'field', 'name'=>'id'), 
            'first_name' => array('type' => 'field', 'name'=>'first_name'), 
            'last_name' => array('type' => 'field', 'name'=>'last_name'), 
            'order' => array('type' => 'field', 'name'=>'order'), 
            'link_field' => array('type' => 'field', 'name'=>'link_field'),
        );

        $beanMock->field_defs = array (
            'id' => array('type' => 'string', 'dbType' => 'varchar'), 
            'first_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'last_name' => array('type' => 'string', 'dbType' => 'varchar'), 
            'order' => array('type' => 'currency', 'dbType' => 'double'), 
            'link_field' => array('type' => 'string', 'dbType' => 'varchar'),
        );

        $template = "[]";
        $componentArray = array (
            'Notes' => array (
                'id'=>array('value_type'=>'past', 'name'=>'id', 'original' =>'id', 'db_type' => 'varchar'), 
                'subject'=>array('value_type'=>'future', 'name'=>'subject', 'original' =>'subject', 'db_type' => 'varchar'), 
                'description'=>array('value_type'=>'future', 'name'=> 'description', 'original' =>'description', 'db_type' => 'varchar'),
                'link'=>array('value_type'=>'href_link', 'name'=> 'link', 'original' =>'link', 'db_type' => 'varchar'),
            )
        );

        $beanHandlerMock->mergeTemplate($beanMock, $template, $componentArray, false);
    }
    
    public function testProcessValueExpression()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('get_href_link'))
                ->getMock();
        
        $expressionMock = array(
            (object)array('expSubtype'=>'INT', 'expType'=>'CONSTANT', 'expValue'=>2),
            (object)array('expSubtype'=>'FLOAT', 'expType'=>'CONSTANT', 'expValue'=>2.1),
            (object)array('expSubtype'=>'DOUBLE', 'expType'=>'CONSTANT', 'expValue'=>2.2),
            (object)array('expSubtype'=>'NUMBER', 'expType'=>'CONSTANT', 'expValue'=>2),
            (object)array('expSubtype'=>'BOOL', 'expType'=>'CONSTANT', 'expValue'=>false),
            (object)array('expSubtype'=>'STRING', 'expType'=>'CONSTANT', 'expValue'=>'some string'),
            (object)array('expSubtype'=>'STRING', 'expType'=>'VARIABLE', 'expValue'=>'subject'),
        );
        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        $beanMock->id = '1';
        $beanMock->subject = 'some subject';
        $beanMock->description = 'some description';
        $beanMock->order = 1;
        
        $evaluatorMock = $this->getMockBuilder('PMSEEvaluator')
                ->disableOriginalConstructor()
                ->setMethods(array('evaluateExpression'))
                ->getMock();
        
        $evaluatorMock->expects($this->once())
                ->method('evaluateExpression');
        
        $beanHandlerMock->setEvaluator($evaluatorMock);
        
        $beanHandlerMock->processValueExpression($expressionMock, $beanMock);
        
    }
    
    public function testProcessValueExpressionSingleExpression()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
                ->disableOriginalConstructor()
                ->setMethods(array('get_href_link'))
                ->getMock();
        
        $expressionMock = array(
            (object)array('expType'=>'CONSTANT', 'expSubtype'=>'int', 'expValue'=>2),
        );
        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        $beanMock->id = '1';
        $beanMock->subject = 'some subject';
        $beanMock->description = 'some description';
        $beanMock->order = 1;
        
        $evaluatorMock = $this->getMockBuilder('PMSEEvaluator')
                ->disableOriginalConstructor()
                ->setMethods(array('evaluateExpression'))
                ->getMock();
        
        
        
        $beanHandlerMock->setEvaluator($evaluatorMock);
        
        $result = $beanHandlerMock->processValueExpression($expressionMock, $beanMock);
        
        $this->assertEquals(2, $result);
    }
    
    public function testParseStringNormal()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('get_href_link'))
            ->getMock();
        
        $template = "{::future::Notes::subject::}=='Some Subject'";
        $baseModule = "Notes";
        $beanHandlerMock->parseString($template, $baseModule);
    }
    
    public function testParseStringSimple()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('get_href_link'))
            ->getMock();
        
        $template = "{::Notes::subject::}=='Some Subject'";
        $baseModule = "Notes";
        $beanHandlerMock->parseString($template, $baseModule);
    }
    
    public function testParseStringWithRelatedModule()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('get_href_link'))
            ->getMock();
        
        $template = "{::future::Notes::Meetings::subject::}=='Some Subject'";
        $baseModule = "Notes";
        $beanHandlerMock->parseString($template, $baseModule);
    }
 
    public function testCalculateDueDate()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('get_href_link'))
            ->getMock();
        
        $expressionMock = array(
            (object)array('expType'=>'FIXED_DATE', 'expValue'=>'2012/12/01'),
            (object)array('expType'=>'SUGAR_DATE', 'expValue'=>'created_date'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'minutes'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'hours'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'days'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'months'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'years'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'arbitrary_measure'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"-", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'minutes'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"-", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'hours'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"-", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'days'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"-", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'months'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"-", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'years'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"-", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'arbitrary_measure')
        );

        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();

        $beanMock->id = '1';
        $beanMock->subject = 'some subject';
        $beanMock->description = 'some description';
        $beanMock->created_date = '2012/12/01';
        $beanMock->order = 1;
        
        $expectedTime = date("Y-m-d H:i:s", strtotime("+10 seconds", strtotime($beanMock->created_date)));
        
        $result = $beanHandlerMock->calculateDueDate($expressionMock, $beanMock);
        $this->assertEquals($expectedTime, $result[1]);
    }
    
    public function testCalculateDueDateNoOperators()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('get_href_link'))
            ->getMock();
        
        $expressionMock = array(
            (object)array('expType'=>'FIXED_DATE', 'expValue'=>'2012/12/01'),
            (object)array('expType'=>'SUGAR_DATE', 'expValue'=>'created_date'),
        );

        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();

        $beanMock->id = '1';
        $beanMock->subject = 'some subject';
        $beanMock->description = 'some description';
        $beanMock->created_date = '2012/12/01';
        $beanMock->order = 1;
        
        $expectedTime = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($beanMock->created_date)));
        
        $result = $beanHandlerMock->calculateDueDate($expressionMock, $beanMock);
        $this->assertEquals($expectedTime, $result[1]);
    }
    
    public function testCalculateDueDateNegativeDate()
    {
        $beanHandlerMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('get_href_link'))
            ->getMock();
        
        $expressionMock = array(
            (object)array('expType'=>'FIXED_DATE', 'expValue'=>'2012/12/01'),
            (object)array('expType'=>'SUGAR_DATE', 'expValue'=>'created_date'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'minutes'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'hours'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'days'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'months'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'years'),
            (object)array('expType'=>'OPERATOR', 'expValue'=>"+", 'expUnit'=>''),
            (object)array('expType'=>'UNIT_TIME', 'expValue'=>1, 'expUnit'=>'arbitrary_measure')
        );

        $beanMock = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();

        $beanMock->id = '1';
        $beanMock->subject = 'some subject';
        $beanMock->description = 'some description';
        $beanMock->created_date = '2012/12/01';
        $beanMock->order = 1;
        
        $expectedTime = date("Y-m-d H:i:s", strtotime("+1 minute +1 hour +1 year +1 month +1 day", strtotime($beanMock->created_date)));
        
        $result = $beanHandlerMock->calculateDueDate($expressionMock, $beanMock);
        $this->assertEquals($expectedTime, $result[1]);
    }

}
