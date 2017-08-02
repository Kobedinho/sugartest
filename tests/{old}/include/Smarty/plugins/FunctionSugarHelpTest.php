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

require_once 'include/SugarSmarty/plugins/function.sugar_help.php';

class FunctionSugarHelpTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        $this->_smarty = new Sugar_Smarty;
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function providerSpecialCharactersHandledInTextParameter()
    {
        return array(
            array(
                'dog "the" bounty hunter & friends are <b>cool</b>',
                'dog &quot;the&quot; bounty hunter &amp; friends are &lt;b&gt;cool&lt;/b&gt;',
                ),
            array(
                "dog 'the' bounty hunter",
                "dog \'the\' bounty hunter"
                ),
            );
    }
    
    /**
     * @dataProvider providerSpecialCharactersHandledInTextParameter
     */
	public function testSpecialCharactersHandledInTextParameter(
        $string,
        $returnedString
        )
    {
        $this->assertContains($returnedString, smarty_function_sugar_help(array('text'=>$string),$this->_smarty));
    }
    
    public function testExtraParametersAreAdded()
    {
        $string = 'my string';
        
        $output = smarty_function_sugar_help(array('text'=>$string,'myPos'=>'foo', 'atPos'=>'bar'),$this->_smarty);
        
        $this->assertContains(",'foo','bar'",$output);
    }
}
