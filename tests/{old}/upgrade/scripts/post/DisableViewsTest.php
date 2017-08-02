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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/7_DisableViews.php';

/**
 * Test covers behavior of SugarUpgradeDisableViews post upgrade script
 */
class DisableViewsTest extends UpgradeTestCase
{
    public static function getFiles()
    {
        return array(
            array(
                'hasCustomViews',
                array(
                    'custom/modules/Accounts/views/bad.php',
                ),
                array(
                    'custom/modules/Accounts/views/bad.php' => 'custom/modules/Accounts/views/Disabled/bad.php',
                ),
            ),
            array(
                'hasCustomViewsModDir',
                array(
                    'modules/Accounts/views/bad.php',
                    'modules/Accounts/views/bad1.php',
                ),
                array(
                    'modules/Accounts/views/bad.php' => 'modules/Accounts/views/Disabled/bad.php',
                    'modules/Accounts/views/bad1.php' => 'modules/Accounts/views/Disabled/bad1.php',
                ),
            ),
        );
    }

    /**
     * Test check how to rename files from healthcheck results
     *
     * @dataProvider getFiles
     *
     * @param string $report name of report of health check
     * @param array $filesToDetect Files to move
     * @param array $filesToCheck Set of files to check as renameDisabled arguments
     */
    public function testRun($report, $filesToDetect, $filesToCheck)
    {
        $this->upgrader->state['healthcheck'] = array(
            array(
                'report' => $report,
                'params' => array(
                    'Accounts',
                    $filesToDetect,
                ),
            ),
        );

        $script = $this->getMockBuilder('SugarUpgradeDisableViews')->setMethods(array('renameDisabled'))->setConstructorArgs(array($this->upgrader))->getMock();

        $script->expects($this->once())->method('renameDisabled')->with($filesToCheck);

        $script->run();
    }
}
