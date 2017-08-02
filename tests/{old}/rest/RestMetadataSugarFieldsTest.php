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

class RestMetadataSugarFieldsTest extends RestTestBase {
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
        $restReply = $this->_restCall('metadata?type_filter=fields');
        $this->assertTrue(isset($restReply['reply']['fields']['_hash']),'SugarField hash is missing.');
    }

    /**
     * @group rest
     */
    public function testMetadataSugarFieldsTemplates() {
        $filesToCheck = array(
            'clients/mobile/fields/address/editView.hbs',
            'clients/mobile/fields/address/detailView.hbs',
            'clients/base/fields/address/editView.hbs',
            'clients/base/fields/address/detailView.hbs',
            'custom/clients/mobile/fields/address/editView.hbs',
            'custom/clients/mobile/fields/address/detailView.hbs',
            'custom/clients/base/fields/address/editView.hbs',
            'custom/clients/base/fields/address/detailView.hbs',
        );
        SugarTestHelper::saveFile($filesToCheck);

        $dirsToMake = array(
            'clients/mobile/fields/address',
            'clients/base/fields/address',
            'custom/clients/mobile/fields/address',
            'custom/clients/base/fields/address',
        );

        foreach ($dirsToMake as $dir ) {
            SugarAutoLoader::ensureDir($dir);
        }

        /**
         * Note that we used to return only one widget per widget name. For example, if we had a base/date
         * and a portal/date, and the current client id was portal, we'd just get the portal/date. However,
         * we have now moved to returning both of these from within the widget type (e.g. reply.<type>.<platform>.<widget>)
         */
        SugarAutoLoader::put('clients/base/fields/address/editView.hbs','BASE EDITVIEW', true);
        // Make sure we get it when we ask for mobile
        SugarAutoLoader::put('clients/mobile/fields/address/editView.hbs','MOBILE EDITVIEW', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get mobile code when that was the direct option");

        // Make sure we get it when we ask for mobile, even though there is base code there
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get mobile code when base code was there.");

        // Make sure we get the base code when we ask for it.
        $this->_clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't get base code when it was the direct option");

        // Delete the mobile address and make sure it falls back to base
        SugarAutoLoader::unlink('clients/mobile/fields/address/editView.hbs', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't fall back to base code when mobile code wasn't there.");


        // Make sure the mobile code is loaded before the non-custom base code
        SugarAutoLoader::put('custom/clients/mobile/fields/address/editView.hbs','CUSTOM MOBILE EDITVIEW', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->mobileAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('CUSTOM MOBILE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't use the custom mobile code.");

        // Make sure custom base code works
        SugarAutoLoader::put('custom/clients/base/fields/address/editView.hbs','CUSTOM BASE EDITVIEW', true);
        $this->_clearMetadataCache();
        $this->authToken = $this->baseAuthToken;
        $restReply = $this->_restCall('metadata/?type_filter=fields');
        $this->assertEquals('CUSTOM BASE EDITVIEW',$restReply['reply']['fields']['address']['templates']['editView'],"Didn't use the custom base code.");
    }


}
