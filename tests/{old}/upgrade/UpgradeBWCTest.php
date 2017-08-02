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

class UpgradeBWCTest extends UpgradeTestCase
{

    protected $modules = array(
        'scantest', 'scantestMB', 'scantestExt', 'scantestHooks', 'scantestHTML'
    );

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::saveFile('custom/Extension/application/Ext/Include/scantest.php');
        $data = "<?php \n";
        foreach($this->modules as $mod) {
            $data .= <<<END
\$beanList['$mod'] = '$mod';
\$beanFiles['$mod'] = 'modules/$mod/$mod.php';
\$moduleList[] = '$mod';
END;
            sugar_mkdir("modules/$mod");
            file_put_contents("modules/$mod/$mod.php", "<?php ");
            $GLOBALS['dictionary'][$mod] = $GLOBALS['dictionary']['Contact'];
        }
        mkdir_recursive('custom/Extension/application/Ext/Include/');
        mkdir_recursive("modules/scantestHooks/views");
        file_put_contents('custom/Extension/application/Ext/Include/scantest.php', $data);

        file_put_contents('modules/scantest/scantest2.php', "<?php echo 'Hello world!'; ");
        copy(dirname(__FILE__)."/view_edit.php", "modules/scantestHooks/views/view.edit.php");

        mkdir_recursive('custom/modules/scantestHooks/Ext/LogicHooks');
        mkdir_recursive('custom/modules/scantestHooks/workflow');

        file_put_contents('custom/modules/scantestHooks/scantestHooks2.php', "<?php echo 'Hello world!'; ");
        $hook_array['before_save'][] = array(1, 'Custom Logic', 'modules/scantestHooks/scantestHooks.php', 'test', 'test');
        write_array_to_file('hook_array', $hook_array, 'custom/modules/scantestHooks/logic_hooks.php');

        $hook_array['after_save'][] = array(1, 'Custom Logic', 'custom/modules/scantestHooks/scantestHooks2.php', 'test', 'test');
        write_array_to_file('hook_array', $hook_array, 'custom/modules/scantestHooks/Ext/LogicHooks/logichooks.ext.php');


        mkdir_recursive('custom/modules/scantestExt/Ext/ActionViewMap');
        file_put_contents('custom/modules/scantestExt/Ext/ActionViewMap/scantestExt.php', "<?php echo 'Hello world!'; ");


        $this->mi = new ModuleInstaller();
        $this->mi->silent = true;

        $GLOBALS['dictionary']['scantestHTML']['fields']['test_c'] = array(
            'name'       => 'test_c',
            'type'       => 'enum',
            'dbType'     => 'varchar',
            'function'   => array(
                            'name'    => 'test',
                            'returns' => 'html',
            )
        );

        $this->mi->rebuild_modules();

        SugarTestHelper::saveFile('custom/Extension/application/Ext/Include/upgrade_bwc.php');
        SugarTestHelper::saveFile('files.md5');
        copy(__DIR__."/files.md5", "files.md5");
    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestHelper::tearDown();
        foreach($this->modules as $mod) {
            rmdir_recursive("modules/$mod");
            rmdir_recursive("custom/modules/$mod");
            unset($GLOBALS['dictionary'][$mod]);
        }
        $this->mi->rebuild_modules();
    }

    /**
     * Test for ScanModules
     */
    public function testScanModules()
    {
        $this->upgrader->setVersions("6.7.3", 'ent', '7.1.5', 'ent');
        $script = $this->upgrader->getScript("post", "6_ScanModules");
        $script->run();

        $bwcModules = array();
        $this->assertFileExists('custom/Extension/application/Ext/Include/upgrade_bwc.php', "custom/Extension/application/Ext/Include/upgrade_bwc.php not created");
        include 'custom/Extension/application/Ext/Include/upgrade_bwc.php';
        // scantest should be in bwc
        $this->assertEquals(array('scantest', 'scantestExt', 'scantestHTML'), $bwcModules);
    }
}
