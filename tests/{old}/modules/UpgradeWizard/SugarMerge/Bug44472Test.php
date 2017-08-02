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
require_once 'include/dir_inc.php';

class Bug44472Test extends Sugar_PHPUnit_Framework_TestCase  {

function setUp() {
   SugarTestMergeUtilities::setupFiles(array('Cases'), array('editviewdefs'), 'tests/{old}/modules/UpgradeWizard/SugarMerge/od_metadata_files/610');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}


function test620TemplateMetaMergeOnCases() 
{		
   $this->merge = new EditViewMerge();	
   $this->merge->merge('Cases', 'tests/{old}/modules/UpgradeWizard/SugarMerge/od_metadata_files/610/oob/modules/Cases/metadata/editviewdefs.php', 'modules/Cases/metadata/editviewdefs.php', 'custom/modules/Cases/metadata/editviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Cases/metadata/editviewdefs.php.suback.php'));
   require('custom/modules/Cases/metadata/editviewdefs.php');
   $this->assertFalse(isset($viewdefs['Cases']['EditView']['templateMeta']['form']), 'Assert that the templateMeta is pulled from the upgraded view rather than the customized view');
}

function test620TemplateMetaMergeOnMeetings() 
{		
   $this->merge = new EditViewMergeMock();	
   $this->merge->setModule('Meetings');
   $data = array();
   $data['Meetings'] = array('EditView'=>array('templateMeta'=>array('form')));
   $this->merge->setCustomData($data);
   $newData = array();
   $newData['Meetings'] = array('EditView'=>array('templateMeta'=>array()));
   $this->merge->setNewData($newData);
   $this->merge->testMergeTemplateMeta();
   $newData = $this->merge->getNewData();   
   $this->assertTrue(!isset($newData['Meetings']['EditView']['templateMeta']['form']), 'Assert that we do not take customized templateMeta section for Meetings');
}

function test620TemplateMetaMergeOnCalls() 
{		
   $this->merge = new EditViewMergeMock();	
   $this->merge->setModule('Calls');
   $data = array();
   $data['Calls'] = array('EditView'=>array('templateMeta'=>array('form')));
   $this->merge->setCustomData($data);   
   $newData = array();
   $newData['Calls'] = array('EditView'=>array('templateMeta'=>array()));
   $this->merge->setNewData($newData);
   $this->merge->testMergeTemplateMeta();
   
   $newData = $this->merge->getNewData();
   $this->assertTrue(!isset($newData['Calls']['EditView']['templateMeta']['form']), 'Assert that we do not take customized templateMeta section for Calls');
}

}

class EditViewMergeMock extends EditViewMerge
{
    function setModule($module)
    {
    	$this->module = $module;
    }
    
    function setCustomData($data)
    {
        $this->customData = $data;	
    }
    
    function setNewData($data)
    {
    	$this->newData = $data;
    }
    
    function getNewData()
    {
    	return $this->newData;
    }
    
    function testMergeTemplateMeta()
    {
    	$this->mergeTemplateMeta();
    }
}

?>