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
require_once 'upgrade/scripts/post/6_ScanModules.php';

/**
 * Class ScanModulesTestIsMBModule for SugarUpgradeScanModules
 */
class ScanModulesTestIsMBModule extends UpgradeTestCase
{
    private $directory = 'modules/TestModule';
    private $moduleName = 'TestModule';

    public function setUp()
    {
        mkdir($this->directory, 0777, true);
        touch("{$this->directory}/{$this->moduleName}.php");
        parent::setUp();
    }

    public function tearDown()
    {
        rmdir_recursive($this->directory);
        parent::tearDown();
    }

    /**
     * Data provider for testIsMBModuleWithEmptyFormsFile
     *
     * @return array Data for test
     */
    public function contentProvider()
    {
        return array(
            array('', true),
            array('<?php', false),
        );
    }

    /**
     * Tests if module considered to be MB regarding the contents of 'Forms.php' file
     *
     * @dataProvider contentProvider
     *
     * @group CRYS199
     *
     * @param string $formFileContents Forms.php file contents
     * @param boolean $shouldBeMB should Module be considered as MB?
     */
    public function testIsMBModuleWithEmptyFormsFile($formFileContents, $shouldBeMB)
    {
        $formsFile = "modules/{$this->moduleName}/Forms.php";
        SugarTestHelper::saveFile($formsFile);
        file_put_contents($formsFile, $formFileContents);

        $scriptObject = new SugarUpgradeScanModules($this->upgrader);

        $scriptObject->beanList = array();
        $scriptObject->beanFiles = array();
        $scriptObject->beanList[$this->moduleName] = $this->moduleName;
        $scriptObject->beanFiles[$this->moduleName] = "{$this->directory}/TestModule.php";

        $result = SugarTestReflection::callProtectedMethod(
            $scriptObject,
            'isMBModule',
            array("modules/{$this->moduleName}")
        );

        $this->assertEquals($shouldBeMB, $result);
    }
}
