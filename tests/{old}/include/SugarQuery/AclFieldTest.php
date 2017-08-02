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

class SugarQueryAclFieldTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var User */
    private static $otherUser;

    public static function setUpBeforeClass()
    {
        global $current_user;
        
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
        self::$otherUser = SugarTestUserUtilities::createAnonymousUser();

        ACLField::$acl_fields[$current_user->id]['Accounts'] = array(
            'name' => ACL_OWNER_READ_WRITE,

            // check that access level of the owner fields doesn't cause infinite recursion
            'assigned_user_id' => ACL_OWNER_READ_WRITE,
            'created_by' => ACL_OWNER_READ_WRITE,
        );
    }

    protected function setUp()
    {
        parent::setUp();
    }

    public static function tearDownAfterClass()
    {
        ACLField::$acl_fields = array();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testAssignedToCurrentUserIsAccessible()
    {
        global $current_user;
        $rows = $this->createAndFetchBean($current_user->id, self::$otherUser->id);
        $this->assertBeanAccessible($rows);
    }

    public function testUnassignedAndCreatedByCurrentUserIsAccessible()
    {
        global $current_user;
        $rows = $this->createAndFetchBean(null, $current_user->id);
        $this->assertBeanAccessible($rows);
    }

    public function testAssignedToOtherUserIsNotAccessible()
    {
        global $current_user;
        $rows = $this->createAndFetchBean(self::$otherUser->id, $current_user->id);
        $this->assertBeanNotAccessible($rows);
    }

    public function testCreatedByOtherUserIsNotAccessible()
    {
        $rows = $this->createAndFetchBean(null, self::$otherUser->id);
        $this->assertBeanNotAccessible($rows);
    }

    private function createAndFetchBean($assignedTo, $createdBy)
    {
        $account = SugarTestAccountUtilities::createAccount(null, array(
            'assigned_user_id' => $assignedTo,
            'created_by' => $createdBy,
            'set_created_by' => false,
        ));

        $query = new SugarQuery();
        $query->from($account);
        $where = $query->where();
        $where->equals('name', $account->name);
        $rows = $query->execute();

        return $rows;
    }

    private function assertBeanAccessible(array $rows)
    {
        $this->assertCount(1, $rows, 'Bean should be accessible to the current user');
    }

    private function assertBeanNotAccessible(array $rows)
    {
        $this->assertCount(0, $rows, 'Bean should not be accessible to the current user');
    }
}
