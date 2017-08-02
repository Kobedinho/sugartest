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
class TeamMembershipTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $_users = array();
    var $_original_path = null;

    public function setUp() 
    {
    	//TODO fix this test
    	 $this->markTestIncomplete(
              'Need to ensure proper cleanup first.'
            );
        $time = date($GLOBALS['timedate']->get_db_date_time_format());

        $users = array('A', 'B', 'C');
        foreach ($users as $user) {
            $this->_users[$user] = SugarTestUserUtilities::createAnonymousUser();
            $this->_users[$user]->first_name = $user;
            $this->_users[$user]->last_name = $time;
            $this->_users[$user]->user_name = $user . $time;
            $this->_users[$user]->save();
        }
    }

    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function isUserPartOfTeam($user, $team_name, $explicit) 
    {
        $teamFactory = BeanFactory::newBean('Teams');
        $team = $teamFactory->retrieve($teamFactory->retrieve_team_id($team_name));
        
        $teamMembership = BeanFactory::newBean('TeamMemberships');
        $teamMembership->retrieve_by_user_and_team($user->id, $team->id);
		
        return $explicit ? $teamMembership->explicit_assign == 1 : $teamMembership->implicit_assign == 1;
    }


    protected function isUserExplicitlyPartOfTeam($user, $team_name) 
    {
        return $this->isUserPartOfTeam($user, $team_name, true);
    }

    protected function isUserImplicitlyPartOfTeam($user, $team_name) 
    {
        return $this->isUserPartOfTeam($user, $team_name, false);
    }

    protected function isUserExplicitlyPartOfGlobalTeam($user) 
    {
        return $this->isUserExplicitlyPartOfTeam($user, 'Global');
    }

    protected function assertUserExplicitlyPartOfTeam($user, $team) 
    {
        $this->assertTrue(
            $this->isUserExplicitlyPartOfTeam($user, $team),
            "User {$user->first_name} is explicitly part of team {$team}"
        );
    }

    protected function assertUserImplicitlyPartOfTeam($user, $team) 
    {
    	$this->assertTrue(
            $this->isUserImplicitlyPartOfTeam($user, $team),
            "User {$user->first_name} is implicitly part of team {$team}"
        );
    }

    protected function assertUserNotImplicitlyPartOfTeam($user, $team) 
    {
        $this->assertFalse(
            $this->isUserImplicitlyPartOfTeam($user, $team),
            "User {$user->first_name} is not implicitly part of team {$team}"
        );
    }

    protected function _userAReportsToUserB() 
    {
        $this->_users['A']->reports_to_id = $this->_users['B']->id;
        $this->_users['A']->save();
        $this->_users['A']->update_team_memberships('');
    }

    protected function _userBReportsToUserC() 
    {
        $this->_users['B']->reports_to_id = $this->_users['C']->id;
        $this->_users['B']->save();
        $this->_users['B']->update_team_memberships('');
    }

    public function testDisabledBaseUserAssumedPartOfOwnTeamAndGlobal() 
    {
        foreach ($this->_users as $user) {
            $this->assertUserExplicitlyPartOfTeam($user, $user->first_name);
            $this->assertUserExplicitlyPartOfTeam($user, 'Global');
        }
    }

    public function testDisabledUserBImplicitlyPartOfUserATeamIfUserAReportsToB() 
    {
        $this->_userAReportsToUserB();
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], $this->_users['A']->first_name);
    }
    
    public function testDisabledUserBImplicitlyAndExplicitlyPartOfGlobalTeamIfUserAReportsToB() 
    {
        $this->_userAReportsToUserB();
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], 'Global');
        $this->assertUserExplicitlyPartOfTeam($this->_users['B'], 'Global');
    }

    public function testDisabledUserCImplicitlyPartOfAllOfUserBTeams() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $this->_users['A']->first_name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $this->_users['B']->first_name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], 'Global');
    }

    public function testDisabledAnyTeamsThatHaveAUserAddedThemRippleImplicitlyToAllUserThatAddUserImplicitlyReportsTo() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['A']);
        
        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);
    }

    public function testDisabledTeamRippleWithExplicitAdd() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['A']);
        $team->add_user_to_team($this->_users['C']->id, $this->_users['C']);

        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);
        $this->assertUserExplicitlyPartOfTeam($this->_users['C'], $team->name);
    }

    public function testDisabledUsersCanBeAddedToTeamWithJustUserId() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id);

        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
    }

    public function testDisabledUserIdGivenPriorityIfProvidedUserDoesNotMatch() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['B']);
        
        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
    }

    public function testDisabledWhenReportsToIsChangedImplicitMembershipRipple() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['A']);
        
        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);

        $old_boss = $this->_users['A']->reports_to_id;
        $this->_users['A']->reports_to_id = '';
        $this->_users['A']->save();
        $this->_users['A']->update_team_memberships($old_boss);

        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['C'], $team->name);
    }

    public function testDisabledChangingWhoAUserReportsToUpdatesWhoIsImplicitlyPartOfTheirTeam() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['A']);
        
        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);

        $old_boss = $this->_users['A']->reports_to_id;
        $this->_users['A']->reports_to_id = '';
        $this->_users['A']->save();
        $this->_users['A']->update_team_memberships($old_boss);

        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['C'], $team->name);

        $this->_users['A']->reports_to_id = $this->_users['C']->id;
        $this->_users['A']->save();
        $this->_users['A']->update_team_memberships('');

        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);
    }

}
?>
