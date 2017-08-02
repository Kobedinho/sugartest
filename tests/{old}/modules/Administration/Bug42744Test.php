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
 * Bug 42744:
 *  if team 1 is deleted=1 then upgrade from 5.2.0k > 5.5.1 fails
 * @ticket 42744
 * @author arymarchik@sugarcrm.com
 */
class Bug42744Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        parent::setUp();
    }

    /**
     * Testing repairing of Global Team
     * @group 42744
     * @outputBuffering enabled
     */
    public function testRepairGLobalTeam()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        global $mod_strings;
        $mteam = new Team();
        $mteam->retrieve($mteam->global_team);
        // Dont use Team::mark_deleted because it stops script's execution
        $mteam->deleted = 1;
        $mteam->save();
        include 'modules/Administration/upgradeTeams.php';
        $mteam->retrieve($mteam->global_team);
        $this->assertEquals($mteam->id, $mteam->global_team);
        $this->assertEquals($mteam->deleted, 0);
    }

}
