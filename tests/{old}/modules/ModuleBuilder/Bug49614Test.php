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

 

class Bug49614Test extends Sugar_PHPUnit_Framework_TestCase
{	
    private $package;
    
	public function setUp()
	{
        $this->package = new MBPackage('SugarTestPackage');
	}
	
	public function tearDown()
	{
        unset($this->package);
	}
    
	public function testPopulateFromPostKeyValueWithSpaces()
	{
        $_REQUEST = array(
            'description' => '',
            'author' => 'Sugar CRM',
            'key' => ' key ',
            'readme' => ''
        );
        
        $this->package->populateFromPost();
        $this->assertEquals('key', $this->package->key);
	}
    
	public function testPopulateFromPostKeyValueWithoutSpaces()
	{
        $_REQUEST = array(
            'description' => '',
            'author' => 'Sugar CRM',
            'key' => 'key',
            'readme' => ''
        );
        
        $this->package->populateFromPost();
        $this->assertEquals('key', $this->package->key);
	}
}
?>