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
require_once 'upgrade/scripts/post/9_ClearHooks.php';

/**
 * Test for clearing logic hooks.
 */
class ClearHooksTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
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
     * Test removing bad hooks.
     * @param array $hooks
     * @param array $result
     * @param bool $rewrite
     *
     * @dataProvider provider
     */
    public function testRun($hooks, $result)
    {
        $path = sugar_cached(__CLASS__);
        SugarAutoLoader::ensureDir($path);
        $upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $upgradeDriver->context = array(
            'source_dir' => $path,
            'backup_dir'=> $path.'/backup',
        );
        $fname = $path . DIRECTORY_SEPARATOR . 'testHooks.php';
        file_put_contents($fname, "<?php \$hook_array=" . var_export($hooks, true) . ";");
        $script = $this->getMockBuilder('SugarUpgradeClearHooks')
            ->setMethods(array('findHookFiles'))
            ->setConstructorArgs(array($upgradeDriver))
            ->getMock();

        if (isset($hooks['before_save'][0][2]) && $hooks['before_save'][0][2] == 'clearHooksTestcheckToken.php') {
            $checkTokenFile = $path . DIRECTORY_SEPARATOR . $hooks['before_save'][0][2];
            file_put_contents($checkTokenFile, '<?php class ' . $hooks['before_save'][0][3] . ' { function one(){ global $x; $this->${$x};$this->{$x};} function two(){echo "1";} function three(){ echo "1";}}');
            $checkToken = SugarTestReflection::callProtectedMethod($script, 'checkClassMethodInFile', array($checkTokenFile, $hooks['before_save'][0][3], $hooks['before_save'][0][4]));
            $this->assertEquals($result, $checkToken);
            $this->assertFileExists($checkTokenFile);
        } else {
            $checkToken = SugarTestReflection::callProtectedMethod($script, 'checkClassMethodInFile', array($hooks['before_save'][0][2], $hooks['before_save'][0][3], $hooks['before_save'][0][4]));
            $this->assertEquals($result, $checkToken);
            $this->assertFileExists($hooks['before_save'][0][2]);
        }
        $script->expects($this->any())
            ->method('findHookFiles')
            ->will($this->returnValue(array('ext' => array(), 'hooks' => array($fname))));
        $script->run();
    }

    /**
     * Data provider.
     * @return array
     */
    public function provider()
    {
        return array(
            'GoodHooks' => array(
                array(
                    'before_save' => array(
                        array(1, '', 'data/SugarBean.php', 'SugarBean', 'retrieve'),
                        array(2, '', 'data/SugarBean.php', 'SugarBean', 'save'),
                    )
                ),
                true,
            ),
            'BadHook' => array(
                array(
                    'before_save' => array(
                        array(1, '', 'data/SugarBean.php', 'SugarBean', 'retrieve2'),
                        array(2, '', 'data/SugarBean.php', 'SugarBean', 'SomeStrangeMethod2'),
                    ),
                ),
                false,
            ),
            'BadHooks' => array(
                array(
                    'before_save' => array(
                        array(1, '', 'data/SugarBean.php', 'SugarBean', 'SomeStrangeMethod2'),
                        array(2, '', 'data/SugarBean.php', 'SugarBean', 'SomeStrangeMethod'),
                        array(3, '', 'data/SugarBean3.php', 'SugarBean3', 'SomeStrangeMethod'),
                        array(4, ''),
                    ),
                ),
                false,
            ),
            'checkTokenHooks' => array(
                array(
                    'before_save' => array(
                        Array(1, 'checkTokenHooks', 'clearHooksTestcheckToken.php', 'test_class_one', 'three'),
                    ),
                ),
                true,
            ),
            'checkTokenHooksFalse' => array(
                array(
                    'before_save' => array(
                        Array(1, 'checkTokenHooksFalse', 'clearHooksTestcheckToken.php', 'test_class_two', 'noneMethod')
                    ),
                ),
                false,
            )
        );
    }
}
