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

abstract class MysqlManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlManager
     */
    protected $db;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
    }

    protected function setUp()
    {
        if ($GLOBALS['db']->dbType != 'mysql') {
            $this->markTestSkipped('The instance needs to be configured to use MySQL');
        }

        parent::setUp();
    }

    public function testQuote()
    {
        $string = "'dog eat ";
        $this->assertEquals($this->db->quote($string), "\\'dog eat ");
    }

    public function testArrayQuote()
    {
        $string = array("'dog eat ");
        $this->db->arrayQuote($string);
        $this->assertEquals($string,array("\\'dog eat "));
    }

    public function providerConvert()
    {
        $returnArray = array(
            array(
                array('foo','nothing'),
                'foo'
                ),
                array(
                    array('foo','today'),
                    'CURDATE()'
                    ),
                array(
                    array('foo','left'),
                    'LEFT(foo)'
                ),
                array(
                    array('foo','left',array('1','2','3')),
                    'LEFT(foo,1,2,3)'
                    ),
                array(
                    array('foo','date_format'),
                    'DATE_FORMAT(foo,\'%Y-%m-%d\')'
                        ),
                array(
                    array('foo','date_format',array('1','2','3')),
                    'DATE_FORMAT(foo,\'1\')'
                    ),
                array(
                    array('foo','date_format',array("'1'","'2'","'3'")),
                    'DATE_FORMAT(foo,\'1\')'
                    ),
                    array(
                    array('foo','datetime',array("'%Y-%m'")),
                    'foo'
                        ),
                array(
                    array('foo','IFNULL'),
                    'IFNULL(foo,\'\')'
                    ),
                array(
                    array('foo','IFNULL',array('1','2','3')),
                    'IFNULL(foo,1,2,3)'
                    ),
                array(
                    array('foo','CONCAT',array('1','2','3')),
                    'CONCAT(foo,1,2,3)'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT'),
                    'CONCAT(1,2,3)'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT',array('foo', 'bar')),
                    'CONCAT(1,2,3,foo,bar)'
                    ),
                array(
                    array('foo','text2char'),
                    'foo'
                ),
                array(
                    array('foo','length'),
                    "LENGTH(foo)"
                ),
                array(
                    array('foo','month'),
                    "MONTH(foo)"
                ),
                array(
                    array('foo','quarter'),
                    "QUARTER(foo)"
                ),
                array(
                    array('foo','add_date',array(1,'day')),
                    "DATE_ADD(foo, INTERVAL 1 day)"
                ),
                array(
                    array('foo','add_date',array(2,'week')),
                    "DATE_ADD(foo, INTERVAL 2 week)"
                ),
                array(
                    array('foo','add_date',array(3,'month')),
                    "DATE_ADD(foo, INTERVAL 3 month)"
                ),
                array(
                    array('foo','add_date',array(4,'quarter')),
                    "DATE_ADD(foo, INTERVAL 4 quarter)"
                ),
                array(
                    array('foo','add_date',array(5,'year')),
                    "DATE_ADD(foo, INTERVAL 5 year)"
                ),
                array(
                    array('1.23','round',array(6)),
                    "round(1.23, 6)"
                ),
                array(
                    array('date_created', 'date_format', array('%v')),
                    "DATE_FORMAT(date_created,'%v')"
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
        $this->assertEquals($result, call_user_func_array(array($this->db, "convert"), $parameters));
     }

     /**
      * @ticket 33283
      */
     public function testConcat()
     {
         $ret = $this->db->concat('foo', array('col1', 'col2', 'col3'));
         $this->assertEquals("LTRIM(RTRIM(CONCAT(IFNULL(foo.col1,''),' ',IFNULL(foo.col2,''),' ',IFNULL(foo.col3,''))))", $ret);
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
                     '2009-01-01 12:00:00'
                     ),
                 array(
                     array('2009-01-01 12:00:00','time'),
                     '2009-01-01 12:00:00'
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
         $this->assertEquals(
             $this->db->fromConvert($parameters[0], $parameters[1]),
             $result);
    }

    public function providerEmptyValues()
    {
        $returnArray = array(
            array(
                array("1970-01-01", 'date'), true,
                ),
            array(
                array("1970-01-01 00:00:00", 'datetime'), true,
                ),
            array(
                array("0000-00-00 00:00:00", 'datetime'), true,
                ),
            array(
                array("0000-00-00", 'date'), true,
                ),
            array(
                array("2013-01-01", 'date'), false,
                ),
            array(
                array("2013-01-01 09:04:32", 'datetime'), false,
                ),
            array(
                array("00:00:00", 'time'), true,
                ),
            array(
                array("12:32:30", 'time'), false,
                ),
            );

        return $returnArray;
    }


    /**
     * @ticket BR-238
     * @dataProvider providerEmptyValues
     */
    public function testEmptyValues($parameters, $expected)
    {
        $emptyValue = SugarTestReflection::callProtectedMethod($this->db, '_emptyValue', $parameters);
        $this->assertEquals($expected, $emptyValue);
    }

    /**
     * Test order_stability capability BR-2097
     */
    public function testOrderStability()
    {
        $msg = 'MysqlManager should not have order_stability capability';
        $this->assertFalse($this->db->supports('order_stability'), $msg);
    }
}
