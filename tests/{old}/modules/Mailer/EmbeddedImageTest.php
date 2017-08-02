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


class EmbeddedImageTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group email
     * @group mailer
     */
    public function testFromSugarBean_ThrowsException() {
        $mockNote = self::getMockBuilder("Note")->setMethods(array("Note"))->getMock();

        $mockNote->expects(self::any())
            ->method("Note")
            ->will(self::returnValue(true));

        self::setExpectedException("MailerException");
        $actual = AttachmentPeer::embeddedImageFromSugarBean($mockNote, '1234567890');
    }

    /**
     * @group email
     * @group mailer
     */
    public function testToArray() {
        $expected      = array(
            "cid"  => "1234",
            "path" => "path/to/somewhere",
            "name" => "abcd",
        );
        $embeddedImage = new EmbeddedImage($expected["cid"], $expected["path"], $expected["name"]);
        $actual        = $embeddedImage->toArray();

        $key = "path";
        self::assertArrayHasKey($key, $actual, "The '{$key}' key should have been added");
        self::assertEquals($expected["path"], $actual["path"], "The paths don't match");

        $key = "cid";
        self::assertArrayHasKey($key, $actual, "The '{$key}' key should have been added");
        self::assertEquals($expected["cid"], $actual["cid"], "The CIDs don't match");

        $key = "name";
        self::assertArrayHasKey($key, $actual, "The '{$key}' key should have been added");
        self::assertEquals($expected["name"], $actual["name"], "The names don't match");
    }
}
