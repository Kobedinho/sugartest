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
$viewdefs['Leads']['EditView'] = array(
    'templateMeta' => array('form' => array('hidden'=>array('<input type="hidden" name="prospect_id" value="{if isset($smarty.request.prospect_id)}{$smarty.request.prospect_id}{else}{$bean->prospect_id}{/if}">',
                                            				'<input type="hidden" name="account_id" value="{if isset($smarty.request.account_id)}{$smarty.request.account_id}{else}{$bean->account_id}{/if}">',
                                            				'<input type="hidden" name="contact_id" value="{if isset($smarty.request.contact_id)}{$smarty.request.contact_id}{else}{$bean->contact_id}{/if}">',
                                            				'<input type="hidden" name="opportunity_id" value="{if isset($smarty.request.opportunity_id)}{$smarty.request.opportunity_id}{else}{$bean->opportunity_id}{/if}">'),
                                            'buttons' => array(
															'SAVE',
															'CANCEL',
											)                				
                            ),
                            'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                           ),
 'javascript' => '<script type="text/javascript" language="Javascript">function copyAddressRight(form)  {ldelim} form.alt_address_street.value = form.primary_address_street.value;form.alt_address_city.value = form.primary_address_city.value;form.alt_address_state.value = form.primary_address_state.value;form.alt_address_postalcode.value = form.primary_address_postalcode.value;form.alt_address_country.value = form.primary_address_country.value;return true; {rdelim} function copyAddressLeft(form)  {ldelim} form.primary_address_street.value =form.alt_address_street.value;form.primary_address_city.value = form.alt_address_city.value;form.primary_address_state.value = form.alt_address_state.value;form.primary_address_postalcode.value =form.alt_address_postalcode.value;form.primary_address_country.value = form.alt_address_country.value;return true; {rdelim} </script>',
),
 'panels' =>array (
  'lbl_contact_information' => 
  array (

    array(
      'lead_source', 
      'status'
    ),

    array (
      array('name'=>'lead_source_description', 'displayParams'=>(array('rows'=>4,'cols'=>40))),
      array('name'=>'status_description', 'displayParams'=>(array('rows'=>4,'cols'=>40))),
    ),

    array('campaign_name','opportunity_amount'),

    array (
      'refered_by',

    ),
        
    array (
      
      array (
        'name' => 'first_name',
        'customCode' => '{html_options id="salutation" name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input id="first_name" name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
      ),
      'phone_work',
    ),
    
    array (
      array('name'=>'last_name',
            'displayParams'=>array('required'=>true),
      ),
      'phone_mobile',
    ),
    
    array (
      'birthdate',
      'phone_home',
    ),
    
    array (
      array('name'=>'account_name', 'type'=>'varchar', 'validateDependency'=>false,'customCode' => '<input name="account_name" {if ($fields.converted.value == 1)}disabled="true"{/if} size="30" maxlength="255" type="text" value="{$fields.account_name.value}">'),
	 'phone_other',
    ),
    
    array (
      NULL,
      'phone_fax',
    ),
    
    array (
      'title','do_not_call',
    ),
    
    array (
      'department',
    ),
    
    array (
       array('name'=>'team_name','displayParams'=>array('required'=>true)),
       ''
    ),
    
    array (
      'assigned_user_name',
      ''
    ),
  ),

   'lbl_email_addresses'=>array(
		array('email1')
   ),  
  
  'lbl_address_information' => 
  array (
    array (
      array (
	      'name' => 'primary_address_street',
          'hideLabel' => true,      
	      'type' => 'address',
	      'displayParams'=>array('key'=>'primary', 'rows'=>2, 'cols'=>30, 'maxlength'=>150),
      ),
      
      array (
	      'name' => 'alt_address_street',
	      'hideLabel'=>true,
	      'type' => 'address',
	      'displayParams'=>array('key'=>'alt', 'copy'=>'primary', 'rows'=>2, 'cols'=>30, 'maxlength'=>150),      
      ),
    ),  
  ),
 
  'lbl_description_information' => 
  array (
    array(
	    'test_c',
	    'description',
    )
  ),
)


);
?>
