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


class SugarTestCaseUtilities
{
    private static $_createdCases = array();

    private function __construct()
    {}

    public static function createCase($id = '', $caseValues = array())
    {
        $time = mt_rand();
        $case = new aCase();

        if (!isset($caseValues['name'])) {
            $case->name = 'SugarCase' . $time;
        }

        foreach ($caseValues as $property => $value) {
            $case->$property = $value;
        }

        if (!empty($id)) {
            $case->new_with_id = true;
            $case->id = $id;
        }
        $case->save();
        $GLOBALS['db']->commit();
        self::$_createdCases[] = $case;
        return $case;
    }

    public static function setCreatedCase($case_ids)
    {
        foreach ($case_ids as $case_id) {
            $case = new aCase();
            $case->id = $case_id;
            self::$_createdCases[] = $case;
        } // foreach
    } // fn
    public static function removeAllCreatedCases()
    {
        $case_ids = self::getCreatedCaseIds();
        $GLOBALS['db']->query('DELETE FROM cases WHERE id IN (\'' . implode("', '", $case_ids) . '\')');
    }

    public static function getCreatedCaseIds()
    {
        $case_ids = array();
        foreach (self::$_createdCases as $case) {
            $case_ids[] = $case->id;
        }
        return $case_ids;
    }
}
