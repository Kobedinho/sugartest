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
 
class Bug39280Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testListViewName2Display() {
    	require_once('modules/Teams/metadata/listviewdefs.php');
    	$this->assertContains('related_fields', $listViewDefs['Teams']['NAME'], "Related fields entry is missing");
    	$this->assertContains('name_2', $listViewDefs['Teams']['NAME']['related_fields'], "name_2 fields entry is missing");
	}

}
