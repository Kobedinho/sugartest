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
$viewdefs['Calls']['mobile']['view']['edit'] = array(
	'templateMeta' => array(
        'maxColumns' => '1', 
        'widths' => array(
            array('label' => '10', 'field' => '30'), 
        ),                                  
    ),
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' => array(
                array(
                    'name'=>'name',
                    'displayParams'=>array(
                        'required'=>true,
                        'wireless_edit_only'=>true,
                    ),
                ),
                'date_start',
                'direction',
                'status',
                array(
                    'name' => 'duration',
                    'type' => 'fieldset',
                    'related_fields' => array('duration_hours', 'duration_minutes'),
                    'label' => "LBL_DURATION",
                    'fields' => array(
                        array(
                            'name' => 'duration_hours',
                        ),
                        array(
                            'name' => 'duration_minutes',
                            'type' => 'enum',
                            'options' => 'duration_intervals'
                        ),
                    ),
                ),
                'description',
                'assigned_user_name',
                'team_name',
            ),
        ),
	),
);