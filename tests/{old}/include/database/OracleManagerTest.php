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


class OracleManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var OracleManager */
    protected $_db = null;

    static public function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        $this->_db = new OracleManager();
    }

    public function testQuote()
    {
        $string = "'dog eat ";
        $this->assertEquals($this->_db->quote($string),"''dog eat ");
    }

    public function testArrayQuote()
    {
        $string = array("'dog eat ");
        $this->_db->arrayQuote($string);
        $this->assertEquals($string,array("''dog eat "));
    }

    public function providerConvert()
    {
        $returnArray = array(
                array(
                    array('foo','nothing'),
                    'foo'
                ),
                array(
                    array('foo','date'),
                    "to_date(foo, 'YYYY-MM-DD')"
                    ),
                array(
                    array('foo','time'),
                    "to_date(foo, 'HH24:MI:SS')"
                    ),
                array(
                    array('foo','datetime'),
                    "to_date(foo, 'YYYY-MM-DD HH24:MI:SS')"
                    ),
                array(
                    array('foo','datetime',array(1,2,3)),
                    "to_date(foo, 'YYYY-MM-DD HH24:MI:SS',1,2,3)"
                    ),
                array(
                    array('foo','today'),
                    'sysdate'
                    ),
                array(
                    array('foo','left'),
                    "LTRIM(foo)"
                    ),
                array(
                    array('foo','left',array(1,2,3)),
                    "LTRIM(foo,1,2,3)"
                    ),
                array(
                    array('foo','date_format'),
                    "TO_CHAR(foo, 'YYYY-MM-DD')"
                    ),
                array(
                    array('foo','date_format',array("'%Y-%m'")),
                    "TO_CHAR(foo, 'YYYY-MM')"
                    ),
               array(
                    array('foo','date_format',array(1,2,3)),
                    "TO_CHAR(foo, 'YYYY-MM-DD')"
                    ),
                array(
                    array('foo','time_format'),
                    "TO_CHAR(foo,'HH24:MI:SS')"
                    ),
                array(
                    array('foo','time_format',array(1,2,3)),
                    "TO_CHAR(foo,1,2,3)"
                    ),
                array(
                    array('foo','IFNULL'),
                    "NVL(foo,'')"
                    ),
                array(
                    array('foo','IFNULL',array(1,2,3)),
                    "NVL(foo,1,2,3)"
                    ),
                array(
                    array('foo','CONCAT'),
                    "foo"
                    ),
                array(
                    array('foo','CONCAT',array(1,2,3)),
                    "foo||1||2||3"
                    ),
                array(
                    array('foo','text2char'),
                    "to_char(foo)"
                    ),
                array(
                    array('foo','length'),
                    "LENGTH(foo)"
                ),
                array(
                    array('foo','month'),
                    "TO_CHAR(foo, 'MM')"
                ),
                array(
                    array('foo','quarter'),
                    "TO_CHAR(foo, 'Q')"
                ),
                array(
                    array('foo','add_date',array(1,'day')),
                    "(foo + 1)"
                ),
                array(
                    array('foo','add_date',array(2,'week')),
                    "(foo + 2*7)"
                ),
                array(
                    array('foo','add_date',array(3,'month')),
                    "ADD_MONTHS(foo, 3)"
                ),
                array(
                    array('foo','add_date',array(4,'quarter')),
                    "ADD_MONTHS(foo, 4*3)"
                ),
                array(
                    array('foo','add_date',array(5,'year')),
                    "ADD_MONTHS(foo, 5*12)"
                ),
                array(
                    array('1.23','round',array(6)),
                    "round(1.23, 6)"
                ),
                array(
                    array('date_created', 'date_format', array('%v')),
                    "TO_CHAR(date_created, 'IW')"
                ),
        );
        return $returnArray;
    }

    /**
     * @ticket 33283
     * @dataProvider providerConvert
     */
    public function testConvert(array $parameters, $result)
    {
        $this->assertEquals($result, call_user_func_array(array($this->_db, "convert"), $parameters));
     }

     /**
      * @ticket 33283
      */
     public function testConcat()
     {
         $ret = $this->_db->concat('foo',array('col1','col2','col3'));
         $this->assertEquals("LTRIM(RTRIM(NVL(foo.col1,'')||' '||NVL(foo.col2,'')||' '||NVL(foo.col3,'')))", $ret);
     }

     public function providerFromConvert()
     {
         $returnArray = array(
             array(
                 array('foo','nothing'),
                 'foo'
                 ),
                 array(
                     array('2009-01-01 12:00:00','date'),
                     '2009-01-01'
                     ),
                 array(
                     array('2009-01-01 12:00:00','time'),
                     '12:00:00'
                     )
                 );

         return $returnArray;
     }

     /**
      * @ticket 33283
      * @dataProvider providerFromConvert
      */
     public function testFromConvert(
         array $parameters,
         $result
         )
     {
         $this->assertEquals($result,
             $this->_db->fromConvert($parameters[0],$parameters[1])
         );
    }

    public function providerFullTextQuery()
    {
        return array(
            array(array('word1'), array(), array(),
                "CONTAINS(unittest, '({word1})', 1) > 0"),
            array(array("'word1'"), array(), array(),
                "CONTAINS(unittest, '({''word1''})', 1) > 0"),
            array(array('word1', 'word2'), array(), array(),
                "CONTAINS(unittest, '({word1} | {word2})', 1) > 0"),
            array(array('word1', 'word2'), array('mustword'), array(),
                "CONTAINS(unittest, '{mustword} & ({word1} | {word2})', 1) > 0"),
            array(array('word1', 'word2'), array('mustword', 'mustword2'), array(),
                "CONTAINS(unittest, '{mustword} & {mustword2} & ({word1} | {word2})', 1) > 0"),
            array(array(), array('mustword', 'mustword2'), array(),
                "CONTAINS(unittest, '{mustword} & {mustword2}', 1) > 0"),
            array(array('word1'), array(), array('notword'),
                "CONTAINS(unittest, '({word1}) ~{notword}', 1) > 0"),
            array(array('word1'), array(), array('notword', 'notword2'),
                "CONTAINS(unittest, '({word1}) ~{notword} ~{notword2}', 1) > 0"),
            array(array('word1', 'word2'), array('mustword', 'mustword2'), array('notword', 'notword2'),
                "CONTAINS(unittest, '{mustword} & {mustword2} & ({word1} | {word2}) ~{notword} ~{notword2}', 1) > 0"),
        );
    }

    /**
     * @ticket 37435
     * @dataProvider providerFullTextQuery
     * @param array $terms
     * @param string $result
     */
    public function testFullTextQuery($terms, $must_terms, $exclude_terms, $result)
    {
        $this->assertEquals($result,
            $this->_db->getFulltextQuery('unittest', $terms, $must_terms, $exclude_terms));
    }

    /**
     * Test order_stability capability BR-2097
     */
    public function testOrderStability()
    {
        $msg = 'OracleManager should not have order_stability capability';
        $this->assertFalse($this->_db->supports('order_stability'), $msg);
    }

    /**
     * Test checks correct detection of type of vardef if type and dbType are different
     */
    public function testIsNullableTypeDetection()
    {
        $vardef = array(
            'type' => 'someMagicType',
            'dbType' => 'text',
        );
        /** @var PHPUnit_Framework_MockObject_MockObject|OracleManager $db */
        $db = $this->createPartialMock(get_class($this->_db), array('getFieldType', 'isTextType'));
        $db->expects($this->atLeastOnce())->method('getFieldType')->with($this->equalTo($vardef))->will($this->returnValue($vardef['dbType']));
        $db->expects($this->atLeastOnce())->method('isTextType')->with($this->equalTo($vardef['dbType']))->will($this->returnValue(true));
        SugarTestReflection::callProtectedMethod($db, 'isNullable', array($vardef));
    }

    public function providerCompareVardefs()
    {
        return array(
            array(
                array(
                    'name' => 'foo',
                    'type' => 'number',
                    'len' => '38',
                ),
                array(
                    'name' => 'foo',
                    'type' => 'int',
                    'len' => '38,2',
                ),
                true
            ),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'number',
                    'len' => '30',
                ),
                array(
                    'name' => 'foo',
                    'type' => 'int',
                    'len' => '31',
                ),
                false
            ),
        );
    }

    /**
     * Test for number fields compare in oracle
     * @param array $fieldDef1 Field from database
     * @param array $fieldDef2 Field from vardefs
     * @param bool $expectedResult If fields the same or not
     *
     * @dataProvider providerCompareVarDefs
     */
    public function testCompareVarDefs($fieldDef1, $fieldDef2, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_db->compareVarDefs($fieldDef1, $fieldDef2));
    }
}
