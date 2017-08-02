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
 
require_once('vendor/nusoap//nusoap.php');
require_once('SugarTestUserUtilities.php');
require_once('tests/{old}/SugarTestContactUtilities.php');

/**
 * Test cases for the Team object
 */
class TeamsRelationshipsTest extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;    
	public $_contact = null;
    public $_soapClient = null;
    public $_sessionId = null;
    public $_teamSetId = null;
    public $_contactId = null;
   
	public function setUp() 
    {
    	$this->markTestIncomplete(
              'fixing this test'
            );
		require('include/modules.php');
		$this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
       	$this->_user->save();
        $this->_user->setPreference('timezone', 'Africa/Douala', 0, 'global');
        $GLOBALS['current_user'] = $this->_user;

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->contacts_users_id = $this->_user->id;
        $this->_contactId = $this->_contact->save();
        
        // clear module cache prior to this test
        //if ( is_dir(dirname(__FILE__).'/../../../cache/modules/Teams/') )
        //    rmdir_recursive(dirname(__FILE__).'/../../../cache/modules/Teams/');
    }    
    
    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $this->_user = null;
        
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        $this->_contact = null;   
    }     
    
    public function testSoapRelationshipCalls() {
    	$soap_url = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
        $this->_soapClient = new nusoapclient($soap_url);      
    	
        $result = $this->_soapClient->call('login',
            array('user_auth' => 
                array('user_name' => $this->_user->user_name,
                      'password' => $this->_user->user_hash,
                      'timezone' => 'Africa/Douala',
                      'version' => '.01'), 
                      'application_name' => 'SoapTest')
            );
            
        $this->_sessionId = $result['id'];    
		$result = $this->_soapClient->call('set_relationship',array('session'=>$this->_sessionId,'module_name'=>'Contacts', 'module_id'=>$this->_contactId, 'link_field_name' => 'teams', 'related_ids' => array('1', 'East', 'West', $this->_contact->team_id)));
		$this->assertEquals($result['created'],1,'Incorrect number of results returned. HTTP Response: '.$this->_soapClient->response); 
		
		$contact = BeanFactory::newBean('Contacts');
		$contact = $contact->retrieve($this->_contactId);
		
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	
		$this->assertEquals($row['total'], 4); 
		
	    $result = $this->_soapClient->call('get_relationships',array('session'=>$this->_sessionId,'module_name'=>'Contacts', 'module_id'=>$this->_contactId, 'link_field_name'=>'teams', 'related_module_query'=>'', 'related_fields' => array('name', 'id'), 'related_module_link_name_to_fields_array' => '' ));
	    $this->assertTrue(!empty($result),'Results not returned. HTTP Response: '.$this->_soapClient->response); 
	    $this->assertEquals(count($result['entry_list']), 4);
    }
   
    public function testRegularRelationshipCalls() {
    	$contact = BeanFactory::newBean('Contacts');
    	$contact = $contact->retrieve($this->_contactId);
	    $teams = array('East', 'West', '1');
		$contact->load_relationship('teams');
		$contact->teams->add($teams);
    	$contact->load_relationship('teams');
    	$contact->teams->add(array('1', 'East', 'West', $contact->team_id));
    	
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($row['total'], 4, "Total number of teams in contact record is 4");
		
		$teams = array('East', 'West');
		$contact->load_relationship('teams');
		$contact->teams->remove($teams);
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	$this->assertEquals($row['total'], 2, "Total number of teams in contact record is 1 after removing East and West teams");

    	$teams = array('East', 'West');
    	$contact->load_relationship('teams');
    	$contact->teams->replace($teams);
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	$this->assertEquals($row['total'], 2, "Total number of teams in contact record is 2 after replacing with East and West teams");
    }
   
    public function testDeleteRelationshipCall(){
    	$contact = BeanFactory::newBean('Contacts');
    	$contact = $contact->retrieve($this->_contactId);
    	$contact->load_relationship('teams');
		$contact->teams->replace(array('East', 'West', $contact->team_id));
    	$contact->load_relationship('teams');
    	$contact->teams->delete($contact->id, 'West');
    	$contact->retrieve($contact->id);
    	
    	$teamSet = BeanFactory::newBean('TeamSets');
    	$team_ids = $teamSet->getTeamIds($contact->team_set_id);

    	$this->assertNotContains('West', $team_ids, 'The list of associated teams still contains West.');
    	//TODO come back to fix this test
    	//$this->assertContains('East', $team_ids, 'The list of associated teams does not contain East.');
    }
    
   
}

?>
