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
require_once 'upgrade/scripts/post/7_UpgradeBwcLayouts.php';

/**
 * Test for upgrade BWC layouts
 */
class SidecarMetaDataUpgraderBwcTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testRunUpgradeWithListOfBwcModules($bwc_modules, $excpectUpgrade)
    {
        $this->script = $this->getMockBuilder('SidecarMetaDataUpgraderBwc')
            ->setMethods(array('prepareUpgradeOneModule', 'upgradeOneModule'))
            ->setConstructorArgs(array($bwc_modules))
            ->getMock();
        $this->assertEquals($bwc_modules, SugarTestReflection::getProtectedValue($this->script, 'modules'));
        if ($excpectUpgrade) {
            $this->script->expects($this->once())
                ->method('prepareUpgradeOneModule')
                ->with(
                    $this->callback(function ($module) use ($excpectUpgrade) {
                        return $excpectUpgrade == $module ? $excpectUpgrade : false;
                    })
                );
        } else {
            $this->script->expects($this->never())
                ->method('upgradeOneModule');
        }
        $this->script->upgrade();
    }

    /**
     * @dataProvider provider
     */
    public function testRunTryGetMBModules($bwc_modules)
    {
        $this->script = $this->getMockBuilder('SidecarMetaDataUpgraderBwc')
            ->setMethods(array('upgradeOneModule'))
            ->setConstructorArgs(array($bwc_modules))
            ->getMock();
        $this->assertEmpty($this->script->getMBModules());
    }

    /**
     * @dataProvider provider
     */
    public function testRunSetQuickCreateFiles($bwc_modules)
    {
        $this->script = $this->getMockBuilder('SidecarMetaDataUpgraderBwc')
            ->setMethods(array('getModulesList', 'getUpgradeFileParams', 'isQuickCreateVisible'))
            ->setConstructorArgs(array($bwc_modules))
            ->getMock();
        $this->script->expects($this->never())
            ->method('getModulesList');
        $this->script->expects($this->never())
            ->method('getUpgradeFileParams');
        $this->script->expects($this->never())
            ->method('isQuickCreateVisible');
        $this->script->SetQuickCreateFiles();
    }

    /**
     * @dataProvider provider
     */
    public function testRunUpgradeOneModule($bwc_modules, $excpectUpgrade)
    {
        $this->script = $this->getMockBuilder('SidecarMetaDataUpgraderBwc')
            ->setMethods(array('setModule', 'upgradeOneModule'))
            ->setConstructorArgs(array($bwc_modules))
            ->getMock();

        if ($excpectUpgrade) {
            $this->script->expects($this->once())
                ->method('upgradeOneModule');
            $this->script->expects($this->once())
                ->method('setModule')
                ->with(
                    $this->callback(function ($module) use ($bwc_modules) {
                        return isset($bwc_modules[0]) && $bwc_modules[0] == $module ? $module : false;
                    })
                );
        } else {
            $this->script->expects($this->never())
                ->method('setModule');
        }
        $this->script->upgrade();
    }

    /**
     * Data provider.
     * @return array
     */
    public function provider()
    {
        return array(
            'empty' => array(
                array(),
                false
            ),
            'upgrade call' => array(
                array(
                    'call'
                ),
                true
            ),
        );
    }
}
