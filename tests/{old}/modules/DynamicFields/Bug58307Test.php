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


class Bug58307Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_fv;
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        
        // Setting the module in the request for this test
        $_REQUEST['view_module'] = 'Accounts';
        
        $this->_fv = new FieldViewer();
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }
    
    public function testPhoneFieldGetsCorrectFieldForm()
    {
        $vardef = array(
            'type' => 'phone',
            'len' => 30,
        );
        
        $layout = $this->_fv->getLayout($vardef);
        
        // Inspect the layout for things we expect. Yes, this is kinda not 
        // scientific but to support varies builds this needs to happen this way.
        $this->assertContains('function forceRange(', $layout, "Layout does not contain expected known function string forceRange");
        $this->assertContains("<input type='text' name='default' id='default' value='' maxlength='30'>", $layout, "Layout does not contain expected known function call");
    }
}