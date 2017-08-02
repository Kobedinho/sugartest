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
 * SugarTestFilterUtilities
 *
 * utility class for filters
 */
class SugarTestFilterUtilities
{
    private static $_createdFilters = array();

    private function __construct() {}

    /**
     * Creates and returns a new user filter object
     *
     * @param string $assigned_user_id the name of user to own the filter
     * @param string $name the name of the filter
     * @param string $filter_definition the body of the filter (JSON)
     * @param string $id Optional the id for the currency record
     * @return Filter
     */
    public static function createUserFilter($assigned_user_id, $name, $filter_definition, $id = null)
    {
        $filter = new Filters();
        if(!empty($id))
        {
            $filter->new_with_id = true;
            $filter->id = $id;
        }
        $filter->assigned_user_id = $assigned_user_id;
        $filter->name = $name;
        $filter->filter_definition = $filter_definition;
        $filter->save();
        $GLOBALS['db']->commit();
        self::$_createdFilters[] = $filter;
        return $filter;
    }

    /**
     * remove all created filters from this utility
     */
    public static function removeAllCreatedFilters()
    {
        $filter_ids = self::getCreatedFilterIds();
        $GLOBALS['db']->query('DELETE FROM filters WHERE id IN (\'' . implode("', '", $filter_ids) . '\')');
    }

    /**
     * get list of created filters by id
     *
     * @return array filter ids
     */
    public static function getCreatedFilterIds()
    {
        $filter_ids = array();
        foreach (self::$_createdFilters as $filter) {
            $filter_ids[] = $filter->id;
        }
        return $filter_ids;
    }
}
