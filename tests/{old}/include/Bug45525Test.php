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

class Bug45525 extends Sugar_PHPUnit_Framework_TestCase
{
   
    /**
     * @group Bug45525
     */
    var $testLangFile = "cache/upload/myLang.php";

    public function setUp()
    {
        if(!file_exists('cache/upload'))
        {
            mkdir_recursive('cache/upload');
        }
    }


    public function tearDown()
    {
    }

    public function testOverwriteDropDown()
    {
      global $app_list_strings;
      $app_list_strings = array("TestList" => array ("A" => "Option A", "B" => "Option B", "C" => "Option C"));

      require_once 'include/utils.php';

      file_put_contents($this->testLangFile, '<?php
        $app_list_strings[\'TestList\'] = array(\'D\' => \'Option D\');
        ?>');

      // Initially TestList should have 3 items
      $this->assertEquals(3, count($app_list_strings['TestList']));

      $app_list_strings = _mergeCustomAppListStrings($this->testLangFile, $app_list_strings);

      // After merge with custom language file, TestList should have just 1 item (standard behaviour)
      $this->assertEquals(1, count($app_list_strings['TestList']));

      unlink($this->testLangFile);

      unset($GLOBALS['app_list_strings']);
    }

    public function testAppendDropDown()
    {
      global $app_list_strings;
      $app_list_strings = array("TestList" => array ("A" => "Option A", "B" => "Option B", "C" => "Option C"));

      require_once 'include/utils.php';

      file_put_contents($this->testLangFile, "<?php\n\$exemptDropdowns[] = 'TestList';\n\$app_list_strings['TestList']['D'] = 'Option D';\n?>");

      // Initially TestList should have 3 items
      $this->assertEquals(3, count($app_list_strings['TestList']));

      $app_list_strings = _mergeCustomAppListStrings($this->testLangFile, $app_list_strings);

      // After merge with custom language file, TestList should have 4 items (after-fix behaviour)
      $this->assertEquals(4, count($app_list_strings['TestList']));

      unlink($this->testLangFile);

      unset($GLOBALS['app_list_strings']);
    }

}

