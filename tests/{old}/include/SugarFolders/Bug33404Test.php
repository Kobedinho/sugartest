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
class Bug33404Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $folder = null;
    var $_user = null;
    
    
	public function setUp()
    {
        global $current_user;
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $current_usr = $this->_user;
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->_user->id}'");
		$this->folder = new SugarFolder(); 
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->_user->id}'");
        unset($this->folder);
    }
    
	function testInsertFolderSubscription(){
	    global $current_user;
	   
	    $id1 = create_guid();
	    $id2 = create_guid();
	    
	    $this->folder->insertFolderSubscription($id1,$this->_user->id);
	    $this->folder->insertFolderSubscription($id2,$this->_user->id);
	    
	    $result = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where assigned_user_id='{$this->_user->id}'");
		$rs = $GLOBALS['db']->fetchByAssoc($result);
		
		$this->assertEquals(2, $rs['cnt'], "Could not insert folder subscriptions properly" );
    }
    
    
    
    function testClearSubscriptionsForFolder()
    {
        global $current_user;
	   
        $random_user_id1 = create_guid();
        $random_user_id2 = create_guid();
        $random_user_id3 = create_guid();
        
	    $folderID = create_guid();
	    
	    $this->folder->insertFolderSubscription($folderID,$random_user_id1);
        $this->folder->insertFolderSubscription($folderID,$random_user_id2);
        $this->folder->insertFolderSubscription($folderID,$random_user_id3);
	    
        $result1 = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where folder_id='{$folderID}' ");
		$rs1 = $GLOBALS['db']->fetchByAssoc($result1);
        $this->assertEquals(3, $rs1['cnt'], "Could not clear folder subscriptions, test setup failed while inserting folder subscriptionss");
        
        //Test deletion of subscriptions.
        $this->folder->clearSubscriptionsForFolder($folderID);
	    $result = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where folder_id='{$folderID}' ");
		$rs = $GLOBALS['db']->fetchByAssoc($result);
	 
		$this->assertEquals(0, $rs['cnt'], "Could not clear folder subscriptions");
    }
}
?>