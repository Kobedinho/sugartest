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
class SugarUpgradeUpgradeBwcLayoutsTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $upgradeDriver;
    private $script;

    /**
     * Test
     * not registered
     * no thing to upgrade or BWC modules are not registered by pre-upgrade script
     * BWC modules are registered by pre-upgrade script, start BWC upgrade
     *
     * @dataProvider provider
     */
    public function testRun($bwc_modules, $excpectUpgrade)
    {
        $this->upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $this->script = $this->getMockBuilder('SugarUpgradeUpgradeBwcLayouts')
            ->setMethods(array('runBwcUpgraderModules'))
            ->setConstructorArgs(array($this->upgradeDriver))
            ->getMock();

        // BWC modules are registered by pre-upgrade script.
        $this->upgradeDriver->state = array(
            'bwc_modules' => $bwc_modules
        );

        if ($excpectUpgrade) {
            $this->script->expects($this->once())
                ->method('runBwcUpgraderModules');
        } else {
            $this->script->expects($this->never())
                ->method('runBwcUpgraderModules');
        }

        $this->script->run();
    }

    /**
     * Test
     *
     * @dataProvider provider
     */
    public function testRunSinglePrepareBwcUpgraderModules($bwc_modules)
    {
        $this->upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $this->script = $this->getMockBuilder('SugarUpgradeUpgradeBwcLayouts')
            ->setMethods(array('runBwcUpgraderModules'))
            ->setConstructorArgs(array($this->upgradeDriver))
            ->getMock();
        SugarTestReflection::callProtectedMethod($this->script, 'prepareBwcUpgraderModules', array($bwc_modules));
        $this->assertInstanceOf('SidecarMetaDataUpgraderBwc', $this->script->sidecarMetaDataUpgraderBwcUpgrader);
    }

    /**
     * Test
     * @dataProvider provider
     */
    public function testRunWhitEmptyBwcModules($bwc_modules, $excpectUpgrade)
    {
        $this->upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $this->script = $this->getMockBuilder('SugarUpgradeUpgradeBwcLayouts')
            ->setMethods(array('runBwcUpgraderModules', 'getBwcModules'))
            ->setConstructorArgs(array($this->upgradeDriver))
            ->getMock();

        // BWC modules are registered by pre-upgrade script.
        $this->upgradeDriver->state = array(
            'bwc_modules' => $bwc_modules
        );

        $this->script->expects($this->any())
            ->method('getBwcModules')
            ->willReturn(array());

        if ($excpectUpgrade || is_array($excpectUpgrade)) {
            $this->script->expects($this->once())
                ->method('runBwcUpgraderModules');
        } else {
            $this->script->expects($this->never())
                ->method('runBwcUpgraderModules');
        }

        $this->script->run();
    }

    /**
     * Data provider.
     * @return array
     */
    public function provider()
    {
        return array(
            'BWC modules are not registered by pre-upgrade script.' => array(
                array(),
                false,
            ),
            'diff as null' => array(
                array('Audit'),
                array(),
            ),
            'upgrade call' => array(
                array('call'),
                true,
            ),
        );
    }
}
