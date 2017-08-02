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
 

class Bug37307Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testRelationshipWithApostropheInLabelOutputsCorrectly()
	{
            if (empty($GLOBALS['app_list_strings'])) {
                $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
            }
		$bean_name = 'Foo';
		$link_module = 'Bar';
		$linked_field = array(
		    'name' => 'Dog',
		    'label' => "My Dog&#039;s",
		    'relationship' => 'Cat',
		    );
		
		$view = new MockReportsViewBuildreportmoduletree;
		$output = $view->_populateNodeItem($bean_name,$link_module,$linked_field);
		
		$this->assertEquals(
		    "javascript:SUGAR.reports.populateFieldGrid('Bar','Cat','Foo','My Dog\'s');",
		    $output['href']
		    );
	}
}

class MockReportsViewBuildreportmoduletree extends ReportsViewBuildreportmoduletree
{
    public function _populateNodeItem($bean_name,$link_module,$linked_field)
    {
        return parent::_populateNodeItem($bean_name,$link_module,$linked_field);
    }
}
