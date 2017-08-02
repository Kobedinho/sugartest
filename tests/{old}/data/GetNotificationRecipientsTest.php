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

/**
 * Test if we respect user preference for email notifications
 */
class GetNotificationRecipientsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if receive_notifications flag is respected for users
     *
     * @param $receiveNotification - Receive notification user preference
     * @param $expectedNumberOfRecords - Number of records returned
     * @dataProvider dataProvider
     */
    public function testGetNotificationRecipients($receiveNotification, $expectedNumberOfRecords)
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->receive_notifications = $receiveNotification;
        $user->save();

        $account = SugarTestAccountUtilities::createAccount();
        $account->assigned_user_id = $user->id;
        $account->save();

        $userList = $account->get_notification_recipients();
        $this->assertEquals($expectedNumberOfRecords, count($userList));
    }

    public static function dataProvider()
    {
        return array(
            array(
                false,
                0
            ),
            array(
                true,
                1
            ),
        );
    }
}
