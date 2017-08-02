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

class RestPublicMetadataSugarViewsTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
        $this->oldFiles = array();

        // Make sure the public test doesn't used a logged in client
        $this->authToken = 'LOGGING_IN';
    }

    public function tearDown()
    {
        foreach ( $this->oldFiles as $filename => $filecontents ) {
            if ( $filecontents == '_NO_FILE' ) {
                if ( file_exists($filename) ) {
                    SugarAutoLoader::unlink($filename);
                }
            } else {
                SugarAutoLoader::put($filename,$filecontents);
            }
        }
        SugarAutoLoader::saveMap();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testMetadataSugarViews() {
        $restReply = $this->_restCall('metadata/public?type_filter=views');

        $this->assertTrue(isset($restReply['reply']['views']['_hash']),'SugarView hash is missing.');
    }
    /**
     * @group rest
     */
    public function testMetadataSugarViewsTemplates() {
        $filesToCheck = array(
            'clients/mobile/views/address/editView.hbs',
            'clients/mobile/views/address/detailView.hbs',
            'clients/portal/views/address/editView.hbs',
            'clients/portal/views/address/detailView.hbs',
            'clients/base/views/address/editView.hbs',
            'clients/base/views/address/detailView.hbs',
            'custom/clients/mobile/views/address/editView.hbs',
            'custom/clients/mobile/views/address/detailView.hbs',
            'custom/clients/portal/views/address/editView.hbs',
            'custom/clients/portal/views/address/detailView.hbs',
            'custom/clients/base/views/address/editView.hbs',
            'custom/clients/base/views/address/detailView.hbs',
        );

        foreach ( $filesToCheck as $filename ) {
            if ( file_exists($filename) ) {
                $this->oldFiles[$filename] = file_get_contents($filename);
            } else {
                $this->oldFiles[$filename] = '_NO_FILE';
            }
        }

        $dirsToMake = array('clients/mobile/views/address',
                            'clients/portal/views/address',
                            'clients/base/views/address',
                            'custom/clients/mobile/views/address',
                            'custom/clients/portal/views/address',
                            'custom/clients/base/views/address',
                            'clients/mobile/views/address',

        );

        foreach ($dirsToMake as $dir ) {
            SugarAutoLoader::ensureDir($dir);
        }

        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put('clients/mobile/views/address/editView.hbs','MOBILE EDITVIEW', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't get mobile code when that was the direct option");


        // Make sure we get it when we ask for mobile, even though there is base code there
        SugarAutoLoader::put('clients/base/views/address/editView.hbs','BASE EDITVIEW', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=mobile');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't get mobile code when base code was there.");


        // Make sure we get the base code when we ask for it.
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=base');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't get base code when it was the direct option");
        $this->assertTrue(!isset($restReply['reply']['views']['mobile']), "Only should be base platform");

        // Delete the mobile address and make sure it falls back to base
        SugarAutoLoader::unlink('clients/mobile/views/address/editView.hbs', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=mobile');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't fall back to base code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom base code
        SugarAutoLoader::put('custom/clients/mobile/views/address/editView.hbs','CUSTOM MOBILE EDITVIEW', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=mobile');
        $this->assertEquals('CUSTOM MOBILE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't use the custom mobile code.");

        // Make sure custom base code works
        SugarAutoLoader::put('custom/clients/base/views/address/editView.hbs','CUSTOM BASE EDITVIEW', true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=views&platform=base');
        $this->assertEquals('CUSTOM BASE EDITVIEW',$restReply['reply']['views']['address']['templates']['editView'],"Didn't use the custom base code.");
    }


}
