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


class ReportsUtilitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        $GLOBALS["current_user"] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS["current_user"]);
    }

    /**
     * @group reports
     * @group email
     * @group mailer
     */
    public function testSendNotificationOfInvalidReport_InvalidRecipientAddress_ThrowsMailerException() {
        $recipient = new User();
        $recipient->email1 = null;
        $recipient->email2 = null;

        self::setExpectedException("MailerException");
        $reportsUtilities = new ReportsUtilities();
        $reportsUtilities->sendNotificationOfInvalidReport($recipient, "asdf");
    }

    /**
     * @group reports
     * @group email
     * @group mailer
     */
    public function testSendNotificationOfInvalidReport_AllMethodCallsAreSuccessful_NoExceptionsThrown() {
        self::markTestIncomplete(
            "Not yet implemented; requires mocking MailerFactory to return a mocked Mailer with a stub for send"
        );

        $recipient = new User();
        $recipient->email1 = null;
        $recipient->email2 = "foo@bar.com";

        $reportsUtilities = new ReportsUtilities();
        $reportsUtilities->sendNotificationOfInvalidReport($recipient, "asdf");
    }
}
