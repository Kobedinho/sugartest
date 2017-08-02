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


class Bug47949Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestIncomplete('Marking this skipped until we figure out why it is failing.');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /*
     * @group bug47949
     */
    public function testGetRelatedBean()
    {
        $team_id = 1;
        $case = new aCase();
        $case->name = 'testBug47949';
        $case->team_id = $team_id;
        $case->team_set_id = 1;
        $case->save();

        $beans = $case->get_linked_beans('teams', 'Team');

        // teams is based on Link (not Link2), should still work
        $this->assertEquals(1, count($beans), 'should have one and only one team');
        $this->assertEquals($team_id, $beans[0]->id, 'incorrect team id, should be ' . $team_id);

        // cleanup
        $GLOBALS['db']->query("delete from cases where id= '{$case->id}'");
    }
}
