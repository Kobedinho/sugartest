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


class SugarTestViewConvertLeadUtilities
{
    private static $_createdViewConvertLeads = array();

    private function __construct() {}

    public static function createViewConvertLead($id = '')
    {
        $view_conv_lead = new ViewConvertLead();
        require('modules/Leads/metadata/convertdefs.php');
        $view_conv_lead->defs = $viewdefs;
        self::$_createdViewConvertLeads[] = $view_conv_lead;
        return $view_conv_lead;
    }
}
?>