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
 

class SugarTestUserUtilities
{
    private static $_createdUsers          = array();
    private static $_createdUserSignatures = array();

    private function __construct() {}
    
    public function __destruct()
    {
        self::removeAllCreatedAnonymousUsers();
        self::removeAllCreatedUserSignatures();
    }

    public static function createAnonymousUser($save = true, $is_admin=0, $fields=array())
    {
        if (isset($_REQUEST['action'])) { 
        unset($_REQUEST['action']);
        }
        
        $time = mt_rand();
    	$userId = 'SugarUser';
    	$user = BeanFactory::newBean("Users");
        $user->user_name = $userId . $time;
        $user->user_hash = md5($userId.$time);
        $user->first_name = $userId;
        $user->last_name = $time;
        $user->status='Active';
        $user->is_group = 0;
        $user->portal_only = 0;

        if ($is_admin) {
            $user->is_admin = 1;
        }

        if (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $user->$field = $value;
            }
        }

        $user->default_team = '1'; //Set Default Team to Global
        $user->team_id = '1';
        if ( $save ) {
            $user->save();
        }

        $user->fill_in_additional_detail_fields();
        self::$_createdUsers[] = $user;
        return $user;
    }
    
    public static function removeAllCreatedAnonymousUsers()
    {
        $user_ids = self::getCreatedUserIds();
        if ( count($user_ids) > 0 ) {
            $in = "'" . implode("', '", $user_ids) . "'";
            $GLOBALS['db']->query("DELETE FROM users WHERE id IN ({$in})");
            $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id IN ({$in})");
            $GLOBALS['db']->query("DELETE FROM teams WHERE associated_user_id IN ({$in})");
            $GLOBALS['db']->query("DELETE FROM team_memberships WHERE user_id IN ({$in})");
            // delete any created email address rows
            $GLOBALS['db']->query("DELETE FROM email_addresses WHERE id IN (SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module ='Users' AND bean_id IN ({$in}))");
            $GLOBALS['db']->query("DELETE FROM emails_beans WHERE bean_module='Users' AND bean_id IN ({$in})");
            $GLOBALS['db']->query("DELETE FROM email_addr_bean_rel WHERE bean_module='Users' AND bean_id IN ({$in})");
        }
        self::$_createdUsers = array();
    }
    
    public static function getCreatedUserIds() 
    {
        $user_ids = array();
        foreach (self::$_createdUsers as $user) {
            if ( is_object($user) && $user instanceOf User && $user->id != false) {
                $user_ids[] = $user->id;
            }
        }
        
        return $user_ids;
    }

    public static function createUserSignature($id = "")
    {
        $time = mt_rand();
        $name = "SugarUserSignature";

        $userSignature                 = new UserSignature();
        $userSignature->name           = "{$name}{$time}";
        $userSignature->signature_html = "<b>{$name}{$time} -- html signature</b>";
        $userSignature->signature      = "<b>{$name}{$time} -- text signature</b>";

        if (!empty($id)) {
            $userSignature->new_with_id = true;
            $userSignature->id = $id;
        }

        $userSignature->save();
        $GLOBALS['db']->commit();
        self::$_createdUserSignatures[] = $userSignature;

        return $userSignature;
    }

    public static function getCreatedUserSignatureIds()
    {
        $signatureIds = array();

        foreach (self::$_createdUserSignatures as $signature) {
            if (is_object($signature) && $signature instanceOf UserSignature) {
                $signatureIds[] = $signature->id;
            }
        }

        return $signatureIds;
    }

    public static function removeAllCreatedUserSignatures()
    {
        $ids = self::getCreatedUserSignatureIds();

        if (count($ids) > 0) {
            $GLOBALS["db"]->query("DELETE FROM users_signatures WHERE id IN ('" . implode("','", $ids) . "')");
        }

        self::$_createdUserSignatures = array();
    }
}
