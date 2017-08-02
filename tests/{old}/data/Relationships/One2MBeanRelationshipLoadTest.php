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
require_once 'data/Relationships/One2MBeanRelationship.php';

/**
 * @coversDefaultClass One2MBeanRelationship
 */
class One2MBeanRelationshipLoadTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $account;
    private static $opportunity;
    private static $opportunity2;
    private static $opportunity3;

    public static function setUpBeforeClass()
    {
        self::$account = SugarTestAccountUtilities::createAccount();
        self::$opportunity = SugarTestOpportunityUtilities::createOpportunity();
        self::$opportunity->name = 'Opportunity1';
        self::$opportunity->save();
        self::$opportunity2 = SugarTestOpportunityUtilities::createOpportunity();
        self::$opportunity2->name = 'Opportunity2';
        self::$opportunity2->save();
        self::$opportunity3 = SugarTestOpportunityUtilities::createOpportunity();

        self::$account->load_relationship('opportunities');
        self::$account->opportunities->add(self::$opportunity);
        self::$account->opportunities->add(self::$opportunity2);
        self::$account->opportunities->add(self::$opportunity3);
        self::$opportunity3->mark_deleted(self::$opportunity3->id);
    }

    public static function tearDownAfterClass()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::load
     */
    public function testLoad()
    {
        $opportunities = self::$account->opportunities->query([]);
        $this->assertCount(2, $opportunities['rows']);
    }

    public function whereDataProvider()
    {
        return [
            [1, 'Opportunity1'],
            [1, 'Opportunity2'],
            [0, 'Opportunity3'],
        ];
    }

    /**
     * @dataProvider whereDataProvider
     * @covers ::load
     */
    public function testWhere($expectedCount, $value)
    {
        $opportunities = self::$account->opportunities->query(
            ['where' => ['lhs_field' => 'name', 'operator' => '=', 'rhs_value' => $value]]
        );
        $this->assertCount($expectedCount, $opportunities['rows']);
    }

    /**
     * @covers ::load
     */
    public function testOrder()
    {
        $opportunities = self::$account->opportunities->query(
            ['where' => ['lhs_field' => 'name', 'operator' => '=', 'rhs_value' => 'Opportunity1']]
        );
        $relId1 = array_pop(array_keys($opportunities['rows']));
        $opportunities = self::$account->opportunities->query(
            ['where' => ['lhs_field' => 'name', 'operator' => '=', 'rhs_value' => 'Opportunity2']]
        );
        $relId2 = array_pop(array_keys($opportunities['rows']));

        $opportunities = self::$account->opportunities->query(['orderby' => 'name DESC']);
        $expected = [
            $relId2 => ['id' => $relId2, 'opportunities__name' => 'Opportunity2'],
            $relId1 => ['id' => $relId1, 'opportunities__name' => 'Opportunity1'],
        ];
        $this->assertEquals($expected, $opportunities['rows']);
        $this->assertEquals(array_keys($expected), array_keys($opportunities['rows']));
    }

    /**
     * @covers ::load
     */
    public function testLimit()
    {
        $opportunities = self::$account->opportunities->query(
            ['where' => ['lhs_field' => 'name', 'operator' => '=', 'rhs_value' => 'Opportunity2']]
        );
        $relId2 = array_pop(array_keys($opportunities['rows']));

        $opportunities = self::$account->opportunities->query(
            ['limit' => 1, 'offset' => 1, 'orderby' => 'name ASC']
        );
        $this->assertArraySubset(
            [$relId2 => ['id' => $relId2, 'opportunities__name' => 'Opportunity2']],
            $opportunities['rows']
        );
    }

    /**
     * @covers ::load
     */
    public function testNoLimit()
    {
        $opportunities = self::$account->opportunities->query(['limit' => -1, 'offset' => 0]);
        $this->assertCount(2, $opportunities['rows'], 'Limit = -1 means that no limit is set');
    }

    /**
     * @covers ::load
     * @expectedException \Exception
     */
    public function testOffsetException()
    {
        self::$account->opportunities->query(['limit' => 1, 'offset' => -1]);
    }

    public function deletedDataProvider()
    {
        return [
            [2, 0],
            [1, 1],
        ];
    }

    /**
     * @dataProvider deletedDataProvider
     * @covers ::load
     */
    public function testDeleted($expectedCount, $deleted)
    {
        $opportunities = self::$account->opportunities->query(['deleted' => $deleted]);
        $this->assertCount($expectedCount, $opportunities['rows']);
    }
}
