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

class SugarFileUtilsTest extends Sugar_PHPUnit_Framework_TestCase 
{
    private $_filename;
    private $_old_default_permissions;
    private $testDirectory;
    
    public function setUp() 
    {	
        if (is_windows())
            $this->markTestSkipped('Skipping on Windows');
        
        $this->_filename = realpath(dirname(__FILE__).'/../../../cache/').'file_utils_override'.mt_rand().'.txt';
        touch($this->_filename);
        $this->_old_default_permissions = $GLOBALS['sugar_config']['default_permissions'];
        $GLOBALS['sugar_config']['default_permissions'] =
            array (
                'dir_mode' => 0777,
                'file_mode' => 0660,
                'user' => $this->_getCurrentUser(),
                'group' => $this->_getCurrentGroup(),
              );

        $this->testDirectory = $GLOBALS['sugar_config']['cache_dir'] . md5($GLOBALS['sugar_config']['cache_dir']) . '/';
    }
    
    public function tearDown() 
    {
        if(file_exists($this->_filename)) {
            unlink($this->_filename);
        }

        $this->recursiveRmdir($this->testDirectory);

        $GLOBALS['sugar_config']['default_permissions'] = $this->_old_default_permissions;
        SugarConfig::getInstance()->clearCache();
    }
    
    private function _getCurrentUser()
    {
        if ( function_exists('posix_getuid') ) {
            return posix_getuid();
        }
        return '';
    }
    
    private function _getCurrentGroup()
    {
        if ( function_exists('posix_getgid') ) {
            return posix_getgid();
        }
        return '';
    }
    
    private function _getTestFilePermissions()
    {
        return substr(sprintf('%o', fileperms($this->_filename)), -4);
    }
    
    public function testSugarTouch()
    {
        $this->assertTrue(sugar_touch($this->_filename));
    }
    
    public function testSugarTouchWithTime()
    {
        $time = filemtime($this->_filename);
        
        $this->assertTrue(sugar_touch($this->_filename, $time));
        
        $this->assertEquals($time,filemtime($this->_filename));
    }
    
    public function testSugarTouchWithAccessTime()
    {
        $time  = filemtime($this->_filename);
        $atime = time();
        
        $this->assertTrue(sugar_touch($this->_filename, $time, $atime));
        
        $this->assertEquals($time,filemtime($this->_filename));
        $this->assertEquals($atime,fileatime($this->_filename));
    }
    
    public function testSugarChmod()
    {
        $this->assertTrue(sugar_chmod($this->_filename));
        $this->assertEquals($this->_getTestFilePermissions(), decoct(get_mode('file_mode')));
    }
    
    public function testSugarChmodWithMode()
    {
        $mode = 0411;
        $this->assertTrue(sugar_chmod($this->_filename, $mode));
        
        $this->assertEquals($this->_getTestFilePermissions(),decoct($mode));
    }
    
    public function testSugarChmodNoDefaultMode()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = null;

        $this->assertTrue(sugar_chmod($this->_filename));
        $this->assertEquals($this->_getTestFilePermissions(), decoct(get_mode('file_mode')));
    }
    
    public function testSugarChmodDefaultModeNotAnInteger()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = '';
        $this->assertFalse(sugar_chmod($this->_filename));
    }
    
    public function testSugarChmodDefaultModeIsZero()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = 0;
        $this->assertFalse(sugar_chmod($this->_filename));
    }
    
    public function testSugarChmodWithModeNoDefaultMode()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = null;
        $mode = 0411;
        $this->assertTrue(sugar_chmod($this->_filename, $mode));
        
        $this->assertEquals($this->_getTestFilePermissions(),decoct($mode));
    }
    
    public function testSugarChmodWithModeDefaultModeNotAnInteger()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = '';
        $mode = 0411;
        $this->assertTrue(sugar_chmod($this->_filename, $mode));
        
        $this->assertEquals($this->_getTestFilePermissions(),decoct($mode));
    }
    
    public function testSugarChown()
    {
        if ($GLOBALS['sugar_config']['default_permissions']['user'] == '')
        {
            $this->markTestSkipped('Can not get UID. Posix extension is required.');
        }
        $this->assertTrue(sugar_chown($this->_filename));
        $this->assertEquals(fileowner($this->_filename),$this->_getCurrentUser());
    }
    
    public function testSugarChownWithUser()
    {
        if ($this->_getCurrentUser() == '')
        {
            $this->markTestSkipped('Can not get UID. Posix extension is required.');
        }
        $this->assertTrue(sugar_chown($this->_filename,$this->_getCurrentUser()));
        $this->assertEquals(fileowner($this->_filename),$this->_getCurrentUser());
    }
    
    public function testSugarChownNoDefaultUser()
    {
        $GLOBALS['sugar_config']['default_permissions']['user'] = '';
        
        $this->assertFalse(sugar_chown($this->_filename));
    }
    
    public function testSugarChownWithUserNoDefaultUser()
    {
        if ($this->_getCurrentUser() == '')
        {
            $this->markTestSkipped('Can not get UID. Posix extension is required.');
        }

        $GLOBALS['sugar_config']['default_permissions']['user'] = '';
        
        $this->assertTrue(sugar_chown($this->_filename,$this->_getCurrentUser()));
        
        $this->assertEquals(fileowner($this->_filename),$this->_getCurrentUser());
    }

    public function testSugarTouchDirectoryCreation()
    {
        $this->recursiveRmdir($this->testDirectory);

        $this->assertEquals(false, is_dir($this->testDirectory), 'Directory exists, though we removed it');

        $file = $this->testDirectory . md5($this->testDirectory);
        sugar_touch($file);

        $this->assertFileExists($file, "File should be created together with directory");
    }

    private function recursiveRmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        $this->recursiveRmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
