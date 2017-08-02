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

require_once 'modules/ModuleBuilder/MB/MBModule.php';

class MBModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $moduleName = 'superAwesomeModule';
    protected $packageKey = 'sap';
    protected $mbModuleName;
    protected $target;
    protected $path;

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('files');
        $this->mbModuleName = "{$this->packageKey}_{$this->moduleName}";
        $this->path = "modules/{$this->moduleName}";
        $this->target = "$this->path/clients/base/menus/header/header.php";
        SugarTestHelper::saveFile($this->target);
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers MBModule::createMenu
     */
    public function testCreateMenu()
    {
        $expectedArray = $this->getExpectedActionItems();

        $mb = new MBModule($this->moduleName, "modules/{$this->moduleName}", 'superAwesomePackage', $this->packageKey);
        $mb->config['importable'] = false;
        $mb->createMenu($this->path);

        // Assertions
        $this->assertFileExists($this->target);

        include $this->target;

        $menu = $viewdefs[$this->mbModuleName]['base']['menu']['header'];
        $this->assertEquals($expectedArray, $menu);
    }

    /**
     * @covers MBModule::createMenu
     */
    public function testCreateMenuWithImport()
    {
        $expectedArray = $this->getExpectedActionItems(true);

        $mb = new MBModule($this->moduleName, "modules/{$this->moduleName}", 'superAwesomePackage', $this->packageKey);
        $mb->config['importable'] = true;
        $mb->createMenu($this->path);

        // Assertions
        $this->assertFileExists($this->target);

        include $this->target;

        $menu = $viewdefs[$this->mbModuleName]['base']['menu']['header'];
        $this->assertEquals($expectedArray, $menu);
    }

    protected function getExpectedActionItems($import = false)
    {
        $expectedArray = array(
            array(
                'route' => "#{$this->mbModuleName}/create",
                'label' => 'LNK_NEW_RECORD',
                'acl_action' => 'create',
                'acl_module' => $this->mbModuleName,
                'icon' => 'fa-plus',
            ),
            array(
                'route' => "#{$this->mbModuleName}",
                'label' => 'LNK_LIST',
                'acl_action' => 'list',
                'acl_module' => $this->mbModuleName,
                'icon' => 'fa-bars',
            ),
        );

        if ($import) {
            $importRoute = http_build_query(
                array(
                    'module' => 'Import',
                    'action' => 'Step1',
                    'import_module' => $this->mbModuleName,
                    'return_module' => $this->mbModuleName,
                    'return_action' => 'index',
                )
            );

            $expectedArray[] = array(
                'route' => "#bwc/index.php?{$importRoute}",
                'label' => 'LBL_IMPORT',
                'acl_action' => 'import',
                'acl_module' => $this->mbModuleName,
                'icon' => 'fa-arrow-circle-o-up',
            );
        }

        return $expectedArray;
    }


    public function vardefProvider()
    {
        return array(
          array(
                array("name" => "testvardef", "label" => "testvardef"),
                "testvardef"
            ),
          array(
                array("name" => "range", "label" => "testvardef"),
                "range_field"
            ),
          array(
                array("name" => "hipopotomounstruosesquipedaliofobia_pentakismyriahexakisquilioletracosiohexacontapentagono", "label" => "testvardef"),
                $GLOBALS['db']->getValidDBName('hipopotomounstruosesquipedaliofobia_pentakismyriahexakisquilioletracosiohexacontapentagono', true, 'column'),
            ),
        );
    }

    /**
     * @dataProvider vardefProvider
     * @param array $vardef
     * @param string $exp_name
     */
    public function testVardefValidation($vardef, $exp_name)
    {
        $mb = new MBModule($this->moduleName, "modules/{$this->moduleName}", 'superAwesomePackage', $this->packageKey);
        $mb->addField($vardef);
        $defs = $mb->mbvardefs->getVardef();
        $this->assertArrayHasKey('fields', $defs);
        $this->assertArrayHasKey($exp_name, $defs['fields']);
        $this->assertEquals($exp_name, $defs['fields'][$exp_name]['name']);
    }
}
