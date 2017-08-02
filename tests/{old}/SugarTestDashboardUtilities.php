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


class SugarTestDashboardUtilities
{
    private static $_createdDashboards = array();

    private function __construct() {}

    public static function createDashboard($id = '', $dashboardValues = array())
    {
        $time = mt_rand();
        $dashboard = BeanFactory::newBean('Dashboards');

        if (isset($dashboardValues['name'])) {
            $dashboard->name = $dashboardValues['name'];
        } else {
            $dashboard->name = 'SugarDashboard' . $time;
        }

        if (isset($dashboardValues['dashboard_module'])) {
            $dashboard->dashboard_module = $dashboardValues['dashboard_module'];
        } else {
            $dashboard->dashboard_module = 'Home';
        }

        if(!empty($id))
        {
            $dashboard->new_with_id = true;
            $dashboard->id = $id;
        }
        $dashboard->save();
        $GLOBALS['db']->commit();
        self::$_createdDashboards[] = $dashboard;
        return $dashboard;
    }

    public static function removeAllCreatedAccounts()
    {
        $dashboard_ids = self::getCreatedDashboardIds();
        if (count($dashboard_ids)) {
            $GLOBALS['db']->query('DELETE FROM dashboards WHERE id IN (\'' . implode("', '", $dashboard_ids) . '\')');
        }
    }

    public static function getCreatedDashboardIds()
    {
        $dashboard_ids = array();
        foreach (self::$_createdDashboards as $dashboard) {
            $dashboard_ids[] = $dashboard->id;
        }
        return $dashboard_ids;
    }
}