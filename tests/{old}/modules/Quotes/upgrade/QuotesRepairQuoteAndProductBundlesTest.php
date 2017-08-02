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
 * Test upgrade script which recalculates all ProductBundle and Quote values based on the Products
 * @see Bug66795
 */
require_once "tests/{old}/upgrade/UpgradeTestCase.php";

class QuotesRepairQuoteAndProductBundlesTest extends UpgradeTestCase
{
    private $taxRate;
    protected $db;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->db = DBManagerFactory::getInstance();

    }

    public function tearDown()
    {
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        $this->db->query("DELETE FROM taxrates WHERE id = '{$this->taxRate->id}'");
        parent::tearDown();
    }

    /**
     * Test if upgrade script correctly calculates values
     *
     * @param $productData
     * @param $shippingValue
     * @param $expected
     * @dataProvider dataProvider
     */
    public function testFixQuotesAndProductBundles($productData, $shippingValue, $expected)
    {
        $this->taxRate = BeanFactory::newBean('TaxRates');
        $this->taxRate->name = 'Bug66795Test TaxRate';
        $this->taxRate->value = 10;
        $this->taxRate->save();

        $product = SugarTestProductUtilities::createProduct();
        foreach ($productData as $id => $value) {
            $product->$id = $value;
        }
        $product->save();

        $productBundle = SugarTestProductBundleUtilities::createProductBundle();
        $productBundle->shipping = $shippingValue;
        $productBundle->save();

        $quote = SugarTestQuoteUtilities::createQuote();
        $quote->taxrate_id = $this->taxRate->id;
        $quote->save();

        $productBundle->set_productbundle_quote_relationship($quote->id, $productBundle->id);
        $productBundle->set_productbundle_product_relationship($product->id, 1, $productBundle->id);

        $this->upgrader->setVersions('6.7.4', 'ent', '7.2.0', 'ent');
        $this->upgrader->setDb($this->db);
        $script = $this->upgrader->getScript('post', '2_RepairQuoteAndProductBundles');
        $script->fixQuoteAndProductBundleValues();

        $productBundle->retrieve($productBundle->id);
        $quote->retrieve($quote->id);

        foreach ($expected as $id => $value) {
            $this->assertEquals(
                $productBundle->$id,
                $value,
                "ProductBundle $id value is not correct, we expect $value"
            );
            $this->assertEquals(
                $quote->$id,
                $value,
                "Quote $id value is not correct, we expect $value"
            );
        }
    }

    public static function dataProvider()
    {
        return array(
            array(
                array(
                    'tax_class' => 'Taxable',
                    'cost_price' => '1000',
                    'list_price' => '100',
                    'discount_price' => '10',
                    'discount_select' => 1,
                    'discount_amount' => 5,
                    'quantity' => 2
                ),
                2,
                array(
                    'tax' => 1.9,
                    'subtotal' => 20,
                    'deal_tot' => 1,
                    'new_sub' => 19,
                    'shipping' => 2,
                    'total' => 22.9,
                    'tax_usdollar' => 1.9,
                    'subtotal_usdollar' => 20,
                    'deal_tot_usdollar' => 1,
                    'new_sub_usdollar' => 19,
                    'shipping_usdollar' => 2,
                    'total_usdollar' => 22.9,
                )
            ),
        );
    }
}
