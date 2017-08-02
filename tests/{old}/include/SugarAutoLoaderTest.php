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


class SugarAutoLoaderTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $todelete = array();

    public static function tearDownAfterClass()
	{
		// rebuild the map JIC
		SugarAutoLoader::buildCache();
	}

	public function tearDown()
	{
	    foreach($this->todelete as $file) {
	        if(is_dir($file)) {
                rmdir_recursive($file);
                SugarAutoLoader::delFromMap($file, false);
                continue;
	        }
	        @SugarAutoLoader::unlink($file);
	    }
	    $this->todelete = array();
	}

	protected function touch($file)
	{
	    $this->todelete[] = $file;
	    SugarAutoLoader::touch($file);
	}

	protected function put($file, $data)
	{
		$this->todelete[] = $file;
		SugarAutoLoader::put($file, $data);
	}

	public function testFileExists()
	{
	    $this->assertTrue(SugarAutoLoader::fileExists("index.php"));
	    $this->assertTrue(SugarAutoLoader::fileExists("custom///modules"));
	}

	public function testExisting()
	{
	     $this->assertEquals(array('index.php'), SugarAutoLoader::existing("index.php", "index-foo.php"));
	}

	public function testNotExisting()
	{
		$this->assertEmpty(SugarAutoLoader::existing("nosuchfile.test1"));
	}

	public function testAdd()
	{
	    $this->assertEmpty(SugarAutoLoader::existing("nosuchfile.test2", "some/dir/nosuchfile.test3"));
	    SugarAutoLoader::addToMap("nosuchfile.test2");
	    $this->assertTrue(SugarAutoLoader::fileExists("nosuchfile.test2"));
	    SugarAutoLoader::addToMap("some/dir/nosuchfile.test3");
	    $this->assertTrue(SugarAutoLoader::fileExists("some/dir/nosuchfile.test3"));
	}

	public function testDel()
	{
	    $this->assertEmpty(SugarAutoLoader::existing("nosuchfile.test4", "some/dir/nosuchfile.test5"));
	    SugarAutoLoader::addToMap("nosuchfile.test4");
	    SugarAutoLoader::addToMap("some/dir/nosuchfile.test5");
	    $this->assertTrue(SugarAutoLoader::fileExists("some/dir/nosuchfile.test5"));
        SugarAutoLoader::delFromMap("nosuchfile.test4", false);
        SugarAutoLoader::delFromMap("some/dir/nosuchfile.test5", false);
        $this->assertEmpty(SugarAutoLoader::existing("nosuchfile.test4", "some/dir/nosuchfile.test5"));
	}

	// load
	public function testLoad()
	{
        $res = SugarAutoLoader::load("include/JSON.php");
        $this->assertTrue($res);
        // test second time still returns true
        $res = SugarAutoLoader::load("include/JSON.php");
        $this->assertTrue($res);
        // not existing
        $res = SugarAutoLoader::load("nosuchfile.php");
        $this->assertFalse($res);
	}

    // requireWithCustom
    public function testRequireWithCustom()
    {
        $this->put("_test.php", "<?php class TestAutoLoader {}");
        SugarAutoLoader::requireWithCustom("_test.php");
        $this->assertTrue(class_exists("TestAutoLoader"), "Class TestAutoLoader does not exist!");
        $this->assertFalse(class_exists("CustomTestAutoLoader"), "Class TestAutoLoader should not exist!");

        $this->put("custom/_test.php", "<?php class CustomTestAutoLoader {}");
        SugarAutoLoader::requireWithCustom("_test.php");
        $this->assertTrue(class_exists("CustomTestAutoLoader"), "Class TestAutoLoader does not exist!");
    }

    // existingCustom
    public function testExistingCustom()
    {
        $this->touch("custom/index.php");
        $this->touch("custom/index2.php");
        $this->assertEquals(
            array('index.php', "custom/index.php", "custom/index2.php"),
            SugarAutoLoader::existingCustom("index.php", "index2.php", "index-foo.php"));
    }

    // existingCustomOne
    public function testExistingCustomOne()
    {
        // none
        $this->assertEmpty( SugarAutoLoader::existingCustomOne("index-foo.php", "blah.php"));
        // only root
        $this->assertEquals("index.php",  SugarAutoLoader::existingCustomOne("index.php", "index2.php", "index-foo.php"));
        // only custom
        $this->touch("custom/index2.php");
        $this->assertEquals("custom/index2.php",  SugarAutoLoader::existingCustomOne("index.php", "index2.php", "index-foo.php"));
        // custom & root
        $this->touch("index2.php");
        $this->assertEquals("custom/index2.php",  SugarAutoLoader::existingCustomOne("index.php", "index2.php", "index-foo.php"));
    }

    // getDirFiles
    public function testGetDirFiles()
    {
        $this->touch("custom/blah1.php");
        $this->touch("custom/blah2.php");
        $this->touch("custom/blah3.php");
        $res = SugarAutoLoader::getDirFiles("custom");
        $this->assertContains("custom/blah1.php", $res);
        $this->assertContains("custom/blah2.php", $res);
        $this->assertContains("custom/blah3.php", $res);
        // directories
        $res = SugarAutoLoader::getDirFiles("custom/", true);
        $this->assertContains("custom/modules", $res);
    }

    // getDirFilesExt
    public function testGetDirFilesExt()
    {
    	$this->touch("custom/blah1.php");
    	$this->touch("custom/blah2.txt");
    	$this->touch("custom/blah3.php");
    	$res = SugarAutoLoader::getDirFiles("custom", false, ".php");
    	$this->assertContains("custom/blah1.php", $res);
    	$this->assertNotContains("custom/blah2.txt", $res);
    	$this->assertContains("custom/blah3.php", $res);
    	$res = SugarAutoLoader::getDirFiles("custom", false, "txt");
    	$this->assertContains("custom/blah2.txt", $res);
    }

    // getFilesCustom
    public function testGetFilesCustom()
    {
    	$this->touch("custom/include/blah1.php");
    	$this->touch("include/blah2.php");
    	$this->touch("include/blah3.php");
    	$this->touch("custom/include/blah3.php");
    	$res = SugarAutoLoader::getFilesCustom("include");

    	$this->assertContains("custom/include/blah1.php", $res);
    	$this->assertContains("include/blah2.php", $res);
    	$this->assertContains("include/blah3.php", $res);
    	$this->assertContains("custom/include/blah3.php", $res);
        // directories
        if(!is_dir("custom/include/language")) {
            mkdir_recursive("custom/include/language");
            SugarAutoLoader::addToMap("custom/include/language/dummy.php");
        }
    	$res = SugarAutoLoader::getFilesCustom("include", true);
    	$this->assertContains("include/utils", $res);
    	$this->assertContains("custom/include/language", $res);
    }

    // customClass
    public function testCustomClass()
    {
        $this->assertEquals("BlahBlahNotExisting", SugarAutoLoader::customClass("BlahBlahNotExisting"));
        $this->assertEquals("Exception", SugarAutoLoader::customClass("Exception"));
        $this->put("custom/_test.php", "<?php class CustomTestAutoLoader {}");
        SugarAutoLoader::requireWithCustom("_test.php");
        $this->assertEquals("CustomTestAutoLoader", SugarAutoLoader::customClass("TestAutoLoader"));
    }

    // lookupFile
    public function testLookupFile()
    {
        $this->touch("custom/include/blah1.php");
        $this->assertEquals("custom/include/blah1.php", SugarAutoLoader::lookupFile(array("modules", "include", "Zend"), "blah1.php"));
        $this->touch("include/blah2.php");
        $this->assertEquals("include/blah2.php", SugarAutoLoader::lookupFile(array("modules", "include", "Zend"), "blah2.php"));
        $this->touch("custom/include/blah2.php");
        $this->assertEquals("custom/include/blah2.php", SugarAutoLoader::lookupFile(array("modules", "include", "Zend"), "blah2.php"));
    }

    // touch & unlink
    public function testTouchUnlink()
    {
        $this->todelete[] = "custom/testunlink.php";
        SugarAutoLoader::touch("custom/testunlink.php");
        $this->assertTrue(file_exists("custom/testunlink.php"), "File does not exist!");
        $this->assertTrue(SugarAutoLoader::fileExists("custom/testunlink.php"), "File does not exist in the map!");
        SugarAutoLoader::unlink("custom/testunlink.php");
        $this->assertFalse(file_exists("custom/testunlink.php"), "File should not exist!");
        $this->assertFalse(SugarAutoLoader::fileExists("custom/testunlink.php"), "File should not exist in the map!");
        array_pop($this->todelete);
    }

    // put & unlink
    public function testPutUnlink()
    {
        $this->todelete[] = "custom/testunlink.php";
        SugarAutoLoader::put("custom/testunlink.php", "TESTDATA");
        $this->assertTrue(file_exists("custom/testunlink.php"), "File does not exist!");
        $this->assertEquals("TESTDATA", file_get_contents("custom/testunlink.php"));
        $this->assertTrue(SugarAutoLoader::fileExists("custom/testunlink.php"), "File does not exist in the map!");
        SugarAutoLoader::unlink("custom/testunlink.php");
        $this->assertFalse(file_exists("custom/testunlink.php"), "File should not exist!");
        $this->assertFalse(SugarAutoLoader::fileExists("custom/testunlink.php"), "File should not exist in the map!");
        array_pop($this->todelete);
    }

    // loadExtension
    public function testLoadExtension()
    {
        mkdir_recursive("custom/modules/AutoLoaderTest/Ext/Layoutdefs/");
        $this->touch("custom/modules/AutoLoaderTest/Ext/Layoutdefs/layoutdefs.ext.php");
        $this->todelete[] = "custom/modules/AutoLoaderTest/";
        $this->assertEquals("custom/modules/AutoLoaderTest/Ext/Layoutdefs/layoutdefs.ext.php", SugarAutoLoader::loadExtension("layoutdefs", "AutoLoaderTest"));
        $this->assertEmpty(SugarAutoLoader::loadExtension("vardefs", "AutoLoaderTest"));
        if(!file_exists("custom/application/Ext/Layoutdefs/layoutdefs.ext.php")) {
            mkdir_recursive("custom/application/Ext/Layoutdefs/");
            $this->touch("custom/application/Ext/Layoutdefs/layoutdefs.ext.php");
        }
        $this->assertEquals("custom/application/Ext/Layoutdefs/layoutdefs.ext.php", SugarAutoLoader::loadExtension("layoutdefs"));
        if(!file_exists("custom/modules/Schedulers/Ext/ScheduledTasks/scheduledtasks.ext.php")) {
            mkdir_recursive("custom/modules/Schedulers/Ext/ScheduledTasks/");
            $this->touch("custom/modules/Schedulers/Ext/ScheduledTasks/scheduledtasks.ext.php");
        }
        $this->assertEquals("custom/modules/Schedulers/Ext/ScheduledTasks/scheduledtasks.ext.php", SugarAutoLoader::loadExtension("schedulers", "AutoLoaderTest"));
    }

    // loadWithMetafiles
    public function testLoadWithMetafiles()
    {
        /*
        * 1. Check custom/module/metadata/$varname.php
        * 2. If not there, check metafiles.php
        * 3. If still not found, use module/metadata/$varname.php
        */
        mkdir_recursive("custom/modules/AutoLoaderTest/metadata");
        mkdir_recursive("modules/AutoLoaderTest/metadata");
        $this->todelete[] = "custom/modules/AutoLoaderTest/";
        $this->todelete[] = "modules/AutoLoaderTest/";

        $this->assertEmpty(SugarAutoLoader::loadWithMetafiles("AutoLoaderTest", "editviewdefs"));

        // root
        $this->touch("modules/AutoLoaderTest/metadata/editviewdefs.php");
        $this->assertEquals("modules/AutoLoaderTest/metadata/editviewdefs.php", SugarAutoLoader::loadWithMetafiles("AutoLoaderTest", "editviewdefs"));
        $this->assertEmpty(SugarAutoLoader::loadWithMetafiles("AutoLoaderTest", "editviewdefsblah", "editviewdefs"));

        // metafiles
        $metafiles['AutoLoaderTest'] = array("editviewdefs" => "modules/AutoLoaderTest/metadata/meta-editviewdefs.php");
        $this->put("modules/AutoLoaderTest/metadata/metafiles.php", "<?php \$metafiles = ".var_export($metafiles, true).";");
        $this->assertEquals("modules/AutoLoaderTest/metadata/editviewdefs.php",
        SugarAutoLoader::loadWithMetafiles("AutoLoaderTest", "editviewdefs"));
        $this->assertEmpty(SugarAutoLoader::loadWithMetafiles("AutoLoaderTest", "editviewdefsblah", "editviewdefs"));
        // now create meta-defs
        $this->touch("modules/AutoLoaderTest/metadata/meta-editviewdefs.php");
        $this->assertEquals("modules/AutoLoaderTest/metadata/meta-editviewdefs.php",
        SugarAutoLoader::loadWithMetafiles("AutoLoaderTest", "editviewdefs"));

        // now custom
        $this->touch("custom/modules/AutoLoaderTest/metadata/editviewdefs.php");
        $this->assertEquals("custom/modules/AutoLoaderTest/metadata/editviewdefs.php",
        SugarAutoLoader::loadWithMetafiles("AutoLoaderTest", "editviewdefs"));
        // other def
        $this->touch("modules/AutoLoaderTest/metadata/detailviewdefs.php");
        $this->assertEquals("modules/AutoLoaderTest/metadata/detailviewdefs.php",
        SugarAutoLoader::loadWithMetafiles("AutoLoaderTest", "detailviewdefs"));

    }

    // loadPopupMeta
    public function testLoadPopupMeta()
    {
        mkdir_recursive("custom/modules/AutoLoaderTest/metadata");
        $this->todelete[] = "custom/modules/AutoLoaderTest/";

        $this->assertEmpty(SugarAutoLoader::loadPopupMeta("AutoLoaderTest", "editviewdefs"));
        // popup
        $this->put("custom/modules/AutoLoaderTest/metadata/popupdefs.php", "<?php \$popupMeta = 'TEST1'; ");
        $this->assertEquals("TEST1", SugarAutoLoader::loadPopupMeta("AutoLoaderTest", "editviewdefs"));
        // other
        $this->put("custom/modules/AutoLoaderTest/metadata/otherdefs.php", "<?php \$popupMeta = 'TEST2'; ");
        $this->assertEquals("TEST2", SugarAutoLoader::loadPopupMeta("AutoLoaderTest", "otherdefs"));
    }

    public function ensureDirTest()
    {
        SugarAutoLoader::ensureDir("custom/testdir/testdir2");
        $this->todelete[] = "custom/testdir";

        $this->asserTrue(is_dir("custom/testdir/testdir2"), "test dir create failed");
        $this->asserTrue(SugarAutoLoader::fileExists("custom/testdir/testdir2"), "test dir not in cache");

        SugarAutoLoader::put("custom/testdir/testdir2/testfile.php", "test");
        $this->asserTrue(SugarAutoLoader::fileExists("custom/testdir/testdir2/testfile.php"), "test file not in cache");

    }

    /**
     * @dataProvider providerPaths
     */
    public function testNormalizeFilePath($baseDir, $fileName, $ds, $expected)
    {
        $baseDirsOriginal = SugarAutoLoaderMock::getBaseDirs();
        SugarAutoLoaderMock::setDs($ds);

        SugarAutoLoaderMock::setBaseDirs(array($baseDir));
        $path = SugarAutoLoaderMock::normalizeFilePath($fileName);

        // Should convert absolute path to relative
        $this->assertEquals($expected, $path);

        SugarAutoLoaderMock::setBaseDirs($baseDirsOriginal);
        SugarAutoLoaderMock::setDs(DIRECTORY_SEPARATOR);
    }

    public function providerPaths()
    {
        return array(
            // Windows network path
            array(
                // All slashes are converted to forward slashes, due to entryPoint.php
                "//VMSTACK127/WWWROOT/SugarPro-Full-7.6.1.0",
                "\\\\VMSTACK127\\WWWROOT\\SugarPro-Full-7.6.1.0/include",
                "\\",
                "include"
            ),
            // Windows local path
            array(
                "C:/inetpub/wwwroot/SugarPro-Full-7.6.1.0",
                "C:\\inetpub\\wwwroot/SugarPro-Full-7.6.1.0/include",
                "\\",
                "include"
            ),
            // Linux/UNIX path
            array(
                "/Users/boro/dev/www/sugar1.com",
                "/Users/boro/dev/www/sugar1.com/include",
                "/",
                "include"
            )
        );
    }
}

/**
 * Mock class for SugarAutoLoader
 */
class SugarAutoLoaderMock extends SugarAutoLoader
{
    public static function getBaseDirs()
    {
        return self::$baseDirs;
    }

    public static function setBaseDirs($value)
    {
        self::$baseDirs = $value;
    }

    public static function setDs($value)
    {
        self::$ds = $value;
    }
}
