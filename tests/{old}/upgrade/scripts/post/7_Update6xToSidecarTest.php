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
require_once 'upgrade/scripts/post/7_6xToSidecar.php';

class Update6xToSidecarTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Testing setUpgradeMBFiles method call with state['MBModules'] data
     */
    public function testMBModules()
    {
        $upgradeMock = $this->getMockForAbstractClass('UpgradeDriver');
        $upgradeMock->state['MBModules'] = array('Accounts', 'Documents');
        $sidecarUpgradeMock = $this->getMockBuilder('SidecarMetaDataUpgrader2')
            ->setMethods(['setUpgradeMBFiles'])
            ->setConstructorArgs([$upgradeMock])
            ->getMock();

        $sidecarUpgradeMock->expects($this->once())->method('setUpgradeMBFiles')
                           ->with($upgradeMock->state['MBModules']);
        $sidecarUpgradeMock->upgrade();
    }
}
