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

require_once('tests/{old}/service/SOAPTestCase.php');

require_once('tests/{old}/SugarTestProductBundleUtilities.php');
require_once('tests/{old}/SugarTestQuoteUtilities.php');
require_once('tests/{old}/SugarTestProductUtilities.php');

/**
 * Bug #32064
 * Setting a relationship between ProductBundles and Quotes or Products and ProductBundles results in a PHP fatal error
 *
 * @ticket 32064
 */
class Bug32064Test extends SOAPTestCase
{
    protected $prodBundle = null;
    protected $quote = null;
    protected $product = null;

    public function setUp()
    {
        parent::setUp();

        $this->_setupTestUser();
        // _login uses a static User instance and _setupTestUser doesn't setup it.
        SOAPTestCase::$_user = $this->_user;

        $this->prodBundle = SugarTestProductBundleUtilities::createProductBundle();
        $this->quote = SugarTestQuoteUtilities::createQuote();
        $this->product = SugarTestProductUtilities::createProduct();

        // Commit setUp records for DB2.
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        $this->_tearDownTestUser();

        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestProductUtilities::removeAllCreatedProducts();

        parent::tearDown();
    }

    /**
     * Setting a relationship between ProductBundles and Quotes or
     * Products and ProductBundles results in a PHP fatal error
     *
     * @group 32064
     */
    public function testProductBundlesRelationsWithProductsAndQuotesSoapV4()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        $this->_soapClient = new nusoapclient($this->_soapURL, false, false, false, false, false, 600, 600);
        $this->_login();

        $this->_soapClient->call(
            'set_relationship',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'link_field_name' => 'products',
                'related_ids' => array($this->product->id),
                'name_value_list' => array(),
                'deleted' => 0
            )
        );

        $this->_soapClient->call(
            'set_relationship',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'link_field_name' => 'quotes',
                'related_ids' => array($this->quote->id),
                'name_value_list' => array(),
                'deleted' => 0
            )
        );

        $assertProductsRel = $this->_soapClient->call(
            'get_relationships',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'link_field_name' => 'products',
                'related_module_query' => '',
                'related_fields' => array('id'),
                'related_module_link_name_to_fields_array' => array(),
                'deleted' => 0,
            )
        );

        $assertQuoteRel = $this->_soapClient->call(
            'get_relationships',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'link_field_name' => 'quotes',
                'related_module_query' => '',
                'related_fields' => array('id'),
                'related_module_link_name_to_fields_array' => array(),
                'deleted' => 0,
            )
        );

        $this->assertEquals($this->product->id, $assertProductsRel['entry_list'][0]['id']);
        $this->assertEquals($this->quote->id, $assertQuoteRel['entry_list'][0]['id']);
    }

    /**
     * @group 32064
     */
    public function testProductBundlesRelationsWithProductsAndQuotesSoapVer1()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        $this->_soapClient = new nusoapclient($this->_soapURL, false, false, false, false, false, 600, 600);
        $this->_login();

        $this->_soapClient->call(
            'set_relationship',
            array(
                'session' => $this->_sessionId,
                'set_relationship_value' => array(
                    'module1' => 'ProductBundles',
                    'module1_id' => $this->prodBundle->id,
                    'module2' => 'Products',
                    'module2_id' => $this->product->id,
                )
            )
        );

        $this->_soapClient->call(
            'set_relationship',
            array(
                'session' => $this->_sessionId,
                'set_relationship_value' => array(
                    'module1' => 'ProductBundles',
                    'module1_id' => $this->prodBundle->id,
                    'module2' => 'Quotes',
                    'module2_id' => $this->quote->id,
                )
            )
        );

        $assertProductsRel = $this->_soapClient->call(
            'get_relationships',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'related_module' => 'Products',
                'deleted' => 0,
            )
        );

        $assertQuoteRel = $this->_soapClient->call(
            'get_relationships',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'related_module' => 'Quotes',
                'deleted' => 0,
            )
        );

        $this->assertEquals($this->product->id, $assertProductsRel['ids'][0]['id']);
        $this->assertEquals($this->quote->id, $assertQuoteRel['ids'][0]['id']);
    }
}
