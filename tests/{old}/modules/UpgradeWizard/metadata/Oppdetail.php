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
$viewdefs['Opportunities']['DetailView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'useTabs' => true,
        'tabDefs' => array(
            'LBL_OPPORTUNITY_INFORMATION' => array(
                'newTab' => true,
                'panelDefault' => 'expanded',
            ),
            'LBL_PANEL_ADVANCED' => array(
                'newTab' => true,
                'panelDefault' => 'expanded',
            ),
            'LBL_PANEL_ASSIGNMENT' => array(
                'newTab' => true,
                'panelDefault' => 'expanded',
            ),
        ),
    ),
    'panels' => array(
        'LBL_OPPORTUNITY_INFORMATION' => array(
            array(
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO',
                ),
                array(
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                ),
            ),
        ),
        'LBL_PANEL_ADVANCED' => array(
            array(
                array(
                    'name' => 'date_entered',
                    'label' => 'LBL_DATE_ENTERED',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                ),
                'description',
            ),
        ),
        'LBL_PANEL_ASSIGNMENT' => array(
            array(
                'id',
                'opportunity_type',
            ),
        ),
        'LBL_PANEL_HIDDEN' => array(
            array(
                'mycustom_c',
                'myother_custom_c',
            )
        ),
    ),
);
