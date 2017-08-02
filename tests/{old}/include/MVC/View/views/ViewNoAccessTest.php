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
 

class ViewNoAccessTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->markTestIncomplete('FRM needs to fix this.');
	}
    public function testConstructor() 
	{
	    $view = new ViewNoaccess;
        
        $this->assertEquals('noaccess',$view->type);
	}
	
	public function testDisplay()
	{
	    $view = new ViewNoaccess;
        $view->display();
        $this->expectedString('<p class="error">Warning: You do not have permission to access this module.</p>');
	}
}
