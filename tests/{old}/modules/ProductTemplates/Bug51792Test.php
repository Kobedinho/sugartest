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

require_once 'modules/ProductTemplates/TreeData.php';

/**
 * Bug #51792
 * Product Category "sort" field not respected when adding product to Quote
 *
 * @ticket 51792
 */
class Bug51792Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_category1;
    private $_category2;
    private $_category3;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        $this->_category1 = SugarTestProductCategoryUtilities::createProductCategory();
        $this->_category1->name = $this->_category1->name.'_1';
        $this->_category1->list_order = 3;
        $this->_category1->save();

        $this->_category2 = SugarTestProductCategoryUtilities::createProductCategory();
        $this->_category2->name = $this->_category2->name.'_2';
        $this->_category2->list_order = 2;
        $this->_category2->save();

        $this->_category3 = SugarTestProductCategoryUtilities::createProductCategory();
        $this->_category3->name = $this->_category3->name.'_3';
        $this->_category3->list_order = 1;
        $this->_category3->save();
    }

    public function tearDown()
    {
        SugarTestProductCategoryUtilities::removeAllCreatedProductCategories();
        SugarTestHelper::tearDown();
    }

    /**
     * @group 51792
     */
    public function testOrder()
    {
        $labels = array($this->_category1->name, $this->_category2->name, $this->_category3->name);
        $indexes = array();

        $catalogtree = new Tree('productcatalog');
        $catalogtree->set_param('module','ProductTemplates');
        $nodes = get_categories_and_products(null);

        $this->assertNotEmpty($nodes, 'Error retrieving data');

        foreach ($nodes as $index => $node) {
            $indexes[$node->_label] = $index;
        }

        foreach ($labels as $label) {
            $this->assertArrayHasKey($label, $indexes, 'Test-created Product Category is not in the result list');
        }

        $this->assertLessThan($indexes[$this->_category2->name], $indexes[$this->_category3->name]);
        $this->assertLessThan($indexes[$this->_category1->name], $indexes[$this->_category2->name]);
    }
}
