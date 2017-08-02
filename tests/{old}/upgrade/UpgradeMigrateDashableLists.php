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

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'upgrade/scripts/post/4_MigrateDashableLists.php';

class UpgradeMigrateDashableLists extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestVersionAvailability
     */
    public function testVersionAvailability($fromVersion, $toVersion, $runMigration = false)
    {
        $script = $this->getMockBuilder('\SugarUpgradeMigrateDashableLists')
            ->setMethods(array('migrateDashableLists', '__get'))
            ->disableOriginalConstructor()
            ->getMock();

        if ($runMigration) {
            $script->expects($this->once())->method('migrateDashableLists');
        } else {
            $script->expects($this->never())->method('migrateDashableLists');
        }

        $script->from_version = $fromVersion;
        $script->to_version = $toVersion;
        $script->run();
    }

    public function providerTestVersionAvailability()
    {
        return array(
            array('6.7', '7.0'),
            array('7.0', '7.2', true),
            array('7.1.5', '7.2', true),
            array('7.2', '7.2'),
            array('7.2', '7.3'),
        );
    }

    /**
     * @dataProvider providerTestUpdateView
     */
    public function testUpdateView($myItems, $favorites, $filterId)
    {
        $script = $this->getMockBuilder('\SugarUpgradeMigrateDashableLists')
            ->setMethods(array('__call'))
            ->disableOriginalConstructor()
            ->getMock();

        $view = new stdClass;
        $view->my_items = $myItems;
        $view->favorites = $favorites;

        $script->updateView($view);

        $this->assertFalse(property_exists($view, 'my_items'));
        $this->assertFalse(property_exists($view, 'favorites'));
        $this->assertEquals($filterId, $view->filter_id);
    }

    public function providerTestUpdateView()
    {
        return array(
            array(1, 0, 'assigned_to_me'),
            array(1, 1, 'assigned_to_me'),
            array(0, 1, 'favorites'),
            array(0, 0, 'all_records'),
        );
    }
}
