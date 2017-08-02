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

class SugarTestOpportunityUtilities
{

    private static $_createdOpportunities = array();

    private static $_createdAccount = null;

    private function __construct()
    {

    }

    /*
    private function _createAccount($time)
    {
        if (self::$_createdAccount === null)
        {
            $name = 'SugarOpportunityAccount';
            $account = new Account();
            $account->name = $name . $time;
            $account->email1 = 'account@' . $time . 'sugar.com';
            $account->save();

            $GLOBALS['db']->commit();
            self::$_createdAccount = $account;
        }

        return self::$_createdAccount;
    }
    */

    public static function createOpportunity($id = '', Account $account = null)
    {
        $opportunity = self::_createOpportunity($id);

        if ($account !== null) {
            $opportunity->account_id = $account->id;
            $opportunity->account_name = $account->name;
            $opportunity->save();
        }

        return $opportunity;
    }

    private static function _createOpportunity($id)
    {
        $timedate = TimeDate::getInstance();
        $db = DBManagerFactory::getInstance();
        $name = 'SugarOpportunity';

        $opportunity = new Opportunity();

        global $app_list_strings;

        if (!empty($id)) {
            $opportunity->new_with_id = true;
            $opportunity->id = $id;
        }

        $opportunity->name = $name . time();
        $opportunity->amount = 10000;
        $opportunity->date_closed = $timedate->getNow()->asDbDate();
        $opportunity->save();

        $db->commit();

        self::$_createdOpportunities[] = $opportunity;
        $opportunity->load_relationship('revenuelineitems');
        return $opportunity;
    }


    public static function setCreatedOpportunity($opportunity_ids)
    {
        foreach ($opportunity_ids as $opportunity_id) {
            $opportunity = new Opportunity();
            $opportunity->id = $opportunity_id;
            self::$_createdOpportunities[] = $opportunity;
        }
    }

    public static function removeAllCreatedOpportunities()
    {
        $opp_ids = self::getCreatedOpportunityIds();
        $db = DBManagerFactory::getInstance();
        
        if (!empty($opp_ids)) {            
            $db->query("DELETE FROM products_audit WHERE parent_id IN (SELECT id FROM products WHERE opportunity_id IN ('" . implode("', '", $opp_ids) . "'))");
            $db->query("DELETE FROM products WHERE opportunity_id IN ('" . implode("', '", $opp_ids) . "')");
            $db->query("DELETE FROM opportunities WHERE id IN ('" . implode("', '", $opp_ids) . "')");
            $db->query("DELETE FROM opportunities_audit WHERE parent_id IN ('" . implode("', '", $opp_ids) . "')");
            $db->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN ('" . implode("', '", $opp_ids) . "')");
            $db->query("DELETE FROM forecast_worksheets WHERE parent_type = 'Opportunities' and parent_id IN ('" . implode("', '", $opp_ids) . "')");
        }

        if (self::$_createdAccount !== null && self::$_createdAccount->id) {
            $db->query("DELETE FROM accounts WHERE id = '" . self::$_createdAccount->id . "'");
        }
        self::$_createdOpportunities = array();
    }

    public static function getCreatedOpportunityIds()
    {
        $opportunity_ids = array();

        foreach (self::$_createdOpportunities as $opportunity) {
            $opportunity_ids[] = $opportunity->id;
        }

        return $opportunity_ids;
    }
}
