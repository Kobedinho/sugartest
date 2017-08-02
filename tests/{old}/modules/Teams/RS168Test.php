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
 * RS168: Prepare Teams Module.
 */
class RS168Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testRetrieves()
    {
        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $user = SugarTestUserUtilities::createAnonymousUser(true, false);
        $id = $team->retrieve_team_id($team->name);
        $this->assertEquals($team->id, $id);
        $users = $team->get_team_members();
        $this->assertEmpty($users);
        $teams = $team->getArrayAllAvailable(false, $user);
        $this->assertGreaterThan(2, $teams);
    }

    public function testScans()
    {
        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $user2 = SugarTestUserUtilities::createAnonymousUser(true, false);

        $user1 = SugarTestUserUtilities::createAnonymousUser(false, false);
        $user1->reports_to_id = $user2->id;
        $user1->save(false);

        $result = $team->scan_direct_reports_for_access($user2->id);
        $this->assertTrue($result);
        DBManagerFactory::getInstance()->commit();
        $result = $team->scan_direct_reports_team_for_access($user1->id);
        $this->assertFalse($result);
    }

    public function testReassign()
    {
        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2->reassign_team_records(array($team1->id));
        $team = BeanFactory::newBean('Teams');
        $team->retrieve($team1->id, true, false);
        $this->assertEquals(1, $team->deleted);
    }
}
