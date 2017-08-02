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
 * @group email
 * @group outboundemailconfiguration
 */
class OutboundEmailConfigurationTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testLoadDefaultConfigs_CharsetIsReset_WordwrapIsInitialized()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);

        // change the default charset in order to show that loadDefaultConfigs will reset it
        $configuration->setCharset("asdf"); // some asinine value that wouldn't actually be used

        // test that the charset has been changed from its default
        $expected = "asdf";
        $actual   = $configuration->getCharset();
        self::assertEquals($expected, $actual, "The charset should have been set to {$expected}");

        $configuration->loadDefaultConfigs();

        // test that the charset has been returned to its default
        $expected = "utf-8";
        $actual   = $configuration->getCharset();
        self::assertEquals($expected, $actual, "The charset should have been reset to {$expected}");

        // test that the wordwrap has been initialized correctly
        $expected = 996;
        $actual   = $configuration->getWordwrap();
        self::assertEquals($expected, $actual, "The wordwrap should have been initialized to {$expected}");
    }

    public function testSetEncoding_PassInAValidEncoding_EncodingIsSet()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $expected      = Encoding::EightBit;

        $configuration->setEncoding($expected);
        $actual = $configuration->getEncoding();
        self::assertEquals($expected, $actual, "The encoding should have been set to {$expected}");
    }

    public function testSetEncoding_PassInAnInvalidEncoding_ThrowsException()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $encoding      = "asdf"; // some asinine value that wouldn't actually be used

        self::setExpectedException("MailerException");
        $configuration->setEncoding($encoding);
    }

    public function testSetMode_ModeIsInvalid_ThrowsException()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $invalidMode   = "asdf"; // some asinine value that wouldn't actually be used

        self::setExpectedException("MailerException");
        $configuration->setMode($invalidMode); // hopefully nothing is actually returned
    }

    public function testSetMode_NoMode_ModeBecomesDefault()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $configuration->setMode("");

        $expected = OutboundEmailConfigurationPeer::MODE_DEFAULT;
        $actual   = $configuration->getMode();
        self::assertEquals($expected, $actual, "The mode should have been a {$expected}");
    }

    public function testSetFrom_EmailIsInvalid_ThrowsMailerException()
    {
        self::setExpectedException("MailerException");
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $configuration->setFrom(1234); // an invalid email address
    }
}
