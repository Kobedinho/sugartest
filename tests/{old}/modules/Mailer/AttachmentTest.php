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


class AttachmentTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group email
     * @group mailer
     */
    public function testFromSugarBean_BeanIsAccount_ThrowsException() {
        $mockAccount = self::getMockBuilder("Account")->setMethods(array("Account"))->getMock();

        $mockAccount->expects(self::any())
            ->method("Account")
            ->will(self::returnValue(true));

        self::setExpectedException("MailerException");
        $actual = AttachmentPeer::attachmentFromSugarBean($mockAccount);
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFromSugarBean_BeanIsNote_ThrowsException() {
        // needs to be able to mock out is_file() and the properities id, filename/name, and file_mime_type
        self::markTestIncomplete("This test has not been implemented yet.");
    }
}
