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
 * @ticket 43643
 */
class Bug43643Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $current_user, $beanList, $beanFiles;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        require('include/modules.php');
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }


    /**
     * Test EmailAddress::testReplyToAddress() method.
     */
    public function testReplyToAddress()
    {
        global $current_user;
        $current_user->load_relationship('email_addresses');

        $non_reply_to_value = 'non-reply-to@example.com';
        $non_reply_to_address = SugarTestEmailAddressUtilities::addAddressToPerson(
            $current_user,
            $non_reply_to_value
        );

        $email_address = new EmailAddress();

        // ensure that the only email address value is returned
        $non_reply_to_result = $email_address->getReplyToAddress($current_user);
        $this->assertEquals($non_reply_to_value, $non_reply_to_result);

        // ensure that empty string is returned when there is no reply-to
        // address exists
        $reply_to_only_result1 = $email_address->getReplyToAddress($current_user, true);
        $this->assertEquals('', $reply_to_only_result1);

        // create reply-to address
        $reply_to_value = 'some-address-2@example.com';
        $reply_to_address = SugarTestEmailAddressUtilities::addAddressToPerson(
            $current_user,
            $reply_to_value,
            array(
                'reply_to_address' => true,
            )
        );

        // ensure that reply-to address is returned
        $reply_to_only_result2 = $email_address->getReplyToAddress($current_user, true);
        $this->assertEquals($reply_to_value, $reply_to_only_result2);

        // clean everything up
        $non_reply_to_address->mark_deleted($non_reply_to_address->id);
        $reply_to_address->mark_deleted($reply_to_address->id);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}
