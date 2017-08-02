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
 * Test cases for the Team object
 */
class TeamsRemovalTest extends Sugar_PHPUnit_Framework_TestCase
{

	var $_user = null;    
	var $_contact = null;
	var $_contact2 = null;
    var $_soapClient = null;
    var $_sessionId = null;
    var $_teamSetId = null;
    var $_contactId = null;
    var $_contact2Id = null;
    
    //Team A
    var $_teamA = null;
    var $_teamAId = null;
    
    //Team B
    var $_teamB = null;
    var $_teamBId = null;


	function setUp() 
	{
	    $this->markTestIncomplete("Skipping unless otherwise specified");

        global $beanList, $beanFiles, $moduleList;
        require('include/modules.php');		
		
		$this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['current_user'] = $this->_user;

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->contacts_users_id = $this->_user->id;
        $this->_contactId = $this->_contact->save();

        $this->_contact2 = SugarTestContactUtilities::createContact();
        $this->_contact2->contacts_users_id = $this->_user->id;
        $this->_contact2Id = $this->_contact2->save();        
        
        $this->_teamA = BeanFactory::newBean('Teams');
        $this->_teamA->name = 'Team A';
        $this->_teamAId = $this->_teamA->save();
        
        $this->_teamB = BeanFactory::newBean('Teams');
        $this->_teamB->name = 'Team B';
        $this->_teamBId = $this->_teamB->save();        
    }    
    
    function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $this->_user = null;
        
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        $this->_contact = null;
        $this->_contact2 = null;   
        
        if ( $this->_teamA instanceOf Team ) {
            $this->_teamA->delete_team();
            $this->_teamA = null;
        }
        if ( $this->_teamB instanceOf Team ) {
            $this->_teamB->delete_team();
            $this->_teamB = null;     
        }

		$user = BeanFactory::getBean('Users', '1');
		$GLOBALS['current_user'] = $user;        
    } 

    function test_disabled_reassignment() {
    	$this->_contact->load_relationship('teams');
    	$this->_contact->teams->add(array($this->_teamAId));
		
    	//Flush the cache, write to team_sets_modules
		TeamSetManager::save();

		//Standard checks...
    	$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$this->_contact->team_set_id}'");     
    	$this->assertTrue(!empty($result));
		$row = $GLOBALS['db']->fetchByAssoc($result);
		//Global, Team A
		$this->assertEquals($row['total'], 2);  

		//This will prompt the reassignment
    	$this->assertTrue($this->_teamA->has_records_in_modules());
    	
    	$this->_contact2->load_relationship('teams');
    	$this->_contact2->teams->add(array($this->_teamAId, $this->_teamBId));

    	//Flush the cache, write to team_sets_modules
		TeamSetManager::save();    	
    	
		//Standard checks...
    	$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$this->_contact2->team_set_id}'");     
    	$this->assertTrue(!empty($result));
		$row = $GLOBALS['db']->fetchByAssoc($result);
		//Global, Team A, Team B
		$this->assertEquals($row['total'], 3);  
		
		//This will prompt the reassignment
    	$this->assertTrue($this->_teamB->has_records_in_modules());

    	$this->_teamA->reassign_team_records(array($this->_teamBId));
    	
    	//The team_set_id of $this->contact2 should now be equal to team_set_id of $this->_contact
    	$result = $GLOBALS['db']->query("SELECT team_set_id FROM CONTACTS WHERE id = '{$this->_contact2->id}'");
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	
    	$this->assertTrue($this->_contact->team_set_id == $row['team_set_id']);
    }
}

?>
