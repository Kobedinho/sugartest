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
require_once "tests/{old}/upgrade/UpgradeTestCase.php";
require_once 'upgrade/scripts/post/7_CustomRecordViewHistorySummaryButton.php';

class CustomRecordViewHistorySummaryButtonTest extends UpgradeTestCase
{
    /** @var SugarUpgradeCustomRecordViewHistorySummaryButton */
    protected $script;

    public function setUp()
    {
        parent::setUp();

        /** @var SugarUpgradeCustomRecordViewHistorySummaryButton */
        $this->script = $this->upgrader->getScript('post', '7_CustomRecordViewHistorySummaryButton');
    }

    public function testAddHistorySummaryButtonWillNotAddWhenButtonExists()
    {

        $module = 'Accounts';
        $viewdefs[$module]['base']['view']['record']['buttons'] = array(
            array(
                'type' => 'actiondropdown',
                'name' => 'main_dropdown',
                'primary' => true,
                'showOn' => 'view',
                'buttons' => array(
                    array(
                        'type' => 'rowaction',
                        'event' => 'button:duplicate_button:click',
                        'name' => 'duplicate_button',
                        'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                        'acl_module' => 'Accounts',
                        'acl_action' => 'create',
                    ),
                    array(
                        'type' => 'rowaction',
                        'event' => 'button:historical_summary_button:click',
                        'name' => 'historical_summary_button',
                        'label' => 'LBL_HISTORICAL_SUMMARY',
                        'acl_action' => 'view',
                    )
                ),
            ),
        );
        $new_defs = $this->script->addHistorySummaryButton($viewdefs, $module);

        $this->assertEquals(2, count($new_defs[$module]['base']['view']['record']['buttons'][0]['buttons']));
    }

    public function testAddHistorySummaryButtonWillAddWhenButtonDoesNotExists()
    {

        $module = 'Accounts';
        $viewdefs[$module]['base']['view']['record']['buttons'] = array(
            array(
                'type' => 'actiondropdown',
                'name' => 'main_dropdown',
                'primary' => true,
                'showOn' => 'view',
                'buttons' => array(
                    array(
                        'type' => 'rowaction',
                        'event' => 'button:duplicate_button:click',
                        'name' => 'duplicate_button',
                        'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                        'acl_module' => 'Accounts',
                        'acl_action' => 'create',
                    )
                ),
            ),
        );
        $new_defs = $this->script->addHistorySummaryButton($viewdefs, $module);

        $this->assertEquals(2, count($new_defs[$module]['base']['view']['record']['buttons'][0]['buttons']));
    }
}
