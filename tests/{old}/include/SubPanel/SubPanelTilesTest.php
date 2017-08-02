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


class SubPanelTilesBase extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestHelper::tearDown();
    }

    /**
     * Set a custom subpanel order for a user, and check if it's returned properly
     *
     * @dataProvider dataProviderCustomSubpanelOrder
     */
    public function testCustomSubpanelOrder($customSubpanelOrder)
    {
        $bean = SugarTestCampaignUtilities::createCampaign();
        $remainingSubpanels = array(
            3 => 'emailmarketing',
            4 => 'track_queue',
            5 => 'targeted',
            6 => 'viewed',
            7 => 'link',
            8 => 'lead',
            9 => 'contact',
            10 => 'invalid_email',
            11 => 'send_error',
            12 => 'removed',
            13 => 'blocked',
            14 => 'accounts',
            15 => 'opportunities',
        );
        $customSubpanelOrder = array_merge($customSubpanelOrder, $remainingSubpanels);

        $GLOBALS['current_user']->setPreference('subpanelLayout', $customSubpanelOrder, 0, $bean->module_dir);

        $tiles = new SubPanelTiles($bean);

        $layout = $tiles->getTabs();

        $this->assertEquals($customSubpanelOrder, $layout, 'SubPanel returned is not correct');
    }

    public static function dataProviderCustomSubpanelOrder()
    {
        return array(
            array(
                array(
                    0 => 'leads',
                    1 => 'prospectlists',
                    2 => 'tracked_urls',
                ),
            ),
            array(
                array(
                    0 => 'prospectlists',
                    1 => 'tracked_urls',
                    2 => 'leads',
                ),
            ),
        );
    }
}
