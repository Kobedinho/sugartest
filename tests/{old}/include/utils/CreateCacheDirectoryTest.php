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
 
require_once 'include/utils/file_utils.php';

class CreateCacheDirectoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_original_cwd = '';

    public function setUp()
    {
        global $sugar_config;
        $this->_original_cwd = getcwd();
        $this->_original_cachedir = $sugar_config['cache_dir'];
        $sugar_config['cache_dir'] = 'cache/';
        chdir(dirname(__FILE__));
        $this->_removeCacheDirectory('./cache');
    }

    public function tearDown()
    {
        $this->_removeCacheDirectory('./cache');
        chdir($this->_original_cwd);
        $sugar_config['cache_dir'] = $this->_original_cwd;
    }

    private function _removeCacheDirectory($dir)
    {
        $dir_handle = @opendir($dir);
        if ($dir_handle === false) {
            return;
        }
        while (($filename = readdir($dir_handle)) !== false) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            if (is_dir("{$dir}/{$filename}")) {
                $this->_removecacheDirectory("{$dir}/{$filename}");
            } else {
                unlink("{$dir}/{$filename}");
            }
        }
        closedir($dir_handle);
        rmdir("{$dir}");
    }

    public function testCreatesCacheDirectoryIfDoesnotExist()
    {
        $this->assertFalse(file_exists('./cache'), 'check that the cache directory does not exist');
        create_cache_directory('foobar');
        $this->assertTrue(file_exists('./cache'), 'creates a cache directory');
    }

    public function testCreatesDirectoryInCacheDirectoryProvidedItIsGivenAFile()
    {
        $this->assertFalse(file_exists('./cache/foobar-test'));
        create_cache_directory('foobar-test/cache-file.php');
        $this->assertTrue(file_exists('./cache/foobar-test'));
    }

    public function testReturnsDirectoryCreated()
    {
        $created = create_cache_directory('foobar/cache-file.php');
        $this->assertEquals(
            'cache/foobar/cache-file.php',
            $created
        );
    }
}

