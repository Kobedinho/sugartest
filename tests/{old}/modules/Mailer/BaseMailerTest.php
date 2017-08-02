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
 * @group mailer
 */
class BaseMailerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testSetMessageId()
    {
        $id = create_guid();
        $hostname = 'mycompany.com';

        $config = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $config->setHostname($hostname);

        $mailer = $this->getMockBuilder('BaseMailer')
            ->setConstructorArgs(array($config))
            ->getMockForAbstractClass();
        $mailer->setMessageId($id);

        $actual = $mailer->getHeader(EmailHeaders::MessageId);
        $this->assertRegExp('/\<\d+\.' . $id . '@' . $hostname . '\>/', $actual);
    }
}
