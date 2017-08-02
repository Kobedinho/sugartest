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

class RestMetadataModuleViewLayoutTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();

        $this->oldFiles = array();

        $this->_restLogin('','','mobile');
        $this->mobileAuthToken = $this->authToken;
        $this->_restLogin('','','base');
        $this->baseAuthToken = $this->authToken;

    }

    /**
     * @group rest
     */
    public function testMetadataSugarFields() {
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata?type_filter=modules');

        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['views']),'No views for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataModuleLayout() {
        $filesToCheck = array('modules/Cases/clients/mobile/layouts/edit/edit.php',
                              'custom/modules/Cases/clients/mobile/layouts/edit/edit.php',
        );
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = array('modules/Cases/clients/mobile/layouts/edit',
                            'custom/modules/Cases/clients/mobile/layouts/edit',
        );

        foreach ($dirsToMake as $dir ) {
            SugarAutoLoader::ensureDir($dir);
        }

        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put($filesToCheck[0],'<'."?php\n\$viewdefs['Cases']['mobile']['layout']['edit'] = array('unit_test'=>'Standard Dir');\n", true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir',$restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'],"Didn't get the mobile layout");

        // Make sure we get the custom file
        SugarAutoLoader::put($filesToCheck[1],'<'."?php\n\$viewdefs['Cases']['mobile']['layout']['edit'] = array('unit_test'=>'Custom Dir');\n", true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Custom Dir',$restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'],"Didn't get the custom mobile layout");

        // Make sure it flops back to the standard file
        SugarAutoLoader::unlink($filesToCheck[1], true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir',$restReply['reply']['modules']['Cases']['layouts']['edit']['meta']['unit_test'],"Didn't get the mobile layout");
    }

    /**
     * @group rest
     */
    public function testMetadataSubPanels()
    {
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata?type_filter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['subpanels']),'No subpanels for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataFTS()
    {
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata?typeFilter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['ftsEnabled']),'No ftsEnabled for the cases module');
    }

    /**
     * @group rest
     */
    public function testMetadataFavorites()
    {
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata?typeFilter=modules');
        $this->assertTrue(isset($restReply['reply']['modules']['Cases']['favoritesEnabled']),'No favoritesEnabled for the cases module');
    }

    /**
    * @group rest
    */
    public function testMetadataModuleViews() {
        $filesToCheck = array('modules/Cases/clients/mobile/views/edit/edit.php',
                              'custom/modules/Cases/clients/mobile/views/edit/edit.php',
        );
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = array('modules/Cases/clients/mobile/views/edit',
                            'custom/modules/Cases/clients/mobile/views/edit',
        );

        foreach ($dirsToMake as $dir ) {
            SugarAutoLoader::ensureDir($dir);
        }

        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put($filesToCheck[0],'<'."?php\n\$viewdefs['Cases']['mobile']['view']['edit'] = array('unit_test'=>'Standard Dir');\n", true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir',$restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'],"Didn't get the mobile view");

        // Make sure we get the custom file
        SugarAutoLoader::put($filesToCheck[1],'<'."?php\n\$viewdefs['Cases']['mobile']['view']['edit'] = array('unit_test'=>'Custom Dir');\n", true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Custom Dir',$restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'],"Didn't get the custom mobile view");

        // Make sure it flops back to the standard file
        SugarAutoLoader::unlink($filesToCheck[1], true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Standard Dir',$restReply['reply']['modules']['Cases']['views']['edit']['meta']['unit_test'],"Didn't get the mobile view");
    }

    /**
     * Test addresses a case related to the metadata location move that caused
     * metadatamanager to not roll up to sugar objects properly
     *
     * @group rest
     */
    public function testMobileMetaDataRollsUp()
    {
        $this->authToken = $this->mobileAuthToken;
        $reply = $this->_restCall('metadata?typeFilter=modules&moduleFilter=Contacts');
        $this->assertNotEmpty($reply['reply']['modules']['Contacts']['views']['list']['meta'], 'Contacts list view metadata was not fetched from SugarObjects');
    }
}
