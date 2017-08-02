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


require_once 'data/visibility/SupportPortalVisibility.php';

/**
 * @coversDefaultClass \SupportPortalVisibility
 */
class SupportPortalVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $accounts = array();

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $_SESSION['type'] = 'support_portal';
        self::setUpAccounts();
    }

    protected function tearDown()
    {
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestNoteUtilities::removeAllCreatedNotes();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        unset($_SESSION['type']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * @dataProvider addVisibilityFromAccountsBasedRuleDataProvider
     * @covers ::addVisibilityFrom
     */
    public function testAccountBasedSql($module, $accountsIndexes, $expected)
    {
        $bean = $this->setUpAccountBasedFixture($module);
        $accountsIds = array();
        foreach ($accountsIndexes as $index) {
            $accountsIds[] = self::$accounts[$index]->id;
        }
        $visibility = $this->getVisibility($bean, $accountsIds);
        $rows = $this->selectSql($bean, $visibility);
        $this->assertEquals($expected ? array(array('id' => $bean->id)) : array(), $rows);
    }

    /**
     * @dataProvider addVisibilityFromAccountsBasedRuleDataProvider
     * @covers ::addVisibilityFromQuery
     */
    public function testAccountBasedSugarQuery($module, $accountsIndexes, $expected)
    {
        $bean = $this->setUpAccountBasedFixture($module);
        $accountsIds = array();
        foreach ($accountsIndexes as $index) {
            $accountsIds[] = self::$accounts[$index]->id;
        }
        $visibility = $this->getVisibility($bean, $accountsIds);
        $rows = $this->selectSugarQuery($bean, $visibility);
        $this->assertEquals($expected ? array(array('id' => $bean->id)) : array(), $rows);
    }

    private function setUpAccountBasedFixture($module)
    {
        $testUtilities = "SugarTest{$module}Utilities";
        $testUtilitiesMethod = "create$module";
        $bean = $testUtilities::$testUtilitiesMethod(null, array('portal_viewable' => 1));
        $bean->load_relationship('accounts');
        $bean->accounts->add(self::$accounts[0]);

        return $bean;
    }

    private static function setUpAccounts()
    {
        if (!count(self::$accounts)) {
            self::$accounts[] = SugarTestAccountUtilities::createAccount(null, array('name' => 'account-1'));
            self::$accounts[] = SugarTestAccountUtilities::createAccount(null, array('name' => 'account-2'));
            self::$accounts[] = SugarTestAccountUtilities::createAccount(null, array('name' => 'account-3'));
        }
    }

    public static function addVisibilityFromAccountsBasedRuleDataProvider()
    {
        return array(
            array('Contact', array(0, 1), true),
            array('Contact', array(2), false),
            array('Case', array(0, 1), true),
            array('Case', array(2), false),
            // Leads aren't available in portal
            array('Lead', array(0, 1), false),
        );
    }

    /**
     * @covers ::addVisibilityFrom
     */
    public function testNotesSql()
    {
        $bean = $this->setUpNotesFixture();
        $visibility = $this->getVisibility($bean, array(self::$accounts[0]->id, self::$accounts[1]->id));
        $rows = $this->selectSql($bean, $visibility);
        $this->assertEquals(array(array('id' => $bean->id)), $rows);
    }

    /**
     * @covers ::addVisibilityFromQuery
     */
    public function testNotesSugarQuery()
    {
        $bean = $this->setUpNotesFixture();
        $visibility = $this->getVisibility($bean, array(self::$accounts[0]->id, self::$accounts[1]->id));
        $rows = $this->selectSugarQuery($bean, $visibility);
        $this->assertEquals(array(array('id' => $bean->id)), $rows);
    }

    private function setUpNotesFixture()
    {
        $case = SugarTestCaseUtilities::createCase(null, array('portal_viewable' => 1));
        $case->load_relationship('accounts');
        $case->accounts->add(self::$accounts[0]);
        $bean = SugarTestNoteUtilities::createNote(null, array('portal_viewable' => 1, 'portal_flag' => 1));
        $bean->load_relationship('cases');
        $bean->cases->add($case);

        return $bean;
    }

    /**
     * @param SugarBean $bean
     * @param array $accountIds
     * @return SupportPortalVisibility|PHPUnit_Framework_MockObject_MockObject
     */
    private function getVisibility(SugarBean $bean, array $accountIds)
    {
        $visibility = $this->getMockBuilder('SupportPortalVisibility')
            ->setConstructorArgs(array($bean))
            ->setMethods(array('getAccountIds'))
            ->getMock();
        $visibility->expects($this->any())
            ->method('getAccountIds')
            ->will($this->returnValue($accountIds));

        return $visibility;
    }

    /**
     * Selects the data using SQL implementation
     *
     * @param SugarBean $bean
     * @param SupportPortalVisibility $visibility
     * @return array
     */
    private function selectSql(SugarBean $bean, SupportPortalVisibility $visibility)
    {
        $query = 'SELECT ' . $bean->table_name . '.id FROM ' . $bean->table_name;
        $visibility->addVisibilityFrom($query);
        $query .= ' WHERE ' . $bean->table_name . '.deleted = 0';
        $visibility->addVisibilityWhere($query);

        $result = $bean->db->query($query);
        $rows = array();
        while ($row = $bean->db->fetchRow($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Selects the data using SugarQuery implementation
     *
     * @param SugarBean $bean
     * @param SupportPortalVisibility $visibility
     * @return array
     */
    private function selectSugarQuery(SugarBean $bean, SupportPortalVisibility $visibility)
    {
        $query = new SugarQuery();
        $query->select('id');
        $query->from($bean);
        $visibility->addVisibilityFromQuery($query);
        $visibility->addVisibilityWhereQuery($query);
        return $query->execute();
    }
}
