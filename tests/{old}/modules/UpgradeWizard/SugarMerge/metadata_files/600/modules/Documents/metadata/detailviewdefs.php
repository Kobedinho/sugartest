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
$viewdefs['Documents']['DetailView'] = array(
'templateMeta' => array('maxColumns' => '2',
                        'form' => array('hidden'=>array('<input type="hidden" name="old_id" value="{$fields.document_revision_id.value}">')), 
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'), 
                                        array('label' => '10', 'field' => '30')
                                        ),
                        ),
'panels' =>array (
  
  array (
    
    array (
      'name' => 'document_name',
      'label' => 'LBL_DOC_NAME',
    ),
    
    array (
      'name' => 'revision',
      'label' => 'LBL_DOC_VERSION',
    ),
  ),
  
  array (
    
    array (
      'name' => 'is_template',
      'label' => 'LBL_DET_IS_TEMPLATE',
    ),
    
    array (
      'name' => 'template_type',
      'label' => 'LBL_DET_TEMPLATE_TYPE',
    ),
  ),
  
  array (
      'category_id',
      'subcategory_id',
  ),
  
  array (
	  'status',

	  'team_name',

  ),
  
  array (
      'last_rev_created_name',
      'last_rev_create_date',
  ),
  
  array (
      'active_date',
      'exp_date',
  ),
  
  array (
    'related_doc_name',
    'related_doc_rev_number',
  ),
  
  array (
    
    array (
      'name' => 'description',
      'label' => 'LBL_DOC_DESCRIPTION',
    ),
  ),
  
  array (
    
    array (
      'name' => 'filename',
      'displayParams' => array('link'=>'filename', 'id'=>'document_revision_id'), 
    ),

  ),
)


   
);
?>
