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
 * @ticket 66276
 */
class Bug66276Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $testFiles = array(
        array('dir' => 'custom/include', 'name' => 'QuickSearchDefaults.php', 'content' => '<?php class QuickSearchDefaultsCustom {}'),
        array('dir' => 'custom/modules/Test', 'name' => 'QuickSearchDefaults.php', 'content' => '<?php class QuickSearchDefaultsModule {}'));

    public function setUp()
    {
        foreach ($this->testFiles as $testFile) {
            if (!file_exists($testFile['dir'])) {
                sugar_mkdir($testFile['dir'], 0777, true);
            }

            SugarAutoLoader::put($testFile['dir'] . '/' . $testFile['name'], $testFile['content'], true);
        }
    }

    public function tearDown()
    {
        foreach ($this->testFiles as $testFile) {
            if (SugarAutoLoader::fileExists($testFile['dir'] . '/' . $testFile['name'])) {
                SugarAutoLoader::unlink($testFile['dir'] . '/' . $testFile['name'], true);
            }
        }
    }

    /**
     * Tests function QuickSearchDefaults::getQuickSearchDefaults()
     */
    public function testGetQuickSearchDefaults()
    {
        $this->assertInstanceOf('QuickSearchDefaultsModule', QuickSearchDefaults::getQuickSearchDefaults(array('custom/modules/Test/QuickSearchDefaults.php'=>'QuickSearchDefaultsModule')));
        $this->assertInstanceOf('QuickSearchDefaultsCustom', QuickSearchDefaults::getQuickSearchDefaults());
        SugarAutoLoader::unlink('custom/include/QuickSearchDefaults.php', true);
        $this->assertInstanceOf('QuickSearchDefaults', QuickSearchDefaults::getQuickSearchDefaults());
    }
}
