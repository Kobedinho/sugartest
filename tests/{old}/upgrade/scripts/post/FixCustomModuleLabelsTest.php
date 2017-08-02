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
require_once 'upgrade/scripts/post/7_FixCustomModuleLabels.php';

/**
 * Class FixCustomModuleLabelsTest test for SugarUpgradeFixCustomModuleLabels upgrader script
 */
class FixCustomModuleLabelsTest extends UpgradeTestCase
{
    protected $module = 'TestModule';

    /** @var TestSugarUpgradeFixCustomModuleLabels */
    protected $testScript;

    public function setUp()
    {
        parent::setUp();

        $this->testScript = new TestSugarUpgradeFixCustomModuleLabels($this->upgrader);

        foreach ($this->testScript->getCustomModules() as $module) {
            // Create language file for test module
            $mod_strings = array(
                'LBL_MODULE_NAME' => $module,
            );

            SugarTestHelper::saveFile($this->testScript->getModuleLangPath($module));
            write_array_to_file('mod_strings', $mod_strings, $this->testScript->getModuleLangPath($module));
        }
    }

    public function testCompileLabels()
    {
        $labels =
            array(
                'LBL_{module_name}',
                'LBL_NAME'
            );

        $expected =
            array(
                'LBL_TESTMODULE',
                'LBL_NAME'
            );

        $actual = $this->testScript->compileLabels($this->module, $labels);

        $this->assertEquals($expected, $actual);
    }

    public function testTranslateLabels()
    {
        $labels =
            array(
                'LNK_IMPORT_TESTMODULE',
                'LNL_LABEL_NOT_EXISTS',
            );

        $mod_strings = array('LBL_MODULE_NAME' => $this->module);

        $expected = array_merge(
            $mod_strings,
            array(
                'LNK_IMPORT_TESTMODULE' => 'Import TestModule',
            )
        );

        $actual = $this->testScript->translateLabels($labels, $mod_strings, $this->module);

        $this->assertEquals($expected, $actual);
    }

    public function testRunNotVersion()
    {
        $this->testScript->from_version = '7.2.1';

        $actual = $this->testScript->run();
        $this->assertNull($actual);
    }

    public function testRun()
    {
        $this->testScript->run();

        foreach ($this->testScript->getCustomModules() as $module) {
            $mod_strings = array();
            require $this->testScript->getModuleLangPath($module);

            // After upgrader script execution, module should contain correct labels
            $this->assertArrayHasKey('LNK_IMPORT_' . strtoupper($module), $mod_strings);
        }
    }
}

/**
 * Test class with additional "mock" logic for tests
 */
class TestSugarUpgradeFixCustomModuleLabels extends SugarUpgradeFixCustomModuleLabels
{
    public $from_version = '6.7.5';

    /**
     * Get custom modules for test
     *
     * @return array
     */
    public function getCustomModules()
    {
        return array('TestModule');
    }

    /**
     * Get module language file path
     *
     * @param $module
     * @return string
     */
    public function getModuleLangPath($module)
    {
        return 'cache/' . $module . 'en_us.lang.php';
    }
}
