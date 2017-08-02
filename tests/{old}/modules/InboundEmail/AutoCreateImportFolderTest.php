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
 

/**
 * @ticket 33404
 */
class AutoCreateImportFolderTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $folder_id = null;
	var $folder_obj = null;
	var $ie = null;
    var $_user = null;
    
    
	public function setUp()
    {
        global $current_user, $currentModule;

        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        
		$this->folder = new SugarFolder(); 
		$this->ie = new InboundEmail();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        
        $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$this->folder_id}'");
        
        unset($this->ie);
    }
    
	function testAutoImportFolderCreation(){
	    global $current_user;
	   
    	$this->ie->name = "Sugar Test";
    	$this->ie->team_id = create_guid();
    	$this->ie->team_set_id = create_guid();
    	$this->folder_id = $this->ie->createAutoImportSugarFolder();
	    $this->folder_obj = new SugarFolder();
	    $this->folder_obj->retrieve($this->folder_id);
		
		$this->assertEquals($this->ie->name, $this->folder_obj->name, "Could not create folder for Inbound Email auto folder creation" );
    	$this->assertEquals($this->ie->team_id, $this->folder_obj->team_id, "Could not create folder for Inbound Email auto folder creation" );
        $this->assertEquals($this->ie->team_set_id, $this->folder_obj->team_set_id, "Could not create folder for Inbound Email auto folder creation" );
    	$this->assertEquals(0, $this->folder_obj->has_child, "Could not create folder for Inbound Email auto folder creation" );
        $this->assertEquals(1, $this->folder_obj->is_group, "Could not create folder for Inbound Email auto folder creation" );
        $this->assertEquals($this->_user->id, $this->folder_obj->assign_to_id, "Could not create folder for Inbound Email auto folder creation" );
        
	}
}
?>