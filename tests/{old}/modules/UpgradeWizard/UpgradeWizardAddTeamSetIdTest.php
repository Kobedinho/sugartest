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
class UpgradeWizardAddTeamSetIdTest extends Sugar_PHPUnit_Framework_TestCase  {

var $skipTest = true;
var $module = 'Contacts'; //Just do this for Contacts module for now
var $team_set_ids = array();
var $team_ids = array();

function setUp() 

{
	
    if($this->skipTest) {
       $this->markTestSkipped("Skipping unless otherwise specified");
    }	
	
	$this->team_set_ids = array();
	$this->team_ids = array();	
	
	$result = $GLOBALS['db']->query("SELECT id, team_set_id from {$this->module}");
	while($row = $GLOBALS['db']->fetchByAssoc($result)) {
		  $this->team_set_ids[$row['id']] = $row['team_set_id'];
	}
	
	//$GLOBALS['db']->query("UPDATE {$this->module} SET team_set_id = NULL");
	
	//Delete the teams_sets and team_sets_teams entry with only one team
    $result = $GLOBALS['db']->query("SELECT id FROM teams");
	while($row = $GLOBALS['db']->fetchByAssoc($result)) {
		  $this->team_ids[$row['id']] = $row['id'];
	}

	foreach($this->team_ids as $id) {
	      $GLOBALS['db']->query("DELETE FROM team_sets_teams WHERE team_set_id = '{$id}'");
	      $GLOBALS['db']->query("DELETE FROM team_sets WHERE id = '{$id}'");
	}
	
	$bean = BeanFactory::newBean($this->module);
	$GLOBALS['db']->deleteColumn($bean, $bean->field_defs['team_set_id']);
}

function tearDown() {
	foreach($this->team_set_ids as $id=>$team_set_id) {
		    $GLOBALS['db']->query("UPDATE {$this->module} SET team_set_id = '{$team_set_id}' WHERE id = '{$id}'");
	}
}

function test_add_teamsetid() {		
	$result = $GLOBALS['db']->query("SELECT count(team_id) as total from {$this->module}");
    $row = $GLOBALS['db']->fetchByAssoc($result);
    $contact_total = $row['total']; 
    
    $FieldArray = $GLOBALS['db']->helper->get_columns($this->module);
    $this->assertTrue(!isset($FieldArray['team_set_id']), "Assert that team_set_id column was removed");
    
	require_once('modules/UpgradeWizard/uw_utils.php');	
	$filter = array($this->module);	
	upgradeModulesForTeamsets($filter);
		
    $result = $GLOBALS['db']->query("SELECT count(team_id) as total from {$this->module} where team_id = team_set_id");
    $row = $GLOBALS['db']->fetchByAssoc($result);
    $contact_total2 = $row['total'];
    $this->assertTrue($contact_total == $contact_total2); 

    $FieldArray = $GLOBALS['db']->helper->get_columns($this->module);
    $this->assertTrue(isset($FieldArray['team_set_id']), "Assert that team_set_id column was created");
}


}

?>
