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
 

class Bug50117Test extends Sugar_PHPUnit_Framework_TestCase
{
   		
	private $_listViewSmartyOutput1;
	private $_listViewSmartyOutput2;
	
	public function setUp()
    {
        $enumField = SugarFieldHandler::getSugarField('enum');
   		$parentFieldArray = array(
		    					'ACCEPT_STATUS_NAME' => 'Accepted',		
							);
		$vardef = array(
					    'name' => 'accept_status_name',
					    'type' => 'enum',
					    'source' => 'non-db',
					    'vname' => 'LBL_LIST_ACCEPT_STATUS',
					    'options' => 'dom_meeting_accept_status',
					    'massupdate' => false,
					    'studio' => Array
					        (
					            'listview' => false,
					            'searchview' => false,
					        )
					);
		$displayParams = array(
							'vname' => 'LBL_LIST_ACCEPT_STATUS',
						    'width' => '11%',
						    'sortable' => false,
						    'linked_field' => 'users',
						    'linked_field_set' => 'users',
						    'name' => 'accept_status_name',
							'module' => 'Users',
						);
		$col = 1;
		
		$this->_listViewSmartyOutput1 = trim($enumField->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col));
		
		$vardef['name'] = 'just_another_name';
		$parentFieldArray['JUST_ANOTHER_NAME'] = 'None';
		
		$this->_listViewSmartyOutput2 = trim($enumField->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col));
	}
    
     /**
     * @bug 50117
     */
	public function testListViewSmarty()
	{	
		$this->assertEquals("Accepted", $this->_listViewSmartyOutput1);
		$this->assertEquals("None", $this->_listViewSmartyOutput2);
    }
}
