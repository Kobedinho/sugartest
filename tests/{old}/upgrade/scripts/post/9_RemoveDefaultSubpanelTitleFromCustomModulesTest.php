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

require_once 'upgrade/scripts/post/9_RemoveDefaultSubpanelTitleFromCustomModules.php';

/**
 * Test for removing the default_subpanel_title label from language files for custom modules
 */
class SugarUpgradeRemoveDefaultSubpanelTitleFromCustomModulesTest extends UpgradeTestCase
{
    /**
     * Tests removing the default_subpanel property from language files
     * @param $data
     * @param $expect
     * @dataProvider removePropFromLangProvider
     */
    public function testRemoveLangProperty($data, $expect)
    {
        $testScript = new SugarUpgradeRemoveDefaultSubpanelTitleFromCustomModulesMock($this->upgrader);
        $actual = $testScript->removeLangProperty($data);
        $this->assertEquals($actual, $expect['mod_strings']);
    }

    public function removePropFromLangProvider()
    {
        return array(
            // mod_strings is empty
            array(
                array(),
                'expect' => array(
                    'mod_strings' => array(),
                ),
            ),
            // mod_strings is not empty and does not have 'LBL_DEFAULT_SUBPANEL_TITLE'
            array(
                'mod_strings' => array(
                    'LBL_TEAM' => 'Teams',
                    'LBL_TEAM_SET' => 'Teams Set',
                ),
                'expect' => array(
                    'mod_strings' => array(
                        'LBL_TEAM' => 'Teams',
                        'LBL_TEAM_SET' => 'Teams Set',
                    ),
                ),
            ),
            // mod_strings is not empty and has 'LBL_DEFAULT_SUBPANEL_TITLE'
            array(
                'mod_strings' => array(
                    'LBL_TEAM' => 'Teams',
                    'LBL_TEAM_SET' => 'Teams Set',
                    'LBL_DEFAULT_SUBPANEL_TITLE' => 'Sale',
                ),
                'expect' => array(
                    'mod_strings' => array(
                        'LBL_TEAM' => 'Teams',
                        'LBL_TEAM_SET' => 'Teams Set',
                    ),
                ),
            ),
        );
    }
}

class SugarUpgradeRemoveDefaultSubpanelTitleFromCustomModulesMock extends SugarUpgradeRemoveDefaultSubpanelTitleFromCustomModules
{
    public function removeLangProperty($data)
    {
        return parent::removeLangProperty($data);
    }
}
