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

class TrackersTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $service;
    /**
     * @var SugarConfig $sc
     */
    protected $sc;


    private $files = array(
        "tracker_perfvardefs.php",
        "tracker_queriesvardefs.php",
        "tracker_sessionsvardefs.php",
        "Trackervardefs.php",
    );

    public function setUp()
    {
        //Ensure the cache for all the vardef files must be rebuilt for each run.
        foreach($this->files as $file) {
            $path =  sugar_cached("modules/Trackers/" . $file);
            if (file_exists($path)) {
                SugarAutoLoader::unlink($path);
            }
        }

        $GLOBALS['reload_vardefs'] = true;
        SugarCache::$isCacheReset = false;
        //Force the use of the file system cache during this test.
        $this->sc = SugarConfig::getInstance();
        $this->sc->_cached_values['noFilesystemMetadataCache'] = false;

        SugarTestHelper::setUp("app_list_strings");

        $this->currentUserApi = new CurrentUserApi();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            @unlink($filepath);
        }
        $this->sc->clearCache('noFilesystemMetadataCache');
    }

    /**
     * Testing scenario found in PAT-1314
     */
    public function testCacheRewriteAPI()
    {
        $timestamp = array();
        new Tracker();
        new TrackerPerf();
        new TrackerQuery();
        new TrackerSession();

        // Store cached file timestamps
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            touch($filepath, 123);
            $this->assertFileExists($filepath, "Cache file '$file' does not exist.");
            $timestamp[$file] = filemtime($filepath);
        }

        // Call to /me
        $this->currentUserApi->retrieveCurrentUser($this->service, array());

        // Check if cache is re-created
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            $this->assertFileExists($filepath, "Cache file '$file' does not exist after REST API call.");
            // file does not have correct timestamp
            $this->assertEquals(
                $timestamp[$file],
                filemtime($filepath),
                "File '$file' is re-created on API call."
            );
        }
    }

    public function testCacheRewriteClassInstantiation()
    {
        unset($GLOBALS['dictionary']);
        $timestamp = array();
        new Tracker();
        new TrackerPerf();
        new TrackerQuery();
        new TrackerSession();

        // Store cached file timestamps
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            touch($filepath, 123);
            $this->assertFileExists($filepath, "Cache file '$file' does not exist.");
            $timestamp[$file] = filemtime($filepath);
        }

        // Instantiate classes
        unset($GLOBALS['dictionary']);
        new Tracker();
        new TrackerPerf();
        new TrackerQuery();
        new TrackerSession();

        // Check if cache is re-created
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            $this->assertFileExists($filepath, "Cache file '$file' does not exist after second instantiation.");
            // file does not have correct timestamp
            $this->assertEquals(
                $timestamp[$file],
                filemtime($filepath),
                "File '$file' is re-created on second instantiation."
            );
        }
    }
}
