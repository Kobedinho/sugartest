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

class ParserDropdownTest extends Sugar_PHPUnit_Framework_TestCase
{
    // Custom include/language file path
    private $customFile;

    // Custom modlist file path
    private $customModList;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->customFile =  'custom/include/language/' . $GLOBALS['current_language'] . '.lang.php';
        if (file_exists($this->customFile)) {
            $this->fileBackup = file_get_contents($this->customFile);
        }

        $this->customModList = 'custom/Extension/application/Ext/Language/' . $GLOBALS['current_language'] . '.sugar_moduleList.php';
        if (file_exists($this->customModList)) {
            $this->modListBackup = file_get_contents($this->customModList);
            SugarAutoLoader::unlink($this->customModList, true);
        }

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
    }

    public function tearDown()
    {
        if (isset($this->fileBackup)) {
            file_put_contents($this->customFile, $this->fileBackup);
        } elseif (file_exists($this->customFile)) {
            SugarAutoLoader::unlink($this->customFile, true);
        }

        if (isset($this->modListBackup)) {
            file_put_contents($this->customModList, $this->modListBackup);
        }

        // Clear cache so it can be reloaded later
        $cache_key = 'app_list_strings.' . $GLOBALS['current_language'];
        sugar_cache_clear($cache_key);

        // Reload app_list_strings
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        
        $_REQUEST = array();
        
        SugarTestHelper::tearDown();
    }

    /**
     *
     */
    public function testSavingEmptyLabels()
    {
        $_REQUEST['view_package'] = 'studio';
        $params = array(
            'dropdown_name' => 'moduleList',
            'list_value' => '',
            'skipSaveExemptDropdowns' => true,
        );

        $parser = $this->createPartialMock('ParserDropDown', array('saveExemptDropdowns', 'synchDropDown', 'saveContents', 'finalize'));
        $parser->expects($this->never())->method('saveExemptDropdowns');
        $parser->saveDropDown($params);
    }

    /**
     * Check if saveExemptDropdowns works as expected
     * This method should set a NULL value to keys that were deleted on exempt dropdowns
     *
     * @param $dropdownValues New dropdown values
     * @param $dropdownName Dropdown key
     * @param $appListStrings Old app_list_strings, containing old dropdown values
     * @param $customFileContents Contents of custom/include/language file
     * @param $expected Dropdown returned from saveExemptDropdowns call
     *
     * @dataProvider providerTestSaveExemptDropdowns
     */
    public function testSaveExemptDropdowns(
        $dropdownValues,
        $dropdownName,
        $appListStrings,
        $customFileContents,
        $expected
    ) {

        $dirName = dirname($this->customFile);
        SugarAutoLoader::ensureDir($dirName);
        SugarAutoLoader::put($this->customFile, $customFileContents);

        $parser = new ParserDropDown();
        $output = $parser->saveExemptDropdowns(
            $dropdownValues,
            $dropdownName,
            $appListStrings,
            $GLOBALS['current_language']
        );

        $this->assertEquals($expected, $output, 'Save Exempt Dropdowns not working properly.');
    }

    public static function providerTestSaveExemptDropdowns()
    {
        return array(
            // Check if non-exempt dropdowns are just passed through
            array(
                array(
                    0 => 'test 0',
                ),
                'test',
                array(
                    'test' => array(
                        0 => 'test 0',
                        1 => 'test 1',
                    ),
                ),
                "",
                array(
                    0 => 'test 0',
                ),
            ),
            // Check if deleted exempt dropdown values are NULL
            array(
                array(
                    0 => 'test 0',
                ),
                'parent_type_display',
                array(
                    'parent_type_display' => array(
                        0 => 'test 0',
                        1 => 'test 1',
                    ),
                ),
                "",
                array(
                    0 => 'test 0',
                    1 => null,
                ),
            ),
            // Check if NULL values from custom/include/language file are copied over so we don't loose the keys
            array(
                array(
                    0 => 'test 0',
                ),
                'parent_type_display',
                array(
                    'parent_type_display' => array(
                        0 => 'test 0',
                        1 => 'test 1',
                        2 => 'test 2',
                    ),
                ),
                "<?php
                    \$app_list_strings['parent_type_display'] = array(
                        'ProjectTask' => 'Project Task',
                        'Prospects' => null,
                    );
                ",
                array(
                    0 => 'test 0',
                    1 => null,
                    2 => null,
                    'Prospects' => null,
                ),
            ),
        );
    }


    /**
     * @param $params
     * @param $existingFileContents
     * @param $expectedFileContents
     *
     * @dataProvider saveDropdownProvider
     */
    public function testSaveDropdown(
        $params,
        $existingFileContents,
        $expected
    ) {
        $lang = $GLOBALS['current_language'];
        $params['dropdown_lang'] = $lang;
        $dropdownName = $params['dropdown_name'];
        $this->customFile = "custom/Extension/application/Ext/Language/$lang.sugar_$dropdownName.php";
        if (!empty($existingFileContents)) {
            sugar_file_put_contents($this->customFile, $existingFileContents);
        }

        $_REQUEST['view_package'] = $params['view_package'];

        $parser = $this->getMockBuilder('ParserDropDown')
            ->disableOriginalConstructor()
            ->setMethods(array('finalize'))
            ->getMock();

        $parser->saveDropDown($params);

        if (!empty($expected)) {
            $this->assertFileExists($this->customFile);

            $app_list_strings = array();
            include($this->customFile);

            $this->assertSame($expected, $app_list_strings, 'Save Dropdown not working properly.');
        } else {
            $this->assertFileNotExists($this->customFile);
        }
    }


    public static function saveDropdownProvider()
    {
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        return array(
            //Add a new module with no existing customization
            array(
                //Params
                array(
                    'dropdown_name' => 'moduleList',
                    'list_value' => static::encodeList(array_merge(
                        $app_list_strings['moduleList'],
                        array('NewModule' => 'New Module'))
                    ),
                    'view_package' => 'studio',
                    'use_push' => true,
                ),
                '',
                array('moduleList' => array('NewModule' => 'New Module'))
            ),
            //Rename existing module
            array(
                //Params
                array(
                    'dropdown_name' => 'moduleList',
                    'list_value' => static::encodeList(array_merge(
                            $app_list_strings['moduleList'],
                            array('Accounts' => 'New Accounts')
                        )
                    ),
                    'view_package' => 'studio',
                    'use_push' => true,
                ),
                '',
                array('moduleList' => array('Accounts' => 'New Accounts'))
            ),
            //No change
            array(
                //Params
                array(
                    'dropdown_name' => 'moduleList',
                    'list_value' => static::encodeList($app_list_strings['moduleList']),
                    'view_package' => 'studio',
                    'use_push' => true,
                ),
                '',
                false
            ),
            //Keep existing customization
            array(
                //Params
                array(
                    'dropdown_name' => 'moduleList',
                    'list_value' => static::encodeList(array_merge(
                            $app_list_strings['moduleList'],
                            array('NewModule' => 'New Module')
                        )
                    ),
                    'view_package' => 'studio',
                    'use_push' => true,
                ),
                '<?php $app_list_strings[\'moduleList\'][\'foo\']=\'bar\';',
                array('moduleList' => array('foo' => 'bar', 'NewModule' => 'New Module'))
            )
        );
    }

    protected static function encodeList(Array $list)
    {
        $new_list = array();
        foreach ($list as $k => $v) {
            $new_list[] = array($k, $v);
        }

        return json_encode($new_list);
    }
}
