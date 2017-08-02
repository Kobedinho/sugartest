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
 * Bug #58890
 * ListView Does Not Retain Sort Order
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58890
 */
class Bug58890Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts order by value
     *
     * @group 58890
     * @return void
     */
    public function testOrderBy()
    {
        $bean = new SugarBean58890();
        $listViewData = new ListViewData();
        $listViewData->listviewName = $bean->module_name;

        $listViewData->getListViewData($bean, '', -1, -1, array('name' => array()));
        $this->assertEquals('date_entered DESC', $bean->orderByString58890, 'Order by date_entered DESC should be used');

        $GLOBALS['current_user']->setPreference('listviewOrder', array(
            'orderBy' => 'name',
            'sortOrder' => 'ASC'
        ), 0, $listViewData->var_name);

        $listViewData->getListViewData($bean, '', -1, -1, array('name' => array()));
        $this->assertEquals('name ASC', $bean->orderByString58890, 'User\'s preference should be used');
    }
}

class SugarBean58890 extends Account
{
    /**
     * @var string
     */
    public $orderByString58890 = '';

    public function create_new_list_query($order_by, $where, $filter = array(), $params = array(), $show_deleted = 0, $join_type = '', $return_array = false, $parentbean = null, $singleSelect = false, $ifListForExport = false)
    {
        $this->orderByString58890 = $order_by;
        return parent::create_new_list_query($order_by, $where, $filter, $params, $show_deleted, $join_type, $return_array, $parentbean, $singleSelect, $ifListForExport);
    }
}
