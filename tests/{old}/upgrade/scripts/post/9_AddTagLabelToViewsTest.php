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

require_once 'upgrade/scripts/post/9_AddTagLabelToViews.php';

/**
 * Test for adding the tag label
 */
class SugarUpgradeAddTagLabelToViewsTest extends UpgradeTestCase
{

    /**
     * Tests adding the taggable property to config
     * @param array $config The data to test
     * @param array $expect Expectations
     * @dataProvider addTaggableToConfigProvider
     */
    public function testAddTaggableConfigProperty($config, $expect)
    {
        $testScript = new SugarUpgradeAddTagLabelToViews($this->upgrader);
        $actual = $testScript->addTaggableConfigProperty($config);
        $this->assertEquals($actual, $expect['config']);
    }

    public function addTaggableToConfigProvider()
    {
        return array(
            // config is empty
            array(
                array(),
                'expect' => array(
                    'config' => array(),
                ),
            ),
            // config is not empty and does not have a taggable property
            array(
                'config' => array(
                    'team_security' => true,
                    'assignable' => true,
                ),
                'expect' => array(
                    'config' => array(
                        'team_security' => true,
                        'assignable' => true,
                        'taggable' => 1,
                    ),
                ),
            ),
            // config is not empty and has a taggable property
            array(
                'config' => array(
                    'team_security' => true,
                    'assignable' => true,
                    'taggable' => 1,
                ),
                'expect' => array(
                    'config' => array(
                        'team_security' => true,
                        'assignable' => true,
                        'taggable' => 1,
                    ),
                ),
            ),
        );
    }

    /**
     * Tests adding the Tags language properties
     * @param $data
     * @param $expect
     * @dataProvider addTagPropToLangProvider
     */
    public function testaddTaggableLangProperties($data, $expect)
    {
        $testScript = new SugarUpgradeAddTagLabelToViews($this->upgrader);
        $testScript->setTaggableLangStrings();
        $actual = $testScript->addTaggableLangProperties($data);
        $this->assertEquals($actual, $expect['mod_strings']);
    }

    public function addTagPropToLangProvider()
    {
        return array(
            // mod_strings is empty
            array(
                array(),
                'expect' => array(
                    'mod_strings' => array(),
                ),
            ),
            // mod_strings is not empty and does not have LBLs
            array(
                'mod_strings' => array(
                    'LBL_TEAM' => 'Teams',
                    'LBL_TEAM_SET' => 'Teams Set',
                ),
                'expect' => array(
                    'mod_strings' => array(
                        'LBL_TEAM' => 'Teams',
                        'LBL_TEAM_SET' => 'Teams Set',
                        'LBL_TAGS_LINK' => 'Tags',
                        'LBL_TAGS' => 'Tags',
                    ),
                ),
            ),
            // mod_strings is not empty and has LBLs
            array(
                'mod_strings' => array(
                    'LBL_TEAM' => 'Teams',
                    'LBL_TEAM_SET' => 'Teams Set',
                ),
                'expect' => array(
                    'mod_strings' => array(
                        'LBL_TEAM' => 'Teams',
                        'LBL_TEAM_SET' => 'Teams Set',
                        'LBL_TAGS_LINK' => 'Tags',
                        'LBL_TAGS' => 'Tags',
                    ),
                ),
            ),
        );
    }
}
