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
require_once 'upgrade/scripts/post/9_RepairVendors.php';

/**
 * Test asserts correct replacing of include path to vendor path
 */
class RepairVendorsTest extends UpgradeTestCase
{
    /** @var UpgradeDriver */
    protected $upgradeDriver = null;
    protected $testDir = 'modules/testVendors/';
    protected $testModule = 'testVendors';

    protected $oldSmartyPluginsDir = 'custom/include/Smarty/';
    protected $newSmartyPluginsDir = 'custom/include/SugarSmarty/';

    protected $smartyPluginPath = 'plugins/function.sugar_test.php';

    protected $pluginContent = <<<EOC
<?php
function smarty_function_sugar_test(\$params, &\$smarty)
{
    return 'sugar_test';
}
EOC;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
        $this->upgradeDriver = new TestUpgrader(self::$admin);
        mkdir_recursive($this->testDir);

        mkdir_recursive($this->oldSmartyPluginsDir . dirname($this->smartyPluginPath));
        file_put_contents($this->oldSmartyPluginsDir . $this->smartyPluginPath, $this->pluginContent);
    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestHelper::tearDown();
        rmdir_recursive($this->testDir);

        rmdir_recursive($this->oldSmartyPluginsDir);
        if (file_exists($this->newSmartyPluginsDir . $this->smartyPluginPath)) {
            unlink($this->newSmartyPluginsDir . $this->smartyPluginPath);
        }
    }

    /**
     * @dataProvider getContents
     */
    public function testRepairVendors($content, $expected, $fromVersion)
    {
        $file = $this->testDir . 'RepairVendorsTest.php';

        $this->upgradeDriver->context['source_dir'] = $this->testDir;
        $this->upgradeDriver->setVersions($fromVersion, 'ULT', '7.9.9', 'ULT');
        SugarAutoLoader::ensureDir($this->testDir);
        SugarTestHelper::saveFile($file);
        sugar_file_put_contents($file, $content);

        // Emaluate 6_ScanModules result for MBModules array
        $this->upgradeDriver->state['MBModules'] = array($this->testModule);

        $script = $this->getMockBuilder('SugarUpgradeRepairVendorsMock')->setMethods(array('backupFile'))->setConstructorArgs(array($this->upgradeDriver))->getMock();
        if ($content == $expected) {
            $script->expects($this->never())->method('backupFile');
        } else {
            $script->expects($this->atLeastOnce())->method('backupFile')->with($this->equalTo('modules/testVendors/RepairVendorsTest.php'));
        }

        $this->assertTrue(file_exists($this->oldSmartyPluginsDir . $this->smartyPluginPath), 'File does not exist');

        $script->run();
        $actual = sugar_file_get_contents($file);
        $this->assertEquals($expected, $actual, 'File replaced incorrectly');

        $this->assertTrue(file_exists($this->newSmartyPluginsDir . $this->smartyPluginPath), 'File does not exist');
        $this->assertFalse(file_exists($this->oldSmartyPluginsDir . $this->smartyPluginPath), 'File exists');
    }

    /**
     * @param string $source
     * @param string $expected
     * @group CRYS467
     * @dataProvider specificFilesProvider
     */
    public function testRepairSugarSpecificFilesPath($source, $expected)
    {
        $file = $this->testDir . 'sugarSpecificInclusions.php';
        SugarAutoLoader::ensureDir($this->testDir);
        SugarTestHelper::saveFile($file);
        sugar_file_put_contents($file, $source);

        $mockObject = $this->getMockBuilder('SugarUpgradeRepairVendorsMock')->setMethods(array('backupFile'))->setConstructorArgs(array($this->upgradeDriver))->getMock();
        $mockObject->expects($this->atLeastOnce())->method('backupFile')->with($file);

        $mockObject->repairSugarSpecificFilesPath($file);

        $actual = sugar_file_get_contents($file);
        $this->assertEquals($expected, $actual, 'File was replaced incorrectly');
    }

    public function specificFilesProvider()
    {
        return array(
            array(
                "<?php \n require_once('include/Smarty/plugins/function.sugar_action_menu.php');",
                "<?php \n require_once('include/SugarSmarty/plugins/function.sugar_action_menu.php');"
            )
        );
    }

    /**
     * Returns data for testRepairVendors, content and its expected replaced version
     *
     * @return array
     */
    public static function getContents()
    {
        return array(
            array(
                "<?php \n require_once 'include/ytree/ytree.php';\n?>",
                "<?php \n require_once 'vendor/ytree/ytree.php';\n?>",
                "6.1.1",
            ),
            array(
                "<?php \n require_once 'include/nusoap/nusoap.php';\n?>",
                "<?php \n require_once 'vendor/nusoap/nusoap.php';\n?>",
                "6.1.1",
            ),
            array(
                "<?php \n require_once 'vendor/ytree/ytree.php';\n?>",
                "<?php \n require_once 'vendor/ytree/ytree.php';\n?>",
                "6.1.1",
            ),
            array(
                "<?php \n require_once 'include/Sugar_Smarty.php';\n?>",
                "<?php \n require_once 'include/Sugar_Smarty.php';\n?>",
                "6.1.1",
            ),
            array(
                "<?php \n require_once 'include/Sugar-Smarty.php';\n?>",
                "<?php \n require_once 'include/Sugar-Smarty.php';\n?>",
                "6.1.1",
            ),
            array(
                "<?php \n require_once 'include/Sugar.Smarty.php';\n?>",
                "<?php \n require_once 'include/Sugar.Smarty.php';\n?>",
                "6.1.1",
            ),
            array(
                "<?php \n require_once 'include/Smarty.php';\n?>",
                "<?php \n require_once 'vendor/Smarty.php';\n?>",
                "6.1.1",
            ),
            // Elastica path change before 7.0.0
            array(
                "<?php \n include_once 'include/SugarSearchEngine/Elastic/Elastica/Index.php';\n?>",
                "<?php \n include_once 'vendor/ruflin/elastica/lib/Elastica/Index.php';\n?>",
                "6.1.1",
            ),
            // Elastica path change from 7.x to 7.5.0
            array(
                "<?php \n include_once 'vendor/Elastica/Index.php';\n?>",
                "<?php \n include_once 'vendor/ruflin/elastica/lib/Elastica/Index.php';\n?>",
                "7.2.1",
            ),
            // ZF1 code-style test
            array(
                "<?php \n require_once 'Zend/Date/Cities.php'; \n require_once 'Zend/Date/Countries.php'; \n\n class Zend_Date_Cities \n { \n public static \$cities; \n } \n?>",
                "<?php \n require_once 'vendor/Zend/Date/Cities.php'; \n require_once 'vendor/Zend/Date/Countries.php'; \n\n class Zend_Date_Cities \n { \n public static \$cities; \n } \n?>",
                "6.1.1",
            ),
            // ZF2 code-style test
            array(
                "<?php \n require_once 'Zend/Date/Cities.php'; \n require_once 'Zend/Date/Countries.php'; \n\n namespace Zend\Form; \n\n use Zend\Stdlib\ArrayUtils; \n use Zend\Stdlib\InitializableInterface; \n\n class Element implements \n ElementAttributeRemovalInterface, \n ElementInterface, \n InitializableInterface, \n LabelAwareInterface \n { \n protected \$attributes = array(); \n } \n?>",
                "<?php \n require_once 'vendor/Zend/Date/Cities.php'; \n require_once 'vendor/Zend/Date/Countries.php'; \n\n namespace Zend\Form; \n\n use Zend\Stdlib\ArrayUtils; \n use Zend\Stdlib\InitializableInterface; \n\n class Element implements \n ElementAttributeRemovalInterface, \n ElementInterface, \n InitializableInterface, \n LabelAwareInterface \n { \n protected \$attributes = array(); \n } \n?>",
                "6.1.1",
            ),
            // Negative test to verify we are only running the pre75 replaces - this is not an actual use case
            array(
                "<?php \n require_once 'include/nusoap/nusoap.php';\n?>",
                "<?php \n require_once 'include/nusoap/nusoap.php';\n?>",
                "7.2.1",
            ),
            // OneLogin folder change (from version 7.2.x and 7.5.x)
            array(
                "<?php \n include_once 'vendor/OneLogin/Saml/AuthRequest.php';\n?>",
                "<?php \n include_once 'vendor/onelogin/php-saml/lib/Saml/AuthRequest.php';\n?>",
                "7.2.1",
            ),
            array(
                "<?php \n include_once 'vendor/OneLogin/Saml/AuthRequest.php';\n?>",
                "<?php \n include_once 'vendor/onelogin/php-saml/lib/Saml/AuthRequest.php';\n?>",
                "7.5.0",
            ),
            // PHPMailer path change before 7.0.0
            array(
                "<?php \n include_once 'include/phpmailer/class.phpmailer.php';\n?>",
                "<?php \n include_once 'vendor/phpmailer/phpmailer/class.phpmailer.php';\n?>",
                '6.1.1',
            ),
            // PHPMailer path change from 7.x to 7.5.0
            array(
                "<?php \n include_once 'vendor/phpmailer/class.phpmailer.php';\n?>",
                "<?php \n include_once 'vendor/phpmailer/phpmailer/class.phpmailer.php';\n?>",
                '7.2.1',
            ),
        );
    }
}

/**
 * Class SugarUpgradeRepairVendorsMock replaces SugarUpgradeRepairVendors methods
 */
class SugarUpgradeRepairVendorsMock extends SugarUpgradeRepairVendors
{

    public function getModulesList()
    {
        return array(
            'modules/testVendors'
        );
    }

    public function isNewModule()
    {
        return true;
    }

    public function isMBModule()
    {
        return true;
    }
}
