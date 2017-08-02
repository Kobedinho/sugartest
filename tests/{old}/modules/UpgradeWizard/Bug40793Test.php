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


require_once('modules/UpgradeWizard/uw_utils.php');

/**
 * @ticket 40793
 */
class Bug40793Test extends Sugar_PHPUnit_Framework_TestCase
{

    const WEBALIZER_DIR_NAME = 'bug40793';
    private $_notIncludeDir;
    private $_includeDir;

    public function setUp()
    {
        $this->_notIncludeDir = self::WEBALIZER_DIR_NAME . "/this_dir_should_not_include";
        $this->_includeDir = self::WEBALIZER_DIR_NAME . "/1";
        mkdir(self::WEBALIZER_DIR_NAME, 0755);
        mkdir($this->_notIncludeDir, 0755);
        mkdir($this->_includeDir, 0755);
    }

    public function tearDown()
    {
        rmdir($this->_notIncludeDir);
        rmdir($this->_includeDir);
        rmdir(self::WEBALIZER_DIR_NAME);
    }

    public function testIfDirIsNotIncluded()
    {
        $skipDirs = array($this->_notIncludeDir);
        $files = uwFindAllFiles( self::WEBALIZER_DIR_NAME, array(), true, $skipDirs);
        $this->assertNotContains($this->_notIncludeDir, $files, "Directory {$this->_notIncludeDir} shouldn't been included in this list");
        $this->assertContains($this->_includeDir, $files, "Directory {$this->_includeDir} should been included in this list");
    }
}