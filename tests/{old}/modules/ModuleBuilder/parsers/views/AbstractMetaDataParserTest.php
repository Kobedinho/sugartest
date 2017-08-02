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


/**
 * Wrapper class to test protected functions of AbstractMetaDataParser
 */
class TestMetaDataParser extends AbstractMetaDataParser
{
    //Trim Field defs implementation is required to extend AbstractMetaDataParser
    static function _trimFieldDefs ( $def ) {}
    
    //Wrapper of isTrue for testing purposes
    public static function testIsTrue($val)
    {
        return self::isTrue($val);
    }
}

class AbstractMetaDataParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test the the isTrue function works correctly for boolean and non-boolean values
     * @group Studio
     */
    public function testIsTrue()
    {
        $testValues = array(
            true => true,
            false => false,
            0 => false,
            "" => false,
            "true" => true,
            "false" => false,
            "FALSE" => false,
            "0" => false,
            "something" => true,
        );
        
        foreach($testValues as $testVal => $boolVal){
            $this->assertEquals($boolVal, TestMetaDataParser::testIsTrue($testVal));
        }
    }
    
    /**
     * Tests validation of studio defs for client and view specific rules
     * 
     * @dataProvider studioValidationDefProvider
     * @param array $def Array of fields defs
     * @param string $view The view name to check defs for
     * @param string $client The client to check defs for
     * @param bool $expected The expected result of the validation call
     */
    public function testGetClientStudioValidation($def, $view, $client, $expected)
    {
        $actual = AbstractMetaDataParser::getClientStudioValidation($def, $view, $client);
        $this->assertEquals($expected, $actual);
    }
    
    public function studioValidationDefProvider()
    {
        return array(
            // Test no client specific rule in the defs is null
            array(
                'def' => array(),
                'view' => 'list',
                'client' => 'base',
                'expected' => null,
            ),
            // Test no client passed is null
            array(
                'def' => array('base' => array()),
                'view' => 'list',
                'client' => '',
                'expected' => null,
            ),
            // Test def[client] is a string is null
            array(
                'def' => array('base' => 'list'),
                'view' => 'list',
                'client' => 'base',
                'expected' => null,
            ),
            // Test no view passed is null
            array(
                'def' => array('mobile' => array()),
                'view' => '',
                'client' => 'mobile',
                'expected' => null,
            ),
            // Test def[client] is boolean returns the boolean
            array(
                'def' => array('mobile' => true),
                'view' => 'list',
                'client' => 'mobile',
                'expected' => true,
            ),
            array(
                'def' => array('mobile' => false),
                'view' => 'list',
                'client' => 'mobile',
                'expected' => false,
            ),
            // Test client and view specific rules are boolean
            array(
                'def' => array('mobile' => array('list' => false)),
                'view' => 'list',
                'client' => 'mobile',
                'expected' => false,
            ),
            array(
                'def' => array('custom' => array('record' => 'somestring')),
                'view' => 'record',
                'client' => 'custom',
                'expected' => true,
            ),
        );
    }

    public function validFieldDataProvider()
    {
        return array(
            array(true, array('name' => 'uploadfile', 'source' => 'non-db', 'type' => 'file'), '', ''),
            array(false, array('name' => 'uploadfile', 'source' => 'non-db', 'type' => 'varchar'), '', '')
        );
    }

    /**
     * @dataProvider validFieldDataProvider
     */
    public function testValidField($expected, $def, $view, $client)
    {
        $this->assertEquals($expected, AbstractMetaDataParser::validField($def, $view, $client));
    }
}