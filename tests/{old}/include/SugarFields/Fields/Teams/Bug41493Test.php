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


class Bug41493Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testGetListViewSmarty()
    {
        $teamset = new SugarFieldTeamset('teamset');
        $result_template = $teamset->getListViewSmarty(array('TEAM_NAME' => 'Team name'), array('name' => 'team_name'), array(), '');
        $this->assertRegExp('/Team name/', $result_template, 'lowercase name');
        $result_template = $teamset->getListViewSmarty(array('TEAM_NAME' => 'Team name'), array('name' => 'TEAM_NAME'), array(), '');
        $this->assertRegExp('/Team name/', $result_template, 'uppercase name');
    }
}
