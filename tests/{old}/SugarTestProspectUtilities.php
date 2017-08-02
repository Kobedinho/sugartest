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


class SugarTestProspectUtilities
	{
    private static $_createdProspects = array();

    private function __construct() {}

    public static function createProspect($id = '') 
    {
        $first_name = 'SugarProspectFirst';
    	$last_name = 'SugarProspectLast';
    	$email1 = 'prospect@sugar.com';
		$title = 'Test prospect title';
    	$prospect = new Prospect();
        $prospect->first_name = $first_name;
        $prospect->last_name = $last_name ;
		$prospect->title = $title;
        $prospect->email1 = 'prospect@sugar.com';
		  
        if(!empty($id))
        {
            $prospect->new_with_id = true;
            $prospect->id = $id;
        }
        $prospect->save();
        self::$_createdProspects[] = $prospect;
        return $prospect;
    }

        
    public static function removeAllCreatedProspects() 
    {
        $prospect_ids = self::getCreatedProspectIds();
        $GLOBALS['db']->query('DELETE FROM prospects WHERE id IN (\'' . implode("', '", $prospect_ids) . '\')');
    }
   public static function getCreatedProspectIds() 
    {
        $prospect_ids = array();
        foreach (self::$_createdProspects as $prospect) {
            $prospect_ids[] = $prospect->id;
        }
        return $prospect_ids;
    }
   
}
