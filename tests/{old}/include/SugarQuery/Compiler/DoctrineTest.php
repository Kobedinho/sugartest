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

require_once 'include/SugarQuery/Compiler/Doctrine.php';

/**
 * @coversDefaultClass SugarQuery_Compiler_Doctrine
 */
class SugarQuery_Compiler_DoctrineTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Account
     */
    private $account;

    protected function setUp()
    {
        parent::setUp();

        $this->account = BeanFactory::newBean('Accounts');
    }

    public function testSelectFieldsByDefault()
    {
        $query = $this->getQuery();
        $builder = $query->compile();

        $select = $builder->getQueryPart('select');
        $this->assertNotEmpty($select);
    }

    public function testSelect()
    {
        $query = new SugarQuery();
        $query->select(array(
            array('name', 'account_name'),
            'parent_name',
        ));
        $query->from($this->account);
        $builder = $query->compile();

        $select = $builder->getQueryPart('select');

        $this->assertContains('accounts.name account_name', $select);
        $this->assertNotContains('accounts.parent_name', $select);
    }

    public function testCountQuery()
    {
        $query = new SugarQuery();
        $query->select('name')->setCountQuery();
        $query->from($this->account);
        $builder = $query->compile();

        $select = $builder->getQueryPart('select');

        $lastColumn = array_pop($select);
        $this->assertStringStartsWith('COUNT(', $lastColumn);

        $groupBy = $builder->getQueryPart('groupBy');
        $this->assertContains('accounts.name', $groupBy);
    }

    public function testDistinctQuery()
    {
        $query = new SugarQuery();
        $query->distinct(true)->select('name');
        $query->from($this->account);
        $builder = $query->compile();

        $select = $builder->getQueryPart('select');
        $this->assertContains('DISTINCT accounts.name', $select);
    }

    /**
     * @param array $options FROM options
     * @param array $expected Expected FROM part
     *
     * @dataProvider compileFromProvider
     */
    public function testCompileFrom(array $options, array $expected)
    {
        $query = $this->getQuery($options);
        $builder = $query->compile();

        $this->assertContains($expected, $builder->getQueryPart('from'));
    }

    /**
     * @return array
     */
    public static function compileFromProvider()
    {
        return array(
            'without-alias' => array(
                array(),
                array(
                    'table' => 'accounts',
                    'alias' => null,
                ),
            ),
            'with-alias' => array(
                array(
                    'alias' => 'a',
                ),
                array(
                    'table' => 'accounts',
                    'alias' => 'a',
                ),
            ),
        );
    }

    /**
     * @expectedException SugarQueryException
     */
    public function testCompileFromNoBean()
    {
        $query = new SugarQuery();
        $query->compile();
    }

    public function testCompileJoin()
    {
        $query = $this->getQuery();
        $query->joinTable('opportunities', array(
            'joinType' => 'left',
        ))->on()->equalsField('opportunities.account_id', 'accounts.id');
        $builder = $query->compile();

        $this->assertArraySubset(array(
            'accounts' => array(
                array(
                    'joinType' => 'left',
                    'joinTable' => 'opportunities',
                    'joinAlias' => 'opportunities',
                    'joinCondition' => 'opportunities.account_id = accounts.id',
                ),
            ),
        ), $builder->getQueryPart('join'));
    }

    public function testCompileJoinSubQuery()
    {
        $subQuery = new SugarQuery();

        /** @var SugarQuery_Compiler_Doctrine|PHPUnit_Framework_MockObject_MockObject $compiler */
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_Doctrine')
            ->setMethods(array('compileSubQuery'))
            ->setConstructorArgs(array($this->account->db))
            ->getMock();
        $compiler->expects($this->once())
            ->method('compileSubQuery')
            ->with($this->anything(), $subQuery)
            ->willReturn('SELECT 1 FROM DUAL');

        $query = new SugarQuery();
        $query->from($this->account, array(
            'team_security' => false,
        ));
        $query->joinTable($subQuery, array(
            'alias' => 'q',
        ));
        $builder = $compiler->compile($query);

        $this->assertArraySubset(array(
            'accounts' => array(
                array(
                    'joinType' => 'inner',
                    'joinTable' => '(SELECT 1 FROM DUAL)',
                    'joinAlias' => 'q',
                    'joinCondition' => null,
                ),
            ),
        ), $builder->getQueryPart('join'));
    }

    /**
     * @param array $options FROM options
     * @param string $expectedWhere Expected WHERE expression
     * @param array $expectedParams Expected statement parameters
     * @param array $expectedTypes Expected types
     * @dataProvider compileWhereProvider
     */
    public function testCompileWhere($options, $expectedWhere, $expectedParams, $expectedTypes)
    {
        $query = new SugarQuery();
        $query->from($this->account, $options);
        $query->where()
            ->equals('industry', 'Apparel');
        $builder = $query->compile();

        $this->assertEquals($expectedWhere, $builder->getQueryPart('where'));
        $this->assertSame($expectedParams, $builder->getParameters());
        $this->assertSame($expectedTypes, $builder->getParameterTypes());
    }

    /**
     * @return array
     */
    public static function compileWhereProvider()
    {
        return array(
            'consider-deleted-flag' => array(
                array(),
                // we don't enforce parentheses around simple expressions, but Doctrine CompositeExpression adds them
                '(accounts.industry = ?) AND (accounts.deleted = ?)',
                array(
                    1 => 'Apparel',
                    2 => 0,
                ),
                array(
                    1 => PDO::PARAM_STR,
                    2 => PDO::PARAM_BOOL,
                ),
            ),
            'ignore-deleted-flag' => array(
                array(
                    'add_deleted' => false,
                ),
                'accounts.industry = ?',
                array(
                    1 => 'Apparel',
                ),
                array(
                    1 => PDO::PARAM_STR,
                ),
            ),
        );
    }

    public function testCompileHaving()
    {
        $query = new SugarQuery();
        $query->from($this->account);
        $query->havingRaw('COUNT(id) > 3');
        $builder = $query->compile();

        $this->assertEquals('COUNT(id) > 3', $builder->getQueryPart('having'));
    }

    /**
     * @param array $orderBy ORDER BY columns from query
     * @param array $expected Expected ORDER BY part
     * @dataProvider compileOrderByProvider
     */
    public function testCompileOrderBy(array $orderBy, array $expected)
    {
        $query = new SugarQuery();
        $query->from($this->account);

        foreach ($orderBy as $column) {
            call_user_func_array(array($query, 'orderBy'), $column);
        }

        $builder = $query->compile();

        // this is not us enforcing the DESC order by default, this is how Sugar works now
        $this->assertArraySubset($expected, $builder->getQueryPart('orderBy'));
    }

    /**
     * @return array
     */
    public static function compileOrderByProvider()
    {
        return array(
            'id-is-added' => array(
                array(
                    array('name', 'DESC'),
                ),
                array(
                    'accounts.name DESC',
                    'accounts.id DESC',
                ),
            ),
            'id-is-not-duplicated' => array(
                array(
                    array('id', 'DESC'),
                ),
                array(
                    'accounts.id DESC',
                ),
            ),
            'direction-is-preserved' => array(
                array(
                    array('name', 'ASC'),
                ),
                array(
                    'accounts.name ASC',
                    'accounts.id ASC',
                ),
            ),
            'empty-order-is-preserved' => array(
                array(),
                array(),
            ),
            'non-db-columns-are-ignored' => array(
                array(
                    array('members'),
                ),
                array(),
            ),
        );
    }

    /**
     * @param callable $where
     * @dataProvider compileConditionProvider
     */
    public function testCompileCondition(callable $where, $expectedWhere, $expectedParams = array())
    {
        $query = $this->getQuery();
        $where($query->where());

        $compiler = $this->getCompilerWithCollationCaseSensitivity(false);
        $builder = $compiler->compile($query);

        $this->assertEquals($expectedWhere, $builder->getQueryPart('where'));
        $this->assertSame($expectedParams, $builder->getParameters());
    }

    /**
     * @return array
     */
    public static function compileConditionProvider()
    {
        return array(
            'is-null' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->isNull('industry');
                },
                'accounts.industry IS NULL',
            ),
            'is-not-null' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->notNull('industry');
                },
                'accounts.industry IS NOT NULL',
            ),
            'in' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->in('industry', array('Apparel', 'Banking'));
                },
                'accounts.industry IN (?,?)',
                array(
                    1 => 'Apparel',
                    2 => 'Banking',
                ),
            ),
            'in-empty-set' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->in('industry', array());
                },
                'accounts.industry IN (NULL)',
            ),
            'in-sub-query' => array(
                function (SugarQuery_Builder_Where $where) {
                    $subQuery = new SugarQuery();
                    $subQuery->from(BeanFactory::newBean('Accounts'), array(
                        'add_deleted' => false,
                        'team_security' => false,
                    ));
                    $subQuery->select('id');
                    $subQuery->where()
                        ->equals('industry', 'Apparel');
                    $where->in('id', $subQuery);
                },
                'accounts.id IN (SELECT accounts.id FROM accounts WHERE accounts.industry = ?)',
                array(
                    1 => 'Apparel',
                ),
            ),
            'not-in' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->notIn('industry', array('Retail', 'Shipping'));
                },
                'accounts.industry IS NULL OR accounts.industry NOT IN (?,?)',
                array(
                    1 => 'Retail',
                    2 => 'Shipping',
                ),
            ),
            'equal-field' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->equalsField('industry', 'account_type');
                },
                'accounts.industry = accounts.account_type',
            ),
            'not-equal-field' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->notEqualsField('industry', 'account_type');
                },
                'accounts.industry != accounts.account_type',
            ),
            'compare-field' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->gt('industry', array('$field' => 'account_type'));
                },
                'accounts.industry > accounts.account_type',
            ),
            'between' => array(
                function (SugarQuery_Builder_Where $where) {
                    // it's a bad example, but Accounts doesn't have numeric fields,
                    // while the SQL for DATE fields will depend on the current DB platform
                    $where->between('rating', 'good', 'bad');
                },
                'accounts.rating BETWEEN ? AND ?',
                array(
                    1 => 'good',
                    2 => 'bad',
                ),
            ),
            'starts-with' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->starts('name', 'A');
                },
                'accounts.name LIKE ?',
                array(
                    1 => 'A%',
                ),
            ),
            /* temporarily disable this for BR-4919
            'ends-with-escaping' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->ends('name', '%_!');
                },
                'accounts.name LIKE ? ESCAPE \'!\'',
                array(
                    1 => '%!%!_!!',
                ),
            ),
            */
            'does-not-contain-array' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->notContains('name', array('X', 'Y'));
                },
                'accounts.name IS NULL OR (accounts.name NOT LIKE ? AND accounts.name NOT LIKE ?)',
                array(
                    1 => '%X%',
                    2 => '%Y%',
                ),
            ),
            'like' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->like('name', '%X%Y%Z%');
                },
                'accounts.name LIKE ?',
                array(
                    1 => '%X%Y%Z%',
                ),
            ),
        );
    }

    public function testCompileConditionCaseSensitive()
    {
        $query = $this->getQuery();
        $query->where()
            ->notContains('name', array('x', 'y'));

        $compiler = $this->getCompilerWithCollationCaseSensitivity(true);
        $builder = $compiler->compile($query);

        $this->assertEquals(
            'accounts.name IS NULL OR (UPPER(accounts.name) NOT LIKE ? AND UPPER(accounts.name) NOT LIKE ?)',
            $builder->getQueryPart('where')
        );
        $this->assertSame(array(
            1 => '%X%',
            2 => '%Y%',
        ), $builder->getParameters());
    }

    private function getQuery(array $options = array())
    {
        $query = new SugarQuery();
        $query->from($this->account, array_merge(array(
            'add_deleted' => false,
            'team_security' => false,
        ), $options));

        return $query;
    }

    /**
     * Returns compiled with mocked case sensitivity of the underlying database collation
     *
     * @param boolean $value Whether the locale is case sensitive
     * @return PHPUnit_Framework_MockObject_MockObject|SugarQuery_Compiler_Doctrine
     */
    private function getCompilerWithCollationCaseSensitivity($value)
    {
        /** @var SugarQuery_Compiler_Doctrine|PHPUnit_Framework_MockObject_MockObject $compiler */
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_Doctrine')
            ->setMethods(array('isCollationCaseSensitive'))
            ->setConstructorArgs(array($this->account->db))
            ->getMock();
        $compiler->expects($this->any())
            ->method('isCollationCaseSensitive')
            ->willReturn($value);

        return $compiler;
    }

    public function testCompileCompareWithSubQuery()
    {
        $subQuery = $this->getQuery();
        $subQuery->select('industry');
        $subQuery->where()
            ->equals('id', 'ACCOUNT_ID');

        $query = $this->getQuery(array(
            'add_deleted' => true,
        ));
        $query->select('name');
        $query->where()
            ->equals('industry', $subQuery);

        $builder = $query->compile();

        $this->assertEquals(
            'SELECT accounts.name FROM accounts WHERE (accounts.industry = ('
            . 'SELECT accounts.industry FROM accounts WHERE accounts.id = ?'
            . ')) AND (accounts.deleted = ?)',
            $builder->getSQL()
        );
        $this->assertSame(array(
            1 => 'ACCOUNT_ID',
            2 => 0,
        ), $builder->getParameters());
        $this->assertSame(array(
            1 => PDO::PARAM_STR,
            2 => PDO::PARAM_BOOL,
        ), $builder->getParameterTypes());
    }

    public function testCompileUnion()
    {
        $query1 = $this->getQuery();
        $query1->select('name');
        $query1->where()
            ->equals('industry', 'Apparel');

        $query2 = $this->getQuery();
        $query2->select('name');
        $query2->where()
            ->equals('account_type', 'Analyst');

        $query = new SugarQuery();
        $query->union($query1);
        $query->union($query2);
        $query->orderBy('name', 'ASC');

        $builder = $query->compile();

        $this->assertEquals(
            'SELECT accounts.name FROM accounts WHERE accounts.industry = ?'
            . ' UNION ALL'
            . ' SELECT accounts.name FROM accounts WHERE accounts.account_type = ?'
            . ' ORDER BY name ASC',
            $builder->getSQL()
        );
        $this->assertSame(array(
            1 => 'Apparel',
            2 => 'Analyst',
        ), $builder->getParameters());
        $this->assertSame(array(
            1 => PDO::PARAM_STR,
            2 => PDO::PARAM_STR,
        ), $builder->getParameterTypes());
    }
}
