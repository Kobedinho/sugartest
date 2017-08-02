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

require_once 'include/SugarSmarty/plugins/function.sugar_link.php';

class FunctionSugarLinkTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_smarty = new Sugar_Smarty;
    }
    
    public function testReturnModuleLinkOnly()
    {
        $string = 'my string';
        
        $output = smarty_function_sugar_link(
            array('module'=>'Dog','link_only'=>'1'),
            $this->_smarty);
        
        $this->assertContains("index.php?module=Dog&action=index", $output);
    }
    
    public function testReturnModuleLinkWithAction()
    {
        $output = smarty_function_sugar_link(
            array('module'=>'Dog','action'=>'cat','link_only'=>'1'),
            $this->_smarty);
        
        $this->assertContains("index.php?module=Dog&action=cat", $output);
    }
    
    public function testReturnModuleLinkWithActionAndExtraParams()
    {
        $output = smarty_function_sugar_link(
            array('module'=>'Dog','action'=>'cat','extraparams'=>'foo=bar','link_only'=>'1'),
            $this->_smarty);
        
        $this->assertContains("index.php?module=Dog&action=cat&foo=bar", $output);
    }
    
    /**
     * @ticket 33909
     */
    public function testReturnLinkWhenPassingData()
    {
        $data = array(
            '63edeacd-6ba5-b658-5e2a-4af9a5d682be',
            'http://localhost',
            'all',
            'iFrames',
            'Foo',
            );

        
        $output = smarty_function_sugar_link(
            array('module'=>'Dog','data'=>$data,'link_only'=>'1'),
            $this->_smarty);
        
        $this->assertContains(
            "index.php?module=iFrames&action=index&record=63edeacd-6ba5-b658-5e2a-4af9a5d682be&tab=true",
            $output
        );
    }
    
    public function testCreatingFullLink()
    {
        $output = smarty_function_sugar_link(
            array(
                'module'=>'Dog',
                'action'=>'cat',
                'id'=>'foo1',
                'class'=>'foo4',
                'style'=>'color:red;',
                'title'=>'foo2',
                'accesskey'=>'B',
                'options'=>'scope="row"',
                'label'=>'foo3',
            ),
            $this->_smarty
        );

        $this->assertContains(
            '<a href="index.php?module=Dog&action=cat" id="foo1" class="foo4" style="color:red;"'
            . ' scope="row" title="foo2" module="Dog">foo3</a>',
            $output
        );
    }
}
