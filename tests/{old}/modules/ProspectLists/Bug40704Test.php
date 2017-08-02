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
 
class Bug40704Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function testUserColumnNotSortable()
	{
		require_once('modules/ProspectLists/metadata/listviewdefs.php');
		if(!empty($listViewDefs['ProspectLists']['ASSIGNED_USER_NAME'])){
			$this->assertTrue(empty($listViewDefs['ProspectLists']['ASSIGNED_USER_NAME']['sortable']), "User column should not be sortable");
		}
	}
}
