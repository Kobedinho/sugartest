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

$viewdefs['Cases']['editview']  = array(
    'templateMeta' => array('maxColumns' => '2',
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'),
                                            array('label' => '10', 'field' => '30')
                                            ),
                            'formId' => 'CaseEditView',
                            'formName' => 'CaseEditView',
                            'hiddenInputs' => array('module' => 'Cases',
                                                    'returnmodule' => 'Cases',
                                                    'returnaction' => 'DetailView',
                                                    'contact_id' => '{$fields.contact_id.value}',
                                                    'bug_id' => '{$fields.bug_id.value}',
                                                    'email_id' => '{$fields.email_id.value}',
                                                    'action' => 'Save',
                                                    'type' => '{$fields.type.value}',
                                                    'status' => 'New',
                                                   ),
							'hiddenFields' => array(
							    array (
	                               'name'=>'portal_viewable',
	                               'operator'=>'=',
	                               'value'=>'1',
							    ),
						    ),
                           ),
    'data' => array(
        array(array('field' => 'case_number', 'readOnly' => true)),
        array('priority', 'status', 'id'),
        array(array('field' => 'name', 'displayParams' => array('size' => 60), 'required'=>true)),
        array(array('field' => 'description', 'displayParams' => array('rows' => '15', 'cols' => '100'))),
    )
);
?>
