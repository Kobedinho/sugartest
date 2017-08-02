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

$module_name = 'f0001_bango';
$_object_name = 'f0001_bango';
$viewdefs[$module_name]['EditView'] = array(
	'templateMeta' => array(
        'maxColumns' => '1',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),
	'panels' => array (
    	array (
			array('name' => $_object_name . '_number','displayParams'=>array('required'=>false,'wireless_detail_only'=>true,)),
		),
		array (
			'priority'
		),
		array (
			'status',
		),
		array (
			array (
				'name' => 'name',
				'label' => 'LBL_SUBJECT',
			)
		),
		array (
			'assigned_user_name',
		),
		array (
			'team_name',
		),
	)
);
?>