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
 

class SugarTestRevenueLineItemUtilities
{
    protected static $_createdRlis = array();

    private function __construct() {}

    public static function createRevenueLineItem($id = '') 
    {
        $time = mt_rand();
        $name = 'SugarRevenueLineItem';
        
        $rli = new RevenueLineItem();
        $rli->currency_id = '-99';
        $rli->name = $name . $time;
        $rli->tax_class = 'Taxable';
        $rli->cost_price = '100.00';
        $rli->list_price = '100.00';
        $rli->discount_price = '100.00';
        $rli->quantity = '100';

        $rli->best_case = '100.00';
        $rli->likely_case = '80.00';
        $rli->worst_case = '50.00';

        if(!empty($id))
        {
            $rli->new_with_id = true;
            $rli->id = $id;
        }
        $rli->save();
        self::$_createdRlis[] = $rli;
        return $rli;
    }

    public static function setCreatedRevenueLineItem($rli_ids) {
        foreach($rli_ids as $rli_id) {
            $rli = new RevenueLineItem();
            $rli->id = $rli_id;
            self::$_createdRlis[] = $rli;
        }
    }
    public static function removeAllCreatedRevenueLineItems() 
    {
        $db = DBManagerFactory::getInstance();
        $conditions = implode(',', array_map(array($db, 'quoted'), self::getCreatedRevenueLineItemIds()));
        if (!empty($conditions)) {
            $db->query('DELETE FROM revenue_line_items WHERE id IN (' . $conditions . ')');
            $db->query('DELETE FROM revenue_line_items_audit WHERE parent_id IN (' . $conditions . ')');
            $db->query('DELETE FROM forecast_worksheets WHERE parent_type = ' . $db->quoted('RevenueLineItems') . ' and parent_id IN (' . $conditions . ')');    
        }

        self::$_createdRlis = array();
    }
        
    public static function getCreatedRevenueLineItemIds() 
    {
        $product_ids = array();
        $rli_ids = array();
        foreach (self::$_createdRlis as $rli) {
            $rli_ids[] = $rli->id;
        }
        return $rli_ids;
    }
}
