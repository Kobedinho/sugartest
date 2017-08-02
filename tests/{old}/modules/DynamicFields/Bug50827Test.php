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
 
class Bug50827Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_smarty;

    public function setUp()
    {
        $this->_smarty = new Sugar_Smarty();
    }

    public function tearDown()
    {
        unset($this->_smarty);
    }

	/**
     * @dataProvider bug50827DataProvider
     */
    public function testCalculatedVisible($vardef, $expected) 
    {
    	$this->_smarty->assign("vardef", $vardef);
    	$output = $this->_smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');
        
        if ($expected) {
        	$this->assertContains('name="calculated" id="calculated"', $output);
		} else {
			$this->assertNotContains('name="calculated" id="calculated"', $output);
		}
    }
    
    /**
     * Data provider for testCalculatedVisible()
     * @return array vardef, expected
     */
    public function bug50827DataProvider() {
        return array(
        	0 => array(
        		array(
        			'name'		=> 'email1',
					'vname'		=> 'LBL_EMAIL_ADDRESS',
					'type'		=> 'varchar',
					'function'	=> array(
						'name'		=> 'getEmailAddressWidget',
						'returns'	=> 'html'),
					'source'	=> 'non-db',
					'group'=>'email1',
		            'merge_filter' => 'enabled',
				    'studio' => array('editField' => true, 'searchview' => false, 'popupsearch' => false),
        		),
        		false
        	), 
        	1 => array(
        		array(
        			'name'		=> 'email1',
					'vname'		=> 'LBL_EMAIL_ADDRESS',
					'type'		=> 'varchar',
					'source'	=> 'non-db',
					'group'=>'email1',
		            'merge_filter' => 'enabled',
				    'studio' => array('editField' => true, 'searchview' => false, 'popupsearch' => false, 'calculated' => true),
        		),
        		true
        	), 
        	2 => array(
        		array(
        			'name'		=> 'email1',
					'vname'		=> 'LBL_EMAIL_ADDRESS',
					'type'		=> 'varchar',
					'source'	=> 'non-db',
					'group'=>'email1',
		            'merge_filter' => 'enabled',
				    'studio' => array('editField' => true, 'searchview' => false, 'popupsearch' => false, 'calculated' => false),
        		),
        		false
        	), 
        	3 => array(
        		array(
        			'name'		=> 'email1',
					'vname'		=> 'LBL_EMAIL_ADDRESS',
					'type'		=> 'varchar',
					'source'	=> 'non-db',
					'group'=>'email1',
		            'merge_filter' => 'enabled',
				    'studio' => array('editField' => true, 'searchview' => false, 'popupsearch' => false),
        		),
        		true
        	), 
        );
    }
    
}
