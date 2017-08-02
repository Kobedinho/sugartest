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

namespace Sugarcrm\SugarcrmTests\Dbal;

use Doctrine\DBAL\Schema\Table;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * Covers our modifications to the DBAL connection class hierarchy
 */
class StatementTest extends \Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $table = new Table('type_conversion');
        $table->addColumn('id', 'string', array(
            'length' => 36,
        ));
        $table->addColumn('name', 'string');
        $table->addColumn('deptno', 'integer', array(
            'notnull' => false,
        ));

        $conn = \DBManagerFactory::getConnection();
        $sm = $conn->getSchemaManager();
        $sm->createTable($table);
    }

    public static function tearDownAfterClass()
    {
        $conn = \DBManagerFactory::getConnection();
        $sm = $conn->getSchemaManager();
        $sm->dropTable('type_conversion');

        parent::tearDownAfterClass();
    }

    public function testStringAsInteger()
    {
        $conn = \DBManagerFactory::getConnection();

        $conn->insert('type_conversion', array(
            'id' => Uuid::uuid1(),
            'name' => 'Alice',
        ));
        $conn->insert('type_conversion', array(
            'id' => 1,
            'name' => 'Bob',
        ));

        $stmt = $conn->executeQuery('SELECT name FROM type_conversion WHERE id = ?', array(1));

        $this->assertEquals(array('Bob'), $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function testIntegerAsString()
    {
        $conn = \DBManagerFactory::getConnection();

        $conn->insert('type_conversion', array(
            'id' => Uuid::uuid1(),
            'name' => 'Alice',
            'deptno' => '7',
        ));
        $conn->insert('type_conversion', array(
            'id' => Uuid::uuid1(),
            'name' => 'Bob',
            'deptno' => 12,
        ));

        $stmt = $conn->executeQuery('SELECT name FROM type_conversion WHERE deptno = ?', array('7'));

        $this->assertEquals(array('Alice'), $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }
}
