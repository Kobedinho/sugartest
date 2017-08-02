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

require_once('tests/{old}/rest/RestTestBase.php');

class RestMetadataSugarLayoutsTest extends RestTestBase {
    protected $_testPaths = array(
        'wiggle' => 'clients/base/layouts/wiggle/wiggle.php',
        'woggle' => 'custom/clients/base/layouts/woggle/woggle.php',
        'pizzle' => 'clients/mobile/layouts/dizzle/dazzle.php', // Tests improperly named metadata files
        'pozzle' => 'custom/clients/mobile/layouts/pozzle/pozzle.php',
    );

    protected $_testFilesCreated = array();

    protected $_oldFileContents = array();

    public function setUp()
    {
        parent::setUp();

        foreach ($this->_testPaths as $file) {
            preg_match('#clients/(.*)/layouts/#', $file, $m);
            $platform = $m[1];
            $filename = basename($file, '.php');
            $contents = "<?php\n\$viewdefs['$platform']['layout']['$filename'] = array('test' => 'foo');\n";
            if (file_exists($file)) {
                $this->_oldFileContents[$file] = file_get_contents($file);
            } else {
                $this->_testFilesCreated[] = $file;
                SugarAutoLoader::ensureDir(dirname($file));
            }

            SugarAutoLoader::put($file, $contents);
        }
        SugarAutoLoader::saveMap();

        $this->_restLogin('','','mobile');
        $this->mobileAuthToken = $this->authToken;
        $this->_restLogin('','','base');
        $this->baseAuthToken = $this->authToken;
        $this->_clearMetadataCache();
    }

    public function tearDown()
    {
        foreach ($this->_oldFileContents as $file => $contents) {
            SugarAutoLoader::put($file, $contents);
        }

        foreach ($this->_testFilesCreated as $file) {
            SugarAutoLoader::unlink($file);
        }
        SugarAutoLoader::saveMap();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testBaseLayoutRequestAll() {
        $this->_clearMetadataCache();
        $reply = $this->_restCall('metadata');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('wiggle', $reply['reply']['layouts'], 'Test result not found');
    }
    /**
     * @group rest
     */
    public function testBaseLayoutRequestLayoutsOnly() {
        $this->_clearMetadataCache();
        $reply = $this->_restCall('metadata?type_filter=layouts');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('woggle', $reply['reply']['layouts'], 'Test result not found');
    }
    /**
     * @group rest
     */
    public function testMobileLayoutRequestAll() {
        $this->authToken = $this->mobileAuthToken;
        $this->_clearMetadataCache();
        $reply = $this->_restCall('metadata');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('pozzle', $reply['reply']['layouts'], 'Test result not found');
    }
    /**
     * @group rest
     */
    public function testMobileLayoutRequestLayoutsOnly() {
        $this->authToken = $this->mobileAuthToken;
        $this->_clearMetadataCache();
        $reply = $this->_restCall('metadata?type_filter=layouts');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertTrue(!isset($reply['reply']['layouts']['dizzle']), 'Incorrectly picked up metadata that should not have been read');
        $this->assertArrayHasKey('wiggle', $reply['reply']['layouts'], 'BASE metadata not picked up');
        $this->assertNotEmpty($reply['reply']['layouts']['wiggle']['meta']['test'], 'Test result data not returned');
    }
}
