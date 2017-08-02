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
require_once 'include/utils/zip_utils.php';
require_once('tests/{old}/rest/RestTestBase.php');

/**
 * Test some scenarios that were problematic with Shadow
 */
class ShadowTest extends RestTestBase
{
        public function setUp()
        {
                $this->dir = getcwd();
                chdir(sugar_root_dir());
                parent::setUp();
        }

        public function tearDown()
        {
                chdir($this->dir);
                SugarTestContactUtilities::removeAllCreatedContacts();
                parent::tearDown();
        }

        public function testZipDir()
        {
                $arch = "upload://test.zip";
                $dir = "upload://import";
                $testfile = "$dir/shadowtest-file.txt";
                SugarTestHelper::saveFile($testfile);
                SugarTestHelper::saveFile($arch);
                file_put_contents($testfile, "test");
                @unlink($arch);
                zip_dir($dir, $arch);
                $this->assertTrue(file_exists($arch));
        }

        public function testFileMime()
        {
            if(!mime_is_detectable()) {
                $this->markTestSkipped('Requires functions to detect mime type');
            }
            $filename = sugar_cached("test.txt");
            SugarTestHelper::saveFile($filename);
            file_put_contents($filename, "This is a text of a test. And this is a test of a text.");
            $this->assertEquals("text/plain", get_file_mime_type($filename, "wront/type"));
        }

        public function testRestPost()
        {
            $this->markTestIncomplete("ENG- This test is erroring in Stack94");
            $this->_contact = SugarTestContactUtilities::createContact();
            $post = array('picture' => '@include/images/badge_256.png');
            $reply = $this->_restCall('Contacts/' . $this->_contact->id . '/file/picture', $post);
            $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
            $this->assertNotEmpty($reply['reply']['picture']['name'], 'File name not returned');
        }

        public function testRestPostCache()
        {
            $this->markTestIncomplete("ENG- This test is erroring in Stack94");
            $this->_contact = SugarTestContactUtilities::createContact();
            $filename = sugar_cached("test.png");
            SugarTestHelper::saveFile($filename);
            copy('include/images/badge_256.png', $filename);
            $post = array('picture' => '@'.$filename);
            $reply = $this->_restCall('Contacts/' . $this->_contact->id . '/file/picture', $post);
            $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
            $this->assertNotEmpty($reply['reply']['picture']['name'], 'File name not returned');
        }
}
