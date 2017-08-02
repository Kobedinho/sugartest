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


if (file_exists("custom/include/SugarSmarty/plugins/function.sugar_currency_format.php"))
{
	require_once("custom/include/SugarSmarty/plugins/function.sugar_currency_format.php");
}
else
{
	require_once("include/SugarSmarty/plugins/function.sugar_currency_format.php");
}

class Bug47406Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $mbvardef;
    private $smarty;

    public function setUp()
    {
        $this->smarty = new Smarty();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->mbvardef);
        unset($this->smarty);
        unset($GLOBALS['current_user']);
    }

    public function providerMBVardefAddFieldVardef()
    {
        return array(
            array(array('name' => 'field_name', 'default' => 0), 0),
            array(array('name' => 'field_name', 'default' => '0'), '0'),
            array(array('name' => 'field_name', 'default' => '0.00'), '0.00'),
            array(array('name' => 'field_name', 'default' => ' '), ' '),
        );
    }

    /**
     * @dataProvider providerMBVardefAddFieldVardef
     */
    public function testMBVardefAddFieldVardef($vardef, $expected)
    {
        unset($this->mbvardef);

        $this->mbvardef = $this->getMockBuilder('MBVardefs')
            ->disableOriginalConstructor(array('load'))
            ->setMethods(array('load'))
            ->setConstructorArgs(array('name', 'path', 'key'))
            ->getMock();

        $this->mbvardef->addFieldVardef($vardef);

        $this->assertEquals( $expected, $this->mbvardef->vardef['fields'][$vardef['name']]['default']);
    }

    public function providerSugarCurrencyFormat()
    {
        return array(
            array(array('var' => ''), '', false),
            array(array('var' => ' '), '0.00', true),
            array(array('var' => 0), '0.00', true),
            array(array('var' => 0.00), '0.00', true),
            array(array('var' => '0.00'), '0.00', true),
        );
    }

    /**
     * @dataProvider providerSugarCurrencyFormat
     */
    public function testSugarCurrencyFormat($params, $expected, $as_regexp = false)
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $return = smarty_function_sugar_currency_format($params, $this->smarty);
        if ( $as_regexp )
        {
            $this->assertRegExp('/'.$expected.'$/', $return);
        }
        else
        {
            $this->assertEquals( $expected, $return);
        }
    }
}
?>
