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


class SugarTestAccountUtilities
{
    private static $_createdAccounts = array();

    private function __construct() {}

    /**
     * @return Account
     */
    public static function createAccount($id = '', $accountValues = array())
    {
        $time = mt_rand();
        $account = BeanFactory::newBean('Accounts');

        $accountValues = array_merge(array(
            'name' => 'SugarAccount' . $time,
            'email' => 'account@'. $time. 'sugar.com',
        ), $accountValues);

        // for backward compatibility with existing tests
        $accountValues['email1'] = $accountValues['email'];
        unset($accountValues['email']);

        foreach ($accountValues as $property => $value) {
            $account->$property = $value;
        }

        if(!empty($id))
        {
            $account->new_with_id = true;
            $account->id = $id;
        }
        $account->save();
        $GLOBALS['db']->commit();
        self::$_createdAccounts[] = $account;
        return $account;
    }

    public static function setCreatedAccount($account_ids) {
    	foreach($account_ids as $account_id) {
    		$account = BeanFactory::newBean('Accounts');
    		$account->id = $account_id;
        	self::$_createdAccounts[] = $account;
    	} // foreach
    } // fn

    public static function removeAllCreatedAccounts()
    {
        $account_ids = self::getCreatedAccountIds();
        $GLOBALS['db']->query('DELETE FROM accounts WHERE id IN (\'' . implode("', '", $account_ids) . '\')');
    }

    public static function getCreatedAccountIds()
    {
        $account_ids = array();
        foreach (self::$_createdAccounts as $account) {
            $account_ids[] = $account->id;
        }
        return $account_ids;
    }

    public static function deleteM2MRelationships($linkName)
    {
        $account_ids = self::getCreatedAccountIds();
        $GLOBALS['db']->query('DELETE FROM accounts_' . $linkName . ' WHERE account_id IN (\'' . implode("', '", $account_ids) . '\')');
    }
}
