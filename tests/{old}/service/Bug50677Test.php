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


/**
 * Bug50677Test
 *
 * This test is to make sure that you can add a relationship between Product Bundles and Products via the standard
 * set_relationship method and include in the extra field.
 *
 * @author Jon Whitcraft
 *
 */

require_once 'tests/{old}/service/SOAPTestCase.php';

class Bug50677Test extends SOAPTestCase
{
    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var ProductBundle
     */
    protected $_product_bundle;

    /**
     * setUp
     * Override the setup from SOAPTestCase to also create the seed search data for Accounts and Contacts.
     */
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v3_1/soap.php';
   		parent::setUp();
        $this->_login(); // Logging in just before the SOAP call as this will also commit any pending DB changes

        $this->_product = SugarTestProductUtilities::createProduct();
        $this->_product_bundle = SugarTestProductBundleUtilities::createProductBundle();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM product_bundle_product WHERE bundle_id = '{$this->_product_bundle->id}'");

        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        parent::tearDown();
    }

    public function testSetRelationshipProductBundleProduct()
    {
        $result = $this->_soapClient->call('set_relationship', array(
            'session' => $this->_sessionId,
            'module_name' => 'ProductBundles',
            'module_id' => $this->_product_bundle->id,
            'link_field_name' => 'products',
            'related_ids' => $this->_product->id,
            'name_value_list' => array(
                array('name' => 'product_index', 'value' => 1)
                ),
            'deleted' => 0
            )
        );
        $this->assertEquals(1, $result['created'], "Failed To Create Product Bundle -> Product Relationship");

        // lets make sure the row is correct since it was created
        // it should have a product_index of 1.
        $db = $GLOBALS['db'];
        $sql = "SELECT id, product_index FROM product_bundle_product WHERE bundle_id = '" . $db->quote($this->_product_bundle->id) . "'
                AND product_id = '" . $db->quote($this->_product->id) . "'";
        $result = $db->query($sql);
        $row = $db->fetchByAssoc($result);

        $this->assertTrue(is_guid($row['id']));
        $this->assertEquals(1, $row['product_index']);

    }
}
