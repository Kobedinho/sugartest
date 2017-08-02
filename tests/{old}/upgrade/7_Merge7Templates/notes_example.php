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
$viewdefs['Notes']['base']['view']['record'] = array (
    'panels' =>
        array (
            0 =>
                array (
                    'name' => 'panel_header',
                    'header' => true,
                    'fields' =>
                        array (
                            0 =>
                                array (
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ),
                            1 => 'name',
                            2 =>
                                array (
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ),
                            3 =>
                                array (
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ),
                        ),
                ),
            1 =>
                array (
                    'name' => 'panel_body',
                    'label' => 'LBL_RECORD_BODY',
                    'columns' => 2,
                    'labels' => true,
                    'labelsOnTop' => true,
                    'placeholders' => true,
                    'newTab' => false,
                    'panelDefault' => 'expanded',
                    'fields' =>
                        array (
                            0 => 'contact_name',
                            1 => 'parent_name',
                            2 => 'assigned_user_name',
                            3 =>
                                array (
                                    'name' => 'filename',
                                    'related_fields' =>
                                        array (
                                            0 => 'file_mime_type',
                                        ),
                                ),
                            4 =>
                                array (
                                    'name' => 'description',
                                    'rows' => 5,
                                    'span' => 12,
                                ),
                        ),
                ),
            2 =>
                array (
                    'name' => 'panel_hidden',
                    'label' => 'LBL_RECORD_SHOWMORE',
                    'hide' => true,
                    'columns' => 2,
                    'labelsOnTop' => true,
                    'placeholders' => true,
                    'newTab' => false,
                    'panelDefault' => 'expanded',
                    'fields' =>
                        array (
                            0 =>
                                array (
                                    'name' => 'date_entered',
                                    'comment' => 'Date record created',
                                    'studio' =>
                                        array (
                                            'portaleditview' => false,
                                        ),
                                    'readonly' => true,
                                    'label' => 'LBL_DATE_ENTERED',
                                ),
                            1 =>
                                array (
                                    'name' => 'date_modified',
                                    'comment' => 'Date record last modified',
                                    'studio' =>
                                        array (
                                            'portaleditview' => false,
                                        ),
                                    'readonly' => true,
                                    'label' => 'LBL_DATE_MODIFIED',
                                ),
                            2 =>
                                array (
                                    'name' => 'team_name',
                                    'span' => 6,
                                ),
                            3 =>
                                array (
                                    'span' => 6,
                                ),
                        ),
                ),
        ),
    'templateMeta' =>
        array (
            'useTabs' => false,
        ),
);
