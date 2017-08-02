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
require_once 'upgrade/scripts/post/4_ClearSubpanels.php';

/**
 * Test for clearing bad defs from supbanels definitions..
 */
class ClearSubpanelsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        global $beanList;
        parent::setUp();
        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('beanList');
        $beanList = array(
            $this->module => 'Account',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @param array $def
     * @param string $layout
     * @param string $file
     * @param array $expectedLayout
     * @param array $state
     *
     * @dataProvider provider
     */
    public function testRun($def, $layout, $file, $expectedLayout, $state = array())
    {
        $path = sugar_cached(__CLASS__);
        SugarAutoLoader::ensureDir($path . DIRECTORY_SEPARATOR . 'custom');
        $fpath = $path . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . $file;
        SugarTestHelper::saveFile($fpath);
        sugar_file_put_contents($fpath, $layout);


        $upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $upgradeDriver->context = array(
            'source_dir' => $path
        );
        $upgradeDriver->state = $state;

        $script = $this->getMockBuilder('SugarUpgradeClearSubpanels')
            ->setMethods(array('getDefFiles', 'updateFile', 'getBeanDefs', 'rebuildExtensions'))
            ->setConstructorArgs(array($upgradeDriver))
            ->getMock();

        $script->expects($this->any())
            ->method('getBeanDefs')
            ->will($this->returnValue($def));
        $script->expects($this->any())
            ->method('getDefFiles')
            ->will($this->returnValue(array($fpath)));
        $script->expects($this->once())
            ->method('rebuildExtensions');
        $script->expects($this->once())
            ->method('updateFile')
            ->with($fpath, $expectedLayout);
        $script->run();
    }

    public function provider()
    {
        return array(
            array(
                array(
                    'a' => array(
                        'name' => 'a',
                        'type' => 'text'
                    ),
                    'b' => array(
                        'name' => 'b',
                        'type' => 'text'
                    ),
                ),
                <<<EOL
<?php
\$subpanel_layout = array (
  'top_buttons' => array (),
  'where' => '',
  'list_fields' => array (
    'a' =>
    array (
      'vname' => 'a',
    ),
    'b' => array (
      'vname' => 'b',
    ),
    'c' => array (
      'vname' => 'c',
      'widget_class' => 'SubPanelDetailViewLink',
    ),
    'd' => array (
      'vname' => 'd',
      'usage' => 'SomeUsage'
    ),
    'e' => array (
      'vname' => 'e',
    ),
    'edit_button' => array (
      'vname' => 'edit_button'
    ),
  ),
);
EOL
            ,
                'tst.php',
                array(
                    'top_buttons' => array(),
                    'where' => '',
                    'list_fields' => array(
                        'a' => array (
                            'vname' => 'a',
                        ),
                        'b' => array (
                            'vname' => 'b',
                        ),
                        'c' => array (
                            'vname' => 'c',
                            'widget_class' => 'SubPanelDetailViewLink',
                        ),
                        'd' => array (
                            'vname' => 'd',
                            'usage' => 'SomeUsage'
                        ),
                        'edit_button' => array(
                            'vname' => 'edit_button',
                            'widget_class' => 'SubPanelEditButton'
                        )
                    )
                )
            ),
            array(
                array(
                    'a' => array(
                        'name' => 'a',
                        'type' => 'link',
                        'relationship' => 'd',
                    ),
                    'b' => array(
                        'name' => 'b',
                        'type' => 'relate',
                        'id_name' => 'c',
                        'relationship' => 'd',
                        'link' => 'a',
                    ),
                    'c' => array(
                        'name' => 'c',
                        'type' => 'id',
                        'relationship' => 'd',
                    )
                ),
                <<<EOL
<?php
\$layout_defs["{$this->module}"]["subpanel_setup"]["c"] = array (
  'order' => 100,
  'module' => '{$this->module}',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL',
  'get_subpanel_data' => 'c',
  'top_buttons' =>
  array (),
);
EOL
            ,
                'tst2.php',
                array(
                    $this->module => array(
                        'subpanel_setup' => array (
                            'a' => array (
                                'order' => 100,
                                'module' => $this->module,
                                'subpanel_name' => 'default',
                                'sort_order' => 'asc',
                                'sort_by' => 'id',
                                'title_key' => 'LBL',
                                'get_subpanel_data' => 'a',
                                'top_buttons' =>
                                    array (),
                            )
                        ),
                    )
                ),
            ),
            array(
                array(
                    'a' => array(
                        'name' => 'a',
                        'type' => 'text',
                    ),
                    'b' => array(
                        'name' => 'b',
                        'type' => 'text',
                    ),
                ),
                <<<EOL
<?php
\$subpanel_layout = array(
    'list_fields' => array(
        'a' => array(
            'vname' => 'a',
        ),
        'b' => array(
            'vname' => 'b',
            'widget_class' => 'WrongWidgetClass',
        ),
    ),
);
EOL
            ,
                'tst3.php',
                array(
                    'list_fields' => array(
                        'a' => array(
                            'vname' => 'a',
                        ),
                    ),
                ),
                array(
                    'healthcheck' => array(
                        array(
                            'report' => 'unknownWidgetClass',
                            'params' => array(
                                'WrongWidgetClass',
                                'b',
                                $this->module,
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
