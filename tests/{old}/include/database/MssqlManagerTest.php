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

abstract class MssqlManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var MssqlManager
     */
    protected $_db;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
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
                    array('foo','today'),
                    'GETDATE()'
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
                    'LEFT(CONVERT(varchar(10),foo,120),10)'
                    ),
                array(
                    array('foo','date_format',array('1','2','3')),
                    'LEFT(CONVERT(varchar(10),foo,120),10)'
                    ),
                array(
                    array('foo','date_format',array("'%Y-%m'")),
                    'LEFT(CONVERT(varchar(7),foo,120),7)'
                    ),
                array(
                    array('foo','IFNULL'),
                    'ISNULL(foo,\'\')'
                    ),
                array(
                    array('foo','IFNULL',array('1','2','3')),
                    'ISNULL(foo,1,2,3)'
                    ),
                array(
                    array('foo','CONCAT',array('1','2','3')),
                    'foo+1+2+3'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT'),
                    '1+2+3'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT',array('foo', 'bar')),
                    '1+2+3+foo+bar'
                    ),
                array(
                    array('foo','text2char'),
                    'CAST(foo AS varchar(8000))'
                ),
                array(
                    array('foo','length'),
                    "LEN(foo)"
                ),
                array(
                    array('foo','month'),
                    "MONTH(foo)"
                ),
                array(
                    array('foo','quarter'),
                    "DATENAME(quarter, foo)"
                ),
                array(
                    array('foo','add_date',array(1,'day')),
                    "DATEADD(day,1,foo)"
                ),
                array(
                    array('foo','add_date',array(2,'week')),
                    "DATEADD(week,2,foo)"
                ),
                array(
                    array('foo','add_date',array(3,'month')),
                    "DATEADD(month,3,foo)"
                ),
                array(
                    array('foo','add_date',array(4,'quarter')),
                    "DATEADD(quarter,4,foo)"
                ),
                array(
                    array('foo','add_date',array(5,'year')),
                    "DATEADD(year,5,foo)"
                ),
                array(
                    array('1.23','round',array(6)),
                    "round(1.23, 6)"
                ),
                array(
                    array('date_created', 'date_format', array('%v')),
                    "datepart(isoww, date_created)"
                )
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
         $this->assertEquals("LTRIM(RTRIM(ISNULL(foo.col1,'')+' '+ISNULL(foo.col2,'')+' '+ISNULL(foo.col3,'')))", $ret);
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
         $this->assertEquals(
             $this->_db->fromConvert($parameters[0],$parameters[1]),
             $result);
    }

    /**
     * @group bug50024 - connect fails when not passed a db_name config option
     */
    public function testConnectWithNoDbName()
    {
        if ($GLOBALS['db']->dbType != 'mssql') {
            $this->markTestSkipped('The instance needs to be configured to use SQL Server');
        }

        // set up a connection w/o a db_name
        $configOptions = array(
            'db_host_name' => $GLOBALS['db']->connectOptions['db_host_name'],
            'db_host_instance' => $GLOBALS['db']->connectOptions['db_host_instance'],
            'db_user_name' => $GLOBALS['db']->connectOptions['db_user_name'],
            'db_password' => $GLOBALS['db']->connectOptions['db_password'],
        );

        $this->assertTrue($this->_db->connect($configOptions));
    }

    /**
     * Test sql for truncate table in SqlServer(s).
     */
    public function testTruncateTableSQL()
    {
        $sql = $this->_db->truncateTableSQL('TEST_TABLE');

        $this->assertEquals('TRUNCATE TABLE TEST_TABLE', $sql);
    }

    public function testSqlLikeString()
    {
        $str = '[[A-Z]';
        $likestr = $this->_db->sqlLikeString($str, '%', false);
        $this->assertEquals('[[][[]A-Z]', $likestr);
    }

    /**
     * Data provider for test of check union(s) in query.
     *
     * @return array
     */
    public function providerIsUnionQuery()
    {
        return array(
            // If UNION(s) in main query and sub queries not exists then this's union query.
            array(
                "
                    select
                        emails1.id id,
                        emails1.date_modified date_modified
                    from emails1
                    union
                    select
                        emails.id id,
                        emails.date_modified date_modified
                    from emails
                    where emails.deleted = 0
                    order by emails.date_modified desc
                ",
                true
            ),
            // If UNION(s) in sub queries and not exists in main query then this's not union query.
            array(
                "
                    select
                          emails.id id,
                          emails.date_modified date_modified,
                          emails.assigned_user_id assigned_user_id,
                          emails.created_by created_by
                    from emails
                    inner join (
                        select tst.team_set_id
                        from team_sets_teams tst
                        inner join team_memberships team_memberships on tst.team_id = team_memberships.team_id
                                and team_memberships.user_id = N'2e98b15e-89a9-b6c2-a8a1-53b42599bd14'
                                and team_memberships.deleted=0 group by tst.team_set_id
                    ) emails_tf on emails_tf.team_set_id  = emails.team_set_id
                    inner join (
                        select eb.email_id, N'direct' source
                        from emails_beans eb
                        where eb.bean_module = N'leads'
                            and eb.bean_id = N'c2c77a37-1732-96f0-1403-53b4253853cd' and eb.deleted=0
                        union
                        select distinct eear.email_id, N'relate' source
                        from emails_email_addr_rel eear
                        inner join email_addr_bean_rel eabr
                        on eabr.bean_id = N'c2c77a37-1732-96f0-1403-53b4253853cd' and eabr.bean_module = N'leads' and
                        eabr.email_address_id = eear.email_address_id and eabr.deleted=0
                        where eear.deleted=0
                    ) email_ids on emails.id=email_ids.email_id
                    where emails.deleted = 0
                    order by emails.date_modified desc
                ",
                false
            ),
            // If UNION(s) in sub queries and in main query then this's union query.
            array(
                "
                    select
                        emails1.id id,
                        emails1.date_modified date_modified
                    from emails1
                    union
                    select
                        emails.id id,
                        emails.date_modified date_modified
                    from emails
                    inner join (
                        select tst.team_set_id
                        from team_sets_teams tst
                        inner join team_memberships team_memberships on tst.team_id = team_memberships.team_id
                                and team_memberships.user_id = N'2e98b15e-89a9-b6c2-a8a1-53b42599bd14'
                                and team_memberships.deleted=0 group by tst.team_set_id
                    ) emails_tf on emails_tf.team_set_id  = emails.team_set_id
                    inner join (
                        select eb.email_id, N'direct' source
                        from emails_beans eb
                        where eb.bean_module = N'leads'
                            and eb.bean_id = N'c2c77a37-1732-96f0-1403-53b4253853cd' and eb.deleted=0
                        union
                        select distinct eear.email_id, N'relate' source
                        from emails_email_addr_rel eear
                        inner join email_addr_bean_rel eabr
                        on eabr.bean_id = N'c2c77a37-1732-96f0-1403-53b4253853cd' and eabr.bean_module = N'leads' and
                        eabr.email_address_id = eear.email_address_id and eabr.deleted=0
                        where eear.deleted=0
                    ) email_ids on emails.id=email_ids.email_id
                    where emails.deleted = 0
                    order by emails.date_modified desc
                ",
                true
            ),
            // Without union(s)
            array(
                "
                    select
                          emails.id id,
                          emails.date_modified date_modified,
                          emails.assigned_user_id assigned_user_id,
                          emails.created_by created_by
                    from emails
                    where emails.deleted = 0
                    order by emails.date_modified desc
                ",
                false
            ),
            // 'union' in literal string
            array(
                "SELECT id
                 FROM accounts
                 WHERE name = 'UNION TEST'
                 AND deleted != 1",
                false
            ),
            array(
                "SELECT id_c
                 FROM accounts_cstm
                 WHERE  union_c = '''UNION''s TEST'''
                 AND deleted != 1",
                false
            ),
            array(
                "(SELECT id
                 FROM accounts
                 WHERE name = 'UNION TEST'
                 AND deleted != 1)UNION(
                 SELECT id_c
                 FROM accounts_cstm
                 WHERE union_c = '''UNION''s TEST'''
                 AND deleted != 1)",
                true
            ),
        );
    }

    /**
     * test of check union(s) in query.
     *
     * @dataProvider providerIsUnionQuery
     *
     * @param string $sql
     * @param boolean $isUnionExpected
     */
    public function testIsUnionQuery($sql, $isUnionExpected)
    {
        $isUnion = SugarTestReflection::callProtectedMethod($this->_db, 'isUnionQuery', array($sql));

        $this->assertEquals($isUnionExpected, $isUnion);
    }

    /**
     * Data provider for testColumnLengthLimits()
     *
     * @return array
     */
    public function dataProviderColumnLengthLimits()
    {
        return array(
            // char with length less than 8000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '1024',
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
            // char with length greater than 8000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '9000',
                ),
                '/foo\s+$baseType\(8000\)/i',
            ),
            // varchar with length less than 8000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '1024',
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
            // varchar with length greater than 8000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '9000',
                ),
                '/foo\s+$baseType\(max\)/i',
            ),
            // varchar with length max
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => 'max',
                ),
                '/foo\s+$baseType\(max\)/i',
            ),
            // binary with length less than 8000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'binary',
                    'len' => '1024',
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
            // binary with length greater than 8000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'binary',
                    'len' => '9000',
                ),
                '/foo\s+$baseType\(8000\)/i',
            ),
            // varbinary with length less than 8000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varbinary',
                    'len' => '1024',
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
            // varbinary with length greater than 8000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varbinary',
                    'len' => '9000',
                ),
                '/foo\s+$baseType\(max\)/i',
            ),
            // varbinary with length max
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varbinary',
                    'len' => 'max',
                ),
                '/foo\s+$baseType\(max\)/i',
            ),
            // nchar with length less than 4000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'nchar',
                    'len' => '1024',
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
            // nchar with length greater than 4000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'nchar',
                    'len' => '9000',
                ),
                '/foo\s+$baseType\(4000\)/i',
            ),
            // nvarchar with length less than 4000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'nvarchar',
                    'len' => '1024',
                ),
                '/foo\s+$baseType\(1024\)/i',
            ),
            // nvarchar with length greater than 4000
            array(
                array(
                    'name' => 'foo',
                    'type' => 'nvarchar',
                    'len' => '9000',
                ),
                '/foo\s+$baseType\(max\)/i',
            ),
            // nvarchar with length max
            array(
                array(
                    'name' => 'foo',
                    'type' => 'nvarchar',
                    'len' => 'max',
                ),
                '/foo\s+$baseType\(max\)/i',
            ),
        );
    }

    /**
     * Test for check valid column type limits.
     *
     * @dataProvider dataProviderColumnLengthLimits
     */
    public function testColumnLengthLimits(array $fieldDef, $successRegex)
    {
        $colType = $this->_db->getColumnType($this->_db->getFieldType($fieldDef));
        if ($type = $this->_db->getTypeParts($colType)) {
            $successRegex = preg_replace('/\$baseType/', $type['baseType'], $successRegex);
        }

        $result = SugarTestReflection::callProtectedMethod($this->_db, 'oneColumnSQLRep', array($fieldDef));
        $this->assertEquals(1, preg_match($successRegex, $result), "Resulting statement: $result failed to match /$successRegex/");
    }

    /**
     * Test order_stability capability BR-2097
     */
    public function testOrderStability()
    {
        $msg = 'SQL Server adapter should not declare order_stability capability';
        $this->assertFalse($this->_db->supports('order_stability'), $msg);
    }
}
