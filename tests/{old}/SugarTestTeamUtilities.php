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
 

class SugarTestTeamUtilities
{
    public static  $_createdTeams = array();

    private function __construct() {}

    public function __destruct()
    {
        self::removeAllCreatedAnonymousTeams();
    }

    public static function createAnonymousTeam($id = '', array $attributes = array())
    {
        $team = BeanFactory::newBean('Teams');
        $team->name = 'Test Team - ' . mt_rand();
        if(!empty($id))
        {
            $team->new_with_id = true;
            $team->id = $id;
        }

        foreach ($attributes as $attribute => $value) {
            $team->{$attribute} = $value;
        }

        $team->save();
        self::$_createdTeams[] = $team;
        return $team;
    }

    public static function removeAllCreatedAnonymousTeams() 
    {
        $team_ids = self::getCreatedTeamIds();
        $GLOBALS['db']->query('DELETE FROM teams WHERE id IN (\'' . implode("', '", $team_ids) . '\')');
    }
    
    public static function getCreatedTeamIds() 
    {
        $team_ids = array();
        foreach (self::$_createdTeams as $team)
            $team_ids[] = $team->id;
        
        return $team_ids;
    }
}

