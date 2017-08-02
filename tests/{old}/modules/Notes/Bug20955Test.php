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

require_once "modules/Bugs/Bug.php";

/**
 * @ticket 20955
 */
class Bug20955Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;
	public $_team = null;

	public function setUp()
    {
		global $current_user;
		$time = date($GLOBALS['timedate']->get_db_date_time_format());

		$this->_team = SugarTestTeamUtilities::createAnonymousTeam();

		$this->_user = SugarTestUserUtilities::createAnonymousUser();//new User();
		$this->_user->first_name = "leon";
		$this->_user->last_name = "zhang";
		$this->_user->user_name = "leon zhang";
		$this->_user->default_team=$this->_team->id;
		$this->_user->save();
		$current_user=$this->_user;
	}

	public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
	}

	public function testDisabledNewNoteDefaultTeam()
    {
		global $current_user;
		$temp_note=new Note();
		$temp_note->save();
		return $this->assertEquals($temp_note->team_id, $current_user->default_team, "The note default team is not the current user's default team! ");
	}

	public function testDisabledNewTaskDefaultTeam()
    {
		global $current_user;
		$temp_task=new Task();
		$temp_task->save();
		return $this->assertEquals($temp_task->team_id,$current_user->default_team, "The task default team is not the current user's default team! ");
	}

	public function testDisabledNewBugDefaultTeam()
    {
		global $current_user;
		$temp_bug=new Bug();
		$temp_bug->save();
		return $this->assertEquals($temp_bug->team_id,$current_user->default_team, "The bug default team is not the current user's default team! ");
	}

	public function testDisabledNewCampaignDefaultTeam() 
    {
		global $current_user;
        $timedate = TimeDate::getInstance();
		$temp_campaign=new Campaign();
		$temp_campaign->end_date = $timedate->nowDbDate();
        $temp_campaign->save();
		return $this->assertEquals($temp_campaign->team_id,$current_user->default_team, "The campaign default team is not the current user's default team! ");
	}
}

