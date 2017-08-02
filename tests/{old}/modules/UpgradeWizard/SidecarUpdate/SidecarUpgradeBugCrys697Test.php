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


class SidecarMetaDataUpgraderBugCrys697Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function getStates()
    {
        return array(
            array(SidecarMetaDataUpgrader::UPGRADE_BASE, array('setBaseFilesToUpgrade')),
            array(SidecarMetaDataUpgrader::UPGRADE_MOBILE, array('setMobileFilesToUpgrade')),
            array(SidecarMetaDataUpgrader::UPGRADE_PORTAL, array('setPortalFilesToUpgrade')),
            array(SidecarMetaDataUpgrader::UPGRADE_SUBPANEL, array('setSubpanelFilesToUpgrade')),
            array(SidecarMetaDataUpgrader::UPGRADE_ALL, array('setBaseFilesToUpgrade', 'setPortalFilesToUpgrade', 'setMobileFilesToUpgrade', 'setSubpanelFilesToUpgrade'))
        );
    }

    /**
     * Tests correct use of diffirent upgrade type flags
     *
     * @dataProvider getStates
     * @param $state
     * @param $methods
     */
    public function testSetFilesToUpgrade($state, $methods)
    {
        $mock = $this->createPartialMock('SidecarMetaDataUpgrader', array('setBaseFilesToUpgrade', 'setPortalFilesToUpgrade', 'setMobileFilesToUpgrade', 'setSubpanelFilesToUpgrade'));

        foreach ($methods as $method) {
            $mock->expects($this->once())->method($method);
        }
        $mock->setUpgradeCategories($state);
        $mock->setFilesToUpgrade();
    }
}
