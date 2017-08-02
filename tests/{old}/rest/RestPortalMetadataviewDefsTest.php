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

require_once('tests/{old}/rest/RestTestPortalBase.php');

class RestMetadataViewDefsTest extends RestTestPortalBase {
    public $testMetaDataFiles = array(
        'contacts' => 'custom/modules/Contacts/clients/portal/layouts/banana/banana.php',
        'cases'     => 'modules/Cases/clients/portal/views/ghostrider/ghostrider.php'
    );

    public function tearDown()
    {
        foreach($this->testMetaDataFiles as $file ) {
            if (file_exists($file)) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                @SugarAutoLoader::unlink($file);

                // Remove the stray directory since metadata manager will pick it up
                $dirname = dirname($file);
                rmdir($dirname);
                SugarAutoLoader::delFromMap($dirname);
            }
        }
        SugarAutoLoader::saveMap();

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testDefaultPortalLayoutMetaData() {
        // FIXME TY-1298: investigate why this test fails
        $restReply = $this->_restCall('metadata?type_filter=modules&module_filter=Contacts');
        // Hash should always be set
        $this->assertTrue(isset($restReply['reply']['modules']['Contacts']['layouts']['_hash']), "Portal layouts missing hash empty");
        unset($restReply['reply']['modules']['Contacts']['layouts']['_hash']);

        // Now the layouts should be empty
        $this->assertTrue(empty($restReply['reply']['modules']['Contacts']['layouts']), "Portal layouts are not empty");
    }

    /**
     * @group rest
     */
    public function testDefaultPortalViewMetaData() {
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=modules&module_filter=Cases');
        $this->assertTrue(empty($restReply['reply']['modules']['Cases']['views']['ghostrider']), "Test file found unexpectedly");
    }

    /**
     * @group rest
     */
    public function testAdditionalPortalLayoutMetaData() {
        // FIXME TY-1298: investigate why this test fails
        SugarAutoLoader::ensureDir(dirname($this->testMetaDataFiles['contacts']));
        SugarAutoLoader::put($this->testMetaDataFiles['contacts'], "<?php\n\$viewdefs['Contacts']['portal']['layout']['banana'] = array('yummy' => 'Banana Split');", true);
        SugarAutoLoader::saveMap();

        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=modules&module_filter=Contacts');
        $this->assertEquals('Banana Split',$restReply['reply']['modules']['Contacts']['layouts']['banana']['meta']['yummy'], "Failed to retrieve all layout metadata");
    }

    /**
     * @group rest
     */
    public function testAdditionalPortalViewMetaData() {
        // FIXME TY-1298: investigate why this test fails
        SugarAutoLoader::ensureDir(dirname($this->testMetaDataFiles['cases']));
        SugarAutoLoader::put($this->testMetaDataFiles['cases'], "<?php\n\$viewdefs['Cases']['portal']['view']['ghostrider'] = array('pattern' => 'Full');", true);
        SugarAutoLoader::saveMap();

        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=modules&module_filter=Cases');
        $this->assertEquals('Full',$restReply['reply']['modules']['Cases']['views']['ghostrider']['meta']['pattern'], "Failed to retrieve all view metadata");

    }

    /**
     * @group rest
     */
    public function testMetadataCacheBuild() {
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata/public?type_filter=config&platform=portal');
        $this->assertArrayHasKey('_hash',$restReply['reply'],"Did not have a _hash on the first run");

        $restReply = $this->_restCall('metadata/public?type_filter=config&platform=portal');
        $this->assertArrayHasKey('_hash',$restReply['reply'],"Did not have a _hash on the second run");

    }


}
