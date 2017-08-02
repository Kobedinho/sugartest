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

class SugarTestACLUtilities
{
    public static $_createdRoles = array();
    public static $_modules = array();

    private function __construct() {}

    /**
     * Create a Role for use in a Unit Test
     * @param  string    $name           - name of the role
     * @param  array     $allowedModules - modules you want to give access to
     * @param  array     $allowedActions - actions user is allowed to have
     * @param  array     $ownerActions   - any owner actions [Edit Owner, etc] the user needs
     * @return SugarBean role
     */
    public static function createRole($name, $allowedModules, $allowedActions, $ownerActions = array(), $type = 'module')
    {
        self::$_modules = array_merge($allowedModules, self::$_modules);

        $role = new ACLRole();
        $role->name = $name;
        $role->description = $name;
        $role->save();
        $db = DBManagerFactory::getInstance();
        $db->commit();

        $roleActions = $role->getRoleActions($role->id);
        foreach ($roleActions as $moduleName => $actions) {
            // enable allowed modules
            if (isset($actions[$type]['access']['id']) && !in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions[$type]['access']['id'], ACL_ALLOW_DISABLED);
            } elseif (isset($actions[$type]['access']['id']) && in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions[$type]['access']['id'], ACL_ALLOW_ENABLED);
            } else {
                foreach ($actions as $action => $actionName) {
                    if (isset($actions[$action]['access']['id'])) {
                        $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                    }
                }
            }

            if (in_array($moduleName, $allowedModules)) {
                foreach ($actions[$type] as $actionName => $action) {
                    if (in_array($actionName, $allowedActions) && in_array($actionName, $ownerActions)) {
                        $aclAllow = ACL_ALLOW_OWNER;
                    } elseif (in_array($actionName, $allowedActions)) {
                        $aclAllow = ACL_ALLOW_ALL;
                    } else {
                        $aclAllow = ACL_ALLOW_NONE;
                    }

                    $role->setAction($role->id, $action['id'], $aclAllow);
                }
            }

        }
        self::$_createdRoles[] = $role;

        return $role;
    }

    /**
     * Create a field
     * @param  string    $role_id      - the role to add this to
     * @param  string    $module       - the module that has the field
     * @param  string    $field_name   - the field name to apply the access to
     * @param  int       $access_level - the access level from ACLField/actiondefs.php
     * @return SugarBean field
     */
    public static function createField($role_id, $module, $field_name, $access_level)
    {
        self::$_modules[] = $module;
        // set the name field as Read Only
        $aclField = new ACLField();
        $aclField->setAccessControl($module, $role_id, $field_name, $access_level);

        return $aclField;
    }

    /**
     * Give the Global current user a role
     * @param  SugarBean $role
     * @return null
     */
    public static function setupUser($role)
    {
        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }

        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        BeanFactory::newBean('ACLFields')->clearACLCache();
        foreach (self::$_modules AS $module) {
            ACLField::loadUserFields($module, $module, $GLOBALS['current_user']->id, true );
        }

    }

    /**
     * TearDown method to remove any roles and fields setup
     * @return null
     */
    public static function tearDown()
    {
        foreach (self::$_createdRoles AS $role) {
            $role->mark_deleted($role->id);
            $role->mark_relationships_deleted($role->id);
            $GLOBALS['db']->query("DELETE FROM acl_fields WHERE role_id = '{$role->id}'");
        }
        self::$_createdRoles = array();
        BeanFactory::newBean('ACLFields')->clearACLCache();
    }
}
