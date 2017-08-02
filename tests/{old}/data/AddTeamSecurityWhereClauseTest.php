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


class AddTeamSecurityWhereClauseTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
	{
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        parent::setUp();
	}

	public function tearDown()
	{
        SugarTestHelper::tearDown();
        parent::tearDown();
	}

	public function testAddTeamSecurityWhereClauseForRegularUser()
	{
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = false;
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);
        $query = preg_replace("/[\t \n]+/", " ", $query);

        $this->assertContains(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id ",
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForRegularUserSpecifyTableAlias()
	{
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = false;
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query,'myfoo');
        $query = preg_replace("/[\t \n]+/", " ", $query);
        $this->assertContains(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_membershipsmyfoo ON tst.team_id = team_membershipsmyfoo.team_id AND team_membershipsmyfoo.user_id = '{$GLOBALS['current_user']->id}' AND team_membershipsmyfoo.deleted=0 group by tst.team_set_id) myfoo_tf on myfoo_tf.team_set_id = myfoo.team_set_id ",
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForRegularUserSpecifyJoinType()
	{
	    $this->markTestIncomplete("Unused functionality");
	    $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = false;
        $query = '';

        $bean->add_team_security_where_clause($query,'','LEFT OUTER');
        $query = preg_replace("/[\t \n]+/", " ", $query);

        $this->assertContains(
            "LEFT OUTER JOIN (select tst.team_set_id from team_sets_teams tst LEFT OUTER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id ",
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForRegularUserWithJoinTeamsParameterTrue()
	{
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = false;
        $query = '';
        $bean->addVisibilityStrategy('TeamSecurity');

        $bean->add_team_security_where_clause($query,'','INNER',false,true);
        $query = preg_replace("/[\t \n]+/", " ", $query);
        $this->assertContains(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id INNER JOIN teams ON teams.id = team_memberships.team_id AND teams.deleted=0 ",
            $query
        );
    }

    public function testAddTeamSecurityWhereClauseWhenRowLevelSecurityIsDisabled()
	{
	    $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = true;
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseWhenModuleIsWorkflow()
	{
	    $bean = new SugarBean();
        $bean->module_dir = 'WorkFlow';
        $bean->table_name = 'workflow';
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForAdmin()
	{
	    $GLOBALS['current_user']->is_admin = 1;

        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForAdminWhenForceAdminIsTrue()
	{
	    $this->markTestIncomplete("Unused functionality");
	    $GLOBALS['current_user']->is_admin = 1;

        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->addVisibilityStrategy('TeamSecurity');
        $bean->disable_row_level_security = false;
        $query = '';

        $bean->add_team_security_where_clause($query,'','INNER',true);
        $query = preg_replace("/[\t \n]+/", " ", $query);

        $this->assertContains(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id ",
            $query
            );
    }

    /**
     * @ticket 26772
     */
	public function testAddTeamSecurityWhereClauseForAdminForModule()
	{
        global $current_user;

        /** @var User|PHPUnit_Framework_MockObject_MockObject $current_user */
        $current_user = $this->createPartialMock('User', array('isAdminForModule'));
        $current_user->expects($this->atLeastOnce())
            ->method('isAdminForModule')
            ->with('Foo')
            ->willReturn(true);

        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $query = '';
        $bean->addVisibilityStrategy('TeamSecurity');

        $bean->add_team_security_where_clause($query);

        $this->assertEquals('', $query);
    }
}
