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
 * @ticket 46740
 */
class Bug46740Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Language used to perform the test
     *
     * @var string
     */
    protected $language;

    /**
     * Module to be renamed
     *
     * @var string
     */
    protected $module = 'Contracts';

    /**
     * Module name translation
     *
     * @var string
     */
    protected $translation = 'ContractsBug46740Test';

    /**
     * Temporary file path
     *
     * @var string
     */
    protected $file = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Generates custom module localization file
     */
    public function setUp()
    {
        SugarTestHelper::setUp('moduleList');
        global $sugar_config;
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user');
        $this->language = $sugar_config['default_language'];

        // create custom localization file
        $this->file = 'custom/include/language/' . $this->language . '.lang.php';

        if (file_exists($this->file)) {
            rename($this->file, $this->file . '.bak');
        }

        $dirName = dirname($this->file);
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }

        $contents = <<<FILE
<?php
\$app_list_strings["moduleList"]["{$this->module}"] = "{$this->translation}";
FILE;

        file_put_contents($this->file, $contents);
        SugarAutoLoader::addToMap($this->file, false);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Removes custom module localization file
     */
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        if (file_exists($this->file . '.bak')) {
            rename($this->file . '.bak', $this->file);
        } else {
            unlink($this->file);
            SugarAutoLoader::delFromMap($this->file, false);
        }
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that custom module localization data is used
     */
    public function testCustomModuleLocalizationIsUsed()
    {
        global $sugar_flavor, $server_unique_key, $current_language;
        $app_list_strings = return_app_list_strings_language($this->language, false);

        $admin_group_header = array();
        require 'modules/Administration/metadata/adminpaneldefs.php';

        $found = false;
        foreach ($admin_group_header as $header)
        {
            $headerGroup = array_shift($header);
            if ($headerGroup === $this->translation)
            {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
