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

$viewdefs['Cases']['detailview'] = array(
    'templateMeta' => array('maxColumns' => '2',
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'),
                                            array('label' => '10', 'field' => '30')
                                            ),
                            'extraFields' => array('date_modified'),
                           ),
    'data' => array(
        array('case_number', 'assigned_user_name'),
        array('created_by_name', 'date_entered'),
        array('modified_by_name', 'date_modified'),
        array('priority', 'status'),
        array('name'),
        array(array('field' => 'description', 'nl2br' => true)),
        array('resolution'),
    )
);
?>
