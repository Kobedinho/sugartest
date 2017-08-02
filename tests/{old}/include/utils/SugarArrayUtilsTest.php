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
 
require_once 'include/utils/array_utils.php';

class SugarArrayUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
	
	public function test_array_merge_values()
	{	
		$array1 = array("a","b","c");
		$array2 = array("x","y","z");
		$array3 = array(1, 2, 3);
		$array4 = array("a", "b", "c", "d", "e");
		
		$expectedResult12 = array("ax","by","cz");
		$expectedResult13 = array("a1", "b2", "c3");
		$expectedResult14 = false;
		
		
		$this->assertEquals($expectedResult12, array_merge_values($array1, $array2));
		$this->assertEquals($expectedResult13, array_merge_values($array1, $array3));
		$this->assertEquals($expectedResult14, array_merge_values($array1, $array4));
			
	}
	
	
	public function test_array_search_insensitive()
	{
		$arrayLowerCase = array("alpha","bravo","charlie","delta","echo");
		$arrayUpperCase = array("ALPHA", "BRAVO", "CHARLIE", "DELTA", "ECHO");
		$arrayMixed = array("Alpha","Bravo","Charlie", "Delta", "Echo");
		$arrayEmpty = array();
		
		$this->assertTrue(array_search_insensitive("delta", $arrayLowerCase));
		$this->assertTrue(array_search_insensitive("delta", $arrayUpperCase));
		$this->assertTrue(array_search_insensitive("delta", $arrayMixed));
		$this->assertFalse(array_search_insensitive("delta", $arrayEmpty));	
	}
	
	public function test_object_to_array_recursive()
	{
		$simple = new SimpleObejct();
		
		$notSimple = new NotSimpleObject();
		$notSimple->setFoo(new SimpleObejct());
		$notObject = "foo";
		
		$simpleExpected = array('foo'=>'bar', 'b'=>1);
		$notSimpleExpected = array('foo'=>array('foo'=>'bar', 'b'=>1), 'b'=>1);
		$notObjectExpected = 'foo';
		
		$this->assertEquals($simpleExpected, object_to_array_recursive($simple));
		$this->assertEquals($notSimpleExpected, object_to_array_recursive($notSimple));
		$this->assertEquals($notObjectExpected, object_to_array_recursive($notObject));
		
	}
	
	public function test_overide_value_to_string()
	{
		$name = 'name';
		$value_name = 1;
		$value = 4;
		
		$expected = '$name[1] = 4;';
		
		$this->assertEquals($expected, override_value_to_string($name, $value_name, $value));
		
	}
	
	//To do: test eval == true.
	public function test_override_value_to_string_recursive()
	{
		$key_names = array(1, 2, 3, 4, 5);
		global $array_name; 
		$array_name= 'name';
		$value = 'foo';
	
		
		$expectedNoEval = '$name[1][2][3][4][5]='."'".'foo'."';";
		$expectedEval = true;
		
		$this->assertEquals($expectedNoEval, override_value_to_string_recursive($key_names, $array_name, $value));
		global $name;
		
		$array = override_value_to_string_recursive($key_names, $array_name, $value, true);
	} 
	
	
	//array_name is never used in this function...
	public function test_override_recursive_helper()
	{
		$key_names = array(1, 2, 3, 4, 5);
		$array_name = 'name';
		$value = 'foo';
		
		$expected = '$name[1][2][3][4][5]='."'".$value."';";
		
		$this->assertEquals($expected, override_value_to_string_recursive($key_names, $array_name, $value));	
	} 

    //Todo: hit the if statement
    public function test_setDeepArrayValue()
    {
        $arrayActualSimple = array(1=>'a');
        setDeepArrayValue($arrayActualSimple, 1, 'b');
        $arrayExpectedSimple = array(1=>'b');

        $this->assertEquals($arrayExpectedSimple, $arrayActualSimple);
    }
    
    /**
     * Note that this test case is moved from "array_utils.php".
     * @ticket 396
     * @dataProvider providerOverride
     */
    public function test_override_value_to_string_recursive2($array_name, $value_name, $value, $config, $expected)
    {
        $this->assertEquals(
            $expected,
            override_value_to_string_recursive2($array_name, $value_name, $value, true, $config)
        );
    }

    /**
     * This function provides inputs for test_override_value_to_string_recursive2().
     *
     * @return array the expected values of the test cases.
     */
    public function providerOverride()
    {
        $returnArray = array(
            array( // Append: sequential array exists in config.php
                "sugar_config",
                "http_referer_396",
                array('list' => array(3 => 'location.com')), // structure from config_override.php
                array('http_referer_396' =>
                    array('list' => array(0 => 'abc.com', 1 => '123.com', 2 => 'mylocation.com'))),
                "\$sugar_config['http_referer_396']['list'][] = 'location.com';\n"
            ),
            array( // Append: non-sequential array exists in config.php
                "sugar_config",
                "http_referer_396",
                array('list' => array(3 => 'location.com')), // structure from config_override.php
                array('http_referer_396' => array('list' => array(0 => 'abc.com',  2 => 'mylocation.com'))),
                "\$sugar_config['http_referer_396']['list'][3] = 'location.com';\n"
            ),
            array( // Append: no array exists in config.php and key = 0, treat it as append
                "sugar_config",
                "http_referer_396",
                array('list' => array(0 => 'location.com')), // structure from config_override.php
                array(),
                "\$sugar_config['http_referer_396']['list'][] = 'location.com';\n"
            ),
            array( // Override: sequential array exists in config.php but old key is overridden
                "sugar_config",
                "http_referer_396",
                array('list' => array(0 => 'otherlocation.com')), // structure from config_override.php
                array('http_referer_396' => array('list' => array(0 => 'location.com', 1 => '123.com'))),
                "\$sugar_config['http_referer_396']['list'][0] = 'otherlocation.com';\n"
            ),
            array( // Override: does not exist in config.php
                "sugar_config",
                "full_text_engine_396",
                array('Elastic' => array('curl' => array(123 => 'user:password'))), // from config_override.php
                array(),
                "\$sugar_config['full_text_engine_396']['Elastic']['curl'][123] = 'user:password';\n"
            ),
            array( // Override: key is a string
                "sugar_config",
                "test_396",
                array('def' => 'def2'), // structure from config_override.php
                array("test_396" => array('abc' => 'abc', 'def' => 'def')),
                "\$sugar_config['test_396']['def'] = 'def2';\n"
            ),
            array( // Override: test app_list_strings
                "app_list_strings",
                "http_referer_396",
                array('list' => array(0 => 'location.com')), // structure from config_override.php
                null,
                "\$app_list_strings['http_referer_396']['list'][0] = 'location.com';\n"
            ),
        );
        return $returnArray;
    }

    /**
     * This function tests cases for the upgrade scenario.
     *
     * @param string $array_name : name of the array
     * @param string $value_name : name of the array keys
     * @param array  $value : value of current array
     * @param array  $config : value of current array
     * @param string $expected : the expected result of the test case.
     *
     * @dataProvider providers_Override2StringForUpgrade
     */
    public function test_Override2StringForUpgrade($array_name, $value_name, $value, $config, $expected)
    {
        $this->assertEquals(
            $expected,
            override_value_to_string_recursive2($array_name, $value_name, $value, true, $config)
        );
    }

    /**
     * This function provides inputs for test_Override2StringWithEmptyOriginal().
     *
     * @return array the expected values of the test.
     */
    public function providers_Override2StringForUpgrade()
    {
        $returnArray = array(
            array( // Case: $value is boolean
                "sugar_config",
                "fts_disable_notification",
                false,
                array(),
                "\$sugar_config['fts_disable_notification'] = false;\n"
            ),
            array( // Case: $value is an array
                "sugar_config",
                "dashlet_display_row_options",
                array('0' => '1', '1' => '5', '2' => '10'),
                array(),
                "\$sugar_config['dashlet_display_row_options'][] = '1';\n" .
                "\$sugar_config['dashlet_display_row_options'][1] = '5';\n" .
                "\$sugar_config['dashlet_display_row_options'][2] = '10';\n"
            ),
            array( // Case: $value is an element added in an array
                   // the original input from "config_override.php" could be
                   // $sugar_config['dashlet_display_row_options'][3] = '20', or
                   // $sugar_config['dashlet_display_row_options'][] = '20';
                "sugar_config",
                "dashlet_display_row_options",
                array(3 => '20'),
                array('dashlet_display_row_options' => array(0 => '1', 1 => '5', 2 => '10')),
                "\$sugar_config['dashlet_display_row_options'][] = '20';\n"
            ),
            array( // Case: $value is a completely new element
                   // the original input from "config_override.php" could be
                   // $sugar_config['dashlet_display_row_options'][0] = '20', or
                   // $sugar_config['dashlet_display_row_options'][] = '20';
                "sugar_config",
                "dashlet_display_row_options",
                array(0 => '20'),
                array(),
                "\$sugar_config['dashlet_display_row_options'][] = '20';\n"
            ),
        );
        return $returnArray;
    }

}

class SimpleObejct
{
	public $foo = 'bar';
	public $b = 1;
}

class NotSimpleObject
{
	public $foo;
	public $b = 1;
	public function setFoo($input)
	{
		$this->foo = $input;
	}
}
