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

/**
 * Bug51721Test.php
 *
 */
require_once('modules/UpgradeWizard/uw_utils.php');

class Bug51721Test extends Sugar_PHPUnit_Framework_TestCase
{

private $new_upgrade;
private $new_upgrade2;

public function setUp()
{
    global $sugar_config;
    $sugar_config['cache_dir'] = 'cache/';

    $GLOBALS['db']->query("DELETE FROM upgrade_history WHERE name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3'");

    $this->new_upgrade = new UpgradeHistory();
    $this->new_upgrade->filename = 'cache/upload/upgrade/temp/Bug51721Test.zip';
    $this->new_upgrade->md5sum = md5('cache/upload/upgrade/temp/Bug51721Test.zip');
    $this->new_upgrade->type = 'patch';
    $this->new_upgrade->version = '6.4.3';
    $this->new_upgrade->status = "installed";
    $this->new_upgrade->name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3';
    $this->new_upgrade->description = 'Silent Upgrade was used to upgrade the instance';
    $this->new_upgrade->save();

    $this->new_upgrade2 = new UpgradeHistory();
    $this->new_upgrade2->filename = 'cache//upload/upgrade/temp/Bug51721Test.zip';
    $this->new_upgrade2->md5sum = md5('cache//upload/upgrade/temp/Bug51721Test.zip');
    $this->new_upgrade2->type = 'patch';
    $this->new_upgrade2->version = '6.4.3';
    $this->new_upgrade2->status = "installed";
    $this->new_upgrade2->name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3';
    $this->new_upgrade2->description = 'Silent Upgrade was used to upgrade the instance';
    $this->new_upgrade2->save();
}

public function tearDown()
{
    $GLOBALS['db']->query("DELETE FROM upgrade_history WHERE id IN ('{$this->new_upgrade->id}', '{$this->new_upgrade2->id}')");
}

public function testRepairUpgradeHistoryTable()
{
    repairUpgradeHistoryTable();
    $file = $GLOBALS['db']->getOne("SELECT filename FROM upgrade_history WHERE id = '{$this->new_upgrade->id}'");
    $this->assertEquals('upload/upgrade/temp/Bug51721Test.zip', $file);
    $file = $GLOBALS['db']->getOne("SELECT filename FROM upgrade_history WHERE id = '{$this->new_upgrade2->id}'");
    $this->assertEquals('upload/upgrade/temp/Bug51721Test.zip', $file);
}

}