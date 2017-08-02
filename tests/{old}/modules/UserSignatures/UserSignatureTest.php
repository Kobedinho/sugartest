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

class UserSignatureTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $createdSignatures = array();

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        if (!empty(static::$createdSignatures)) {
            $ids = implode("','", static::$createdSignatures);
            $GLOBALS['db']->query("DELETE FROM users_signatures WHERE id IN ('{$ids}')");
        }
        parent::tearDownAfterClass();
    }

    public function testSave_UserIdIsEmpty_CurrentUserIdIsUsed()
    {
        $signature = BeanFactory::newBean('UserSignatures');
        static::$createdSignatures[] = $signature->save();
        $this->assertEquals(
            $GLOBALS['current_user']->id,
            $signature->user_id,
            "user_id should match the current user's ID"
        );
        $this->assertEquals(
            $GLOBALS['current_user']->id,
            $signature->created_by,
            'Should have been created by the current user'
        );
    }

    public function testSave_UserIdIsNotEmptyAndCreatedByIsEmpty_UserIdIsUsedForCreatedBy()
    {
        $expected = create_guid();
        $signature = BeanFactory::newBean('UserSignatures');
        $signature->user_id = $expected;
        static::$createdSignatures[] = $signature->save();
        $this->assertEquals($expected, $signature->user_id, "user_id should not have changed");
        $this->assertEquals($expected, $signature->created_by, 'Should match user_id');
    }

    public function testSave_CreatedByDoesNotMatchUserId_UserIdIsUsedForCreatedBy()
    {
        $expected = create_guid();
        $signature = BeanFactory::newBean('UserSignatures');
        $signature->user_id = $expected;
        $signature->created_by = create_guid();
        static::$createdSignatures[] = $signature->save();
        $this->assertEquals($expected, $signature->created_by, 'Should match user_id');
    }
}

