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

$viewdefs['Documents']['QuickCreate'] = array(
    'templateMeta' => array('form' => array('enctype'=>'multipart/form-data',
                                            'hidden'=>array('<input type="hidden" name="old_id" value="{$fields.document_revision_id.value}">',
                                                            '<input type="hidden" name="parent_id" value="{$smarty.request.parent_id}">',
                                                            '<input type="hidden" name="parent_type" value="{$smarty.request.parent_type}">',)),
                                            
                            'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
                            'includes' => 
                              array (
                                array('file' => 'include/javascript/popup_parent_helper.js'),
                                array('file' => 'modules/Documents/documents.js'),
                              ),
),
 'panels' =>array (
  'default' => 
  array (
    
    array (

      array('name'=>'uploadfile', 
            'customCode' => '<input type="hidden" name="escaped_document_name"><input name="uploadfile" type="file" size="30" maxlength="" onchange="setvalue(this);" value="{$fields.filename.value}">{$fields.filename.value}',
            'displayParams'=>array('required'=>true),
            ),
      'status_id',            
    ),
    
    array (
      'document_name',
      array('name'=>'revision',
            'customCode' => '<input name="revision" type="text" value="{$fields.revision.value}">'
           ),    
    ),    
    
    array (
        array (
          'name' => 'template_type',
          'label' => 'LBL_DET_TEMPLATE_TYPE',
        ),
    	array (
          'name' => 'is_template',
          'label' => 'LBL_DET_IS_TEMPLATE',
        ),
    ),
    
    array (
       array('name'=>'active_date','displayParams'=>array('required'=>true)),
       'category_id',
    ),
    
    array (
      'exp_date',
      'subcategory_id',
    ),    
    
    array (
      array('name'=>'team_name','displayParams'=>array('required'=>true)),
     
    ),

    array (
      array('name'=>'description', 'displayParams'=>array('rows'=>10, 'cols'=>120)),
    ),
  ),
)

);
