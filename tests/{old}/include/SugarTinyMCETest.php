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
 * Original Bug: 27655
 *
 * This test was expanded to hit both major paths in this file.
 *
 */
class SugarTinyMCETest extends Sugar_PHPUnit_Framework_TestCase
{

    static $customDir = 'custom/include';
    static $customConfigFile = 'custom/include/tinyButtonConfig.php';
    static $customDefaultConfigFile = 'custom/include/tinyMCEDefaultConfig.php';
    static $MCE;

    /*
     * Setup: Backup old custom files and create new ones for the test
     */
    public static function setUpBeforeClass()
    {

        SugarAutoLoader::ensureDir(self::$customDir);

        if (file_exists(self::$customConfigFile)) {
            rename(self::$customConfigFile, self::$customConfigFile . ".bak");
        }

        if (file_exists(self::$customDefaultConfigFile)) {
            rename(self::$customDefaultConfigFile, self::$customDefaultConfigFile . ".bak");
        }

        SugarAutoLoader::put(
            self::$customConfigFile,
            "<?php
            \$buttonConfigs = array('default' => array('buttonConfig' =>'testcase',
                                    'buttonConfig2' => 'cut,copy,paste,pastetext,pasteword,selectall,separator,search,replace,separator,bullist,numlist,separator,outdent,
                                             indent,separator,ltr,rtl,separator,undo,redo,separator, link,unlink,anchor,image,separator,sub,sup,separator,charmap,
                                             visualaid',
                                    'buttonConfig3' => 'tablecontrols,separator,advhr,hr,removeformat,separator,insertdate,inserttime,separator,preview'),
                                    'badkey1' => 'bad data1');
            ?>"
        );

        SugarAutoLoader::put(
            self::$customDefaultConfigFile,
            "<?php
            \$defaultConfig = array('extended_valid_elements' => 'upload[testlength|ratio|initialtest|mintestsize|threads|maxchunksize|maxchunkcount],download[testlength|initialtest|mintestsize|threads|maximagesize]',
                                                                 'badkey2' => 'bad data2');
            ?>"
        );
        $tinySugar = new SugarTinyMCE();
        self::$MCE = $tinySugar->getInstance();

    }

    /*
     * Teardown: remove new custom files and restore the previous ones
     */
    public static function tearDownAfterClass()
    {
        if (file_exists(self::$customConfigFile . ".bak")) {
            unlink(self::$customConfigFile);
            rename(self::$customConfigFile . ".bak", self::$customConfigFile);
        } else {
            SugarAutoLoader::unlink(self::$customConfigFile);
        }
        if (file_exists(self::$customDefaultConfigFile . ".bak")) {
            unlink(self::$customDefaultConfigFile);
            rename(self::$customDefaultConfigFile . ".bak", self::$customDefaultConfigFile);
        } else {
            SugarAutoLoader::unlink(self::$customDefaultConfigFile);
        }
    }

    public function testCheckValidCustomButtonOverrdide()
    {
        $this->assertContains("testcase", self::$MCE, "TinyMCE custom button not found.");
    }

    public function testCheckInvalidCustomButtonOverrdide()
    {
        $pos = strpos("badkey1", self::$MCE);
        if ($pos === false) {
            $pos = 0;
        }
        $this->assertEquals(0, $pos, "Invalid custom button found. Stripping code failed.");
    }

    public function testCheckValidDefaultOverrdide()
    {
        $this->assertContains("download", self::$MCE, "TinyMCE custom config not found.");
    }

    public function testCheckInvalidDefaultOverrdide()
    {
        $pos = strpos("badkey2", self::$MCE);
        if ($pos === false) {
            $pos = 0;
        }
        $this->assertEquals(0, $pos, "Invalid custom config found. Stripping code failed.");
    }
}
