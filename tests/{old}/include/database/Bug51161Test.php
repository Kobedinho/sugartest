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



class Bug51161Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;



	public function setUp()
    {
	    $this->_db = DBManagerFactory::getInstance();
	}

    public function providerClobsNonOracle()
    {
        return array(
            array(
                array(
                    'foo' => array(
                        'name' => 'foo',
                        'type' => 'clob',
                        'len' => '1024',
                    ),
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
            array(
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'type' => 'blob',
                        'len' => '1024',
                    ),
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
            array(
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'type' => 'text',
                        'len' => '1024',
                    ),
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
        );
    }

    public function providerClobsOracle()
    {
        return array(
            array(
                array(
                    'foo' => array(
                        'name' => 'foo',
                        'type' => 'clob',
                        'len' => '1024',
                    ),
                ),
                '/foo\s+$baseType/i',
            ),
            array(
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'type' => 'blob',
                        'len' => '1024',
                    ),
                ),
                '/foo\s+$baseType/i',
            ),
            array(
                array(
                    'foo' => array (
                        'name' => 'foo',
                        'type' => 'text',
                        'len' => '1024',
                    ),
                ),
                '/foo\s+$baseType/i',
            ),
        );
    }


	public function providerBug51161()
    {
        $returnArray = array(
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'varchar',
						'len' => '34',
						),
					),
					'/foo\s+$baseType\(34\)/i',
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'nvarchar',
						'len' => '35',
						),
					),
					'/foo\s+$baseType\(35\)/i',
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'char',
						'len' => '23',
						),
					),
					'/foo\s+$baseType\(23\)/i',
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'clob',
						),
					),
					'/foo\s+$colType/i',
				),
           );

        return $returnArray;
    }

    /**
     * @dataProvider providerBug51161
     * @param $fieldDef
     * @param $successRegex
     */
    public function testBug51161($fieldDef, $successRegex)
    {
        // Allowing type part variables in passed in regular expression so that database specific mappings
        // can be accounted for in the test
        list($sql, $successRegex) = $this->getTableSql($fieldDef, $successRegex);
        $this->assertEquals(
            1,
            preg_match($successRegex, $sql),
            "Resulting statement: $sql failed to match /$successRegex/"
        );
    }

    /**
     * @dataProvider providerClobsOracle
     * @param $fieldDef
     * @param $successRegex
     */
    public function testOracleClobs($fieldDef, $successRegex)
    {
        if (!$this->_db instanceof OracleManager) {
            $this->markTestSkipped('Oracle only');
        }
        list($sql, $successRegex) = $this->getTableSql($fieldDef, $successRegex);
        $this->assertEquals(
            1,
            preg_match($successRegex, $sql),
            "Resulting statement: $sql failed to match /$successRegex/"
        );
    }

    /**
     * @dataProvider providerClobsNonOracle
     * @param $fieldDef
     * @param $successRegex
     */
    public function testNonOracleClobs($fieldDef, $successRegex)
    {
        if ($this->_db instanceof OracleManager) {
            $this->markTestSkipped('non-Oracle only');
        }
        list($sql, $successRegex) = $this->getTableSql($fieldDef, $successRegex);
        $this->assertEquals(
            1,
            preg_match($successRegex, $sql),
            "Resulting statement: $sql failed to match /$successRegex/"
        );
    }

    protected function getTableSql($fieldDef, $successRegex)
    {
        $ftype = $this->_db->getFieldType($fieldDef['foo']);
        $colType = $this->_db->getColumnType($ftype);
        $successRegex = preg_replace('/\$colType/', $colType, $successRegex);
        if($type = $this->_db->getTypeParts($colType)){
            if(isset($type['baseType']))
                $successRegex = preg_replace('/\$baseType/', $type['baseType'], $successRegex);
            if(isset($type['len']))
                $successRegex = preg_replace('/\$len/', $type['len'], $successRegex);
            if(isset($type['scale']))
                $successRegex = preg_replace('/\$scale/', $type['scale'], $successRegex);
            if(isset($type['arg']))
                $successRegex = preg_replace('/\$arg/', $type['arg'], $successRegex);
        }
        return array($this->_db->createTableSQLParams('test', $fieldDef, array()), $successRegex);
    }
}
