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
 * Test upgrade script which fixes duplicate bundle indexes
 * @see Bug65573
 */
require_once "tests/{old}/upgrade/UpgradeTestCase.php";

class QuotesRepairProductBundleIndexesTest extends UpgradeTestCase
{
    /**
     * @var DBManager
     */
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
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        parent::tearDown();
    }

    /**
     * Test if upgrade script correctly reindexes product bundles
     */
    public function testFixQuotesAndProductBundles()
    {
        $productBundleOne = SugarTestProductBundleUtilities::createProductBundle();
        $productBundleTwo = SugarTestProductBundleUtilities::createProductBundle();
        $quote = SugarTestQuoteUtilities::createQuote();

        // Create 2 relationships, but with the same bundle_index
        $productBundleOne->set_productbundle_quote_relationship($quote->id, $productBundleOne->id, 1);
        $productBundleTwo->set_productbundle_quote_relationship($quote->id, $productBundleTwo->id, 1);

        $this->upgrader->setVersions('6.7.4', 'ent', '7.2.0', 'ent');
        $this->upgrader->setDb($this->db);
        $script = $this->upgrader->getScript('post', '2_RepairQuoteAndProductBundles');
        $script->fixProductBundleIndexes();

        $result = $this->db->query(
            "
            SELECT quote_id, count(quote_id) AS cnt
            FROM product_bundle_quote
            GROUP BY quote_id, bundle_index
            HAVING count(quote_id) > 1
            "
        );
        $row = $this->db->fetchByAssoc($result);

        $this->assertEmpty($row, 'There should be no bundles with duplicate indexes');
    }
}
