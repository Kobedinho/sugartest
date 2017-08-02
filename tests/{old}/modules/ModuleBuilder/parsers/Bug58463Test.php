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
 * Bug 58463 - Drop Down Lists do not show in studio after save
 */
class Bug58463Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_testCustomFile = 'custom/application/Ext/Language/en_us.lang.ext.php';
    protected $_currentRequest;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');

        // Back up the current file if there is one
        if (file_exists($this->_testCustomFile)) {
            rename($this->_testCustomFile, $this->_testCustomFile . '.testbackup');
        } else {
            SugarAutoLoader::addToMap($this->_testCustomFile);
        }

        // Create an empty test custom file
        mkdir_recursive(dirname($this->_testCustomFile));
        sugar_file_put_contents($this->_testCustomFile, '<?php' . "\n");

        // Back up the current request vars
        $this->_currentRequest = $_REQUEST;
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();

        // Clean up our file
        unlink($this->_testCustomFile);

        if (file_exists($this->_testCustomFile . '.testbackup')) {
            rename($this->_testCustomFile . '.testbackup', $this->_testCustomFile);
        } else {
            SugarAutoLoader::delFromMap($this->_testCustomFile);
        }

        // Reset the request
        $_REQUEST = $this->_currentRequest;

        // Clear the cache
        sugar_cache_clear('app_list_strings.en_us');
    }

    /**
     * @group Bug58463
     */
    public function testCustomDropDownListSavesProperly()
    {
        $values = array(
            array('bobby', 'Bobby'),
            array('billy', 'Billy'),
            array('benny', 'Benny'),
        );

        $_REQUEST = array(
            'list_value' => json_encode($values),
            'dropdown_lang' => 'en_us',
            'dropdown_name' => 'test_dropdown',
            'view_package' => 'studio',
        );
        $parser = new ParserDropDown();
        $parser->saveDropDown($_REQUEST);

        $als = $this->_getCustomDropDownEntry();
        $this->assertArrayHasKey('test_dropdown', $als, "The dropdown did not save");
        foreach ($values as $item) {
            $this->assertArrayHasKey($item[0], $als['test_dropdown'], "The dropdown list item {$item[0]} did not save");
        }
    }

    protected function _getCustomDropDownEntry()
    {
        if (file_exists($this->_testCustomFile)) {
            require $this->_testCustomFile;
            if (isset($app_list_strings)) {
                return $app_list_strings;
            }
        }

        // This would indicate a failure
        return array();
    }
}
