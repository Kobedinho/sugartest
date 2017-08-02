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

require_once "tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php";

/**
 * @group email
 */
class MailRecordTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $mailRecord,
            $mockEmail;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");

        $this->mailRecord          = new MailRecord();
        $this->mailRecord->subject = "MailRecord subject";

        $this->mockEmail = $this->createPartialMock("Email", array("email2Send"));
    }

    public function tearDown()
    {
        $_REQUEST = array();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testAddRecipients_ParameterIsNotAnArray_ReturnsABlankString()
    {
        $mailRecord = new MailRecordCaller();

        $expected = "";
        $actual   = $mailRecord->addRecipientsCaller("not an array");
        $this->assertEquals($expected, $actual, "No recipients should have been added.");
    }

    public function testAddRecipients_ParameterIsAnArrayButArrayIsEmpty_ReturnsABlankString()
    {
        $mailRecord = new MailRecordCaller();

        $expected = "";
        $actual   = $mailRecord->addRecipientsCaller();
        $this->assertEquals($expected, $actual, "No recipients should have been added.");
    }

    public function testAddRecipients_ParameterIsAnArrayAndArrayIsNotEmpty_ReturnsACommaSeparatedStringOfValidRecipients()
    {
        $mailRecord = new MailRecordCaller();
        $recipients = array(
            array(
                "email" => "foo@bar.com",
            ),
            array(
                "email" => "biz@baz.com",
                "name"  => "Biz Baz",
            ),
            "invalid recipient",
        );

        $expected = "<foo@bar.com>, Biz Baz <biz@baz.com>";
        $actual   = $mailRecord->addRecipientsCaller($recipients);
        $this->assertEquals($expected, $actual, "Only two recipients should have been added.");
    }

    public function testSplitAttachments_ParameterIsNotAnArray_ReturnsAnEmptyArray()
    {
        $mailRecord = new MailRecordCaller();

        $expected = array();
        $actual   = $mailRecord->splitAttachmentsCaller("not an array");
        $this->assertEquals($expected, $actual, "No attachments should have been returned.");
    }

    public function testSplitAttachments_ParameterIsAnArrayButArrayIsEmpty_ReturnsAnEmptyArray()
    {
        $mailRecord = new MailRecordCaller();

        $expected = array();
        $actual   = $mailRecord->splitAttachmentsCaller(array());
        $this->assertEquals($expected, $actual, "No attachments should have been returned.");
    }

    public function testSplitAttachments_InputContainsUploadTypeOnly_ReturnsOneListOfAttachments()
    {
        $mailRecord  = new MailRecordCaller();
        $attachments = array(
            array(
                "type" => MailRecord::ATTACHMENT_TYPE_UPLOAD,
                "id"   => "abcd-1234",
                "name" => "attachment1",
            ),
            array(
                "type" => MailRecord::ATTACHMENT_TYPE_UPLOAD,
                "id"   => "efgh-5678",
                "name" => "attachment2",
            ),
        );

        $expected = array(
            MailRecord::ATTACHMENT_TYPE_UPLOAD => array(
                "abcd-1234attachment1",
                "efgh-5678attachment2",
            ),
        );
        $actual   = $mailRecord->splitAttachmentsCaller($attachments);
        $this->assertEquals($expected, $actual, "Two attachments in one list should have been returned.");
    }

    public function testSplitAttachments_InputContainsThreeTypes_ReturnsThreeListsOfAttachments()
    {
        $mailRecord  = new MailRecordCaller();
        $attachments = array(
            array(
                "type" => MailRecord::ATTACHMENT_TYPE_DOCUMENT,
                "id"   => "document-1",
            ),
            array(
                "type" => MailRecord::ATTACHMENT_TYPE_TEMPLATE,
                "id"   => "template-1",
            ),
            array(
                "type" => MailRecord::ATTACHMENT_TYPE_UPLOAD,
                "id"   => "upload-1",
                "name" => "fooUpload.jpg",
            ),
            array(
                "type" => MailRecord::ATTACHMENT_TYPE_DOCUMENT,
                "id"   => "document-2",
                "name"   => "ignore-me",
            ),
        );

        $expected = array(
            MailRecord::ATTACHMENT_TYPE_DOCUMENT => array(
                "document-1",
                "document-2",
            ),
            MailRecord::ATTACHMENT_TYPE_TEMPLATE => array(
                "template-1",
            ),
            MailRecord::ATTACHMENT_TYPE_UPLOAD => array(
                "upload-1fooUpload.jpg",
            ),
        );
        $actual   = $mailRecord->splitAttachmentsCaller($attachments, true);
        $this->assertEquals($expected, $actual, "Four attachments in three lists should have been returned.");
    }

    public function dataProviderForSetSendRequest_SetBody()
    {
        return array(
            array(
                null,
                null,
                array(
                    "sendDescription" => "",
                ),
            ),
            array(
                null,
                "foo bar",
                array(
                    "sendDescription" => "foo bar",
                ),
            ),
            array(
                "<b>foo</b> <i>bar</i>",
                "foo bar",
                array(
                    "sendDescription" => "<b>foo</b> <i>bar</i>",
                    "setEditor" => "1",
                ),
            ),
        );
    }

    /**
     * @dataProvider dataProviderForSetSendRequest_SetBody
     * @param $htmlBody
     * @param $textBody
     * @param $expected
     */
    public function testSetSendRequest_SetBody($htmlBody, $textBody, $expected)
    {
        $mailRecord            = new MailRecordCaller();
        $mailRecord->html_body = $htmlBody;
        $mailRecord->text_body = $textBody;

        $actual = $mailRecord->setSendRequestCaller();

        $this->assertEquals($expected["sendDescription"], $actual["sendDescription"]);

        if (array_key_exists("setEditor", $expected)) {
            // the "setEditor" values should match
            $this->assertEquals($expected["setEditor"], $actual["setEditor"]);
        } else {
            // the "setEditor" key should not be returned
            $this->assertArrayNotHasKey("setEditor", $actual);
        }
    }

    public function dataProviderForSetSendRequest_SetAttachments()
    {
        return array(
            array(
                array(
                    MailRecord::ATTACHMENT_TYPE_UPLOAD => array('foo', 'bar'),
                ),
                array(
                    "attachments" => 'foo::bar',
                ),
                'Two upload type files should be mapped to attachments request param - other request params not set'
            ),
            array(
                array(
                    MailRecord::ATTACHMENT_TYPE_UPLOAD => array('abc', 'cba'),
                    MailRecord::ATTACHMENT_TYPE_DOCUMENT => array('def', 'fed'),
                    MailRecord::ATTACHMENT_TYPE_TEMPLATE => array('ghi', 'ihg'),
                    "foo" => array('jkl', 'lkj'),
                ),
                array(
                    "attachments" => 'abc::cba',
                    "documents" => 'def::fed',
                    "templateAttachments" => 'ghi::ihg',
                    "foo" => 'jkl::lkj',
                ),
                'All attachment types should be mapped to appropriate request params and array elements imploded'
            ),
        );
    }

    /**
     * @dataProvider dataProviderForSetSendRequest_SetAttachments
     * @param $attachments
     * @param $expected
     * @param $message
     */
    public function testSetSendRequest_SetAttachments($attachments, $expected, $message)
    {
        $mailRecord = new MailRecordCaller();
        $actual = $mailRecord->setSendRequestCaller("ready", null, "", "", "", $attachments);

        foreach(array("attachments", "documents", "templateAttachments", "foo") as $type) {
            if (isset($expected[$type])) {
                $this->assertEquals($expected[$type], $actual[$type], $message);
            } else {
                $this->assertFalse(isset($actual[$type]), $message);
            }
        }
    }

    public function dataProviderForSetSendRequest_SetStatus()
    {
        return array(
            array("send", false),
            array("draft", true),
        );
    }

    /**
     * @dataProvider dataProviderForSetSendRequest_SetStatus
     * @param $status
     * @param $expected
     */
    public function testSetSendRequest_SetStatus($status, $expected)
    {
        $mailRecord = new MailRecordCaller();

        $actual = $mailRecord->setSendRequestCaller($status);

        if ($expected) {
            // the "saveDraft" value should be the string "true"
            $this->assertEquals("true", $actual["saveDraft"]);
        } else {
            // the "saveDraft" key should not be returned
            $this->assertArrayNotHasKey("saveDraft", $actual);
        }
    }

    public function dataProviderForSetSendRequest_SetTeams()
    {
        return array(
            array(
                null,
                null,
            ),
            array(
                array(
                    "primary" => "team1",
                ),
                "team1",
            ),
            array(
                array(
                    "primary" => "team1",
                    "others"   => array("team2", "team3"),
                ),
                "team1,team2,team3",
            ),
        );
    }

    /**
     * @dataProvider dataProviderForSetSendRequest_SetTeams
     * @param $teams
     * @param $expected
     */
    public function testSetSendRequest_SetTeams($teams, $expected)
    {
        $mailRecord        = new MailRecordCaller();
        $mailRecord->teams = $teams;

        $actual = $mailRecord->setSendRequestCaller();

        if (!is_null($expected)) {
            $this->assertEquals($teams["primary"], $actual["primaryteam"]);
            $this->assertEquals($expected, $actual["teamIds"]);
        } else {
            // the "primaryteam" and "teamIds" keys should not be returned
            $this->assertArrayNotHasKey("primaryteam", $actual);
            $this->assertArrayNotHasKey("teamIds", $actual);
        }
    }

    public function dataProviderForSetSendRequest_SetRelated()
    {
        return array(
            array(
                null,
                null,
            ),
            array(
                array(
                    "type" => "Contacts",
                    "id"   => "abcd-1234",
                ),
                array(
                    "parent_type" => "Contacts",
                    "parent_id"   => "abcd-1234",
                ),
            ),
            array(
                array(
                    "id"   => "abcd-1234",
                ),
                null,
            ),
            array(
                array(
                    "type" => "Contacts",
                ),
                null,
            ),
        );
    }

    /**
     * @dataProvider dataProviderForSetSendRequest_SetRelated
     * @param $related
     * @param $expected
     */
    public function testSetSendRequest_SetRelated($related, $expected)
    {
        $mailRecord          = new MailRecordCaller();
        $mailRecord->related = $related;

        $actual = $mailRecord->setSendRequestCaller();

        if (!is_null($expected)) {
            $this->assertEquals($expected["parent_type"], $actual["parent_type"]);
            $this->assertEquals($expected["parent_id"], $actual["parent_id"]);
        } else {
            // the "parent_type" and "parent_id" keys should not be returned
            $this->assertArrayNotHasKey("parent_type", $actual);
            $this->assertArrayNotHasKey("parent_id", $actual);
        }
    }

    public function testSend_Email2SendThrowsAnException_ReturnsArrayWithErrorData()
    {
        self::setExpectedException("MailerException");

        $this->mockEmail->expects($this->once())
            ->method("email2Send")
            ->will($this->throwException(new Exception("An exception was thrown from within email2Send.")));

        $this->mailRecord->mockEmailBean = $this->mockEmail;

        $this->mailRecord->send();
    }

    public function testSend_Email2SendReturnsTrue_ReturnsArray_NoException()
    {
        $this->mockEmail->expects($this->once())
            ->method("email2Send")
            ->will($this->returnValue(true));

        $this->mailRecord->mockEmailBean = $this->mockEmail;

        $this->mailRecord->send();
    }

    public function testSend_Email2SendReturnsTrueAndOutputWasCaptured_ExceptionIsThrown_ReturnsArrayWithErrorData()
    {
        self::setExpectedException("MailerException");

        $this->mockEmail->expects($this->once())
            ->method("email2Send")
            ->will($this->returnValue(true));

        $mailRecord                = $this->createPartialMock('MailRecord', array("endCapturingOutput"));
        $mailRecord->subject       = "MailRecord subject";
        $mailRecord->mockEmailBean = $this->mockEmail;

        $mailRecord->expects($this->once())
            ->method("endCapturingOutput")
            ->will($this->returnValue("output to capture"));

        $mailRecord->send();
    }

    /**
     * This test case is considered a functional test for the relationship between MailRecord and Email. While it is
     * not fully comprehensive, it should help to prevent bugs in MailRecord when untested changes are made to
     * Email::email2Send. Once Email::email2Send is tested -- as we further change/enhance our email workflows
     * throughout the application -- then some of these included test cases can move and become unit tests in the right
     * location, and a single unit test can remain in MailRecordTest for testing MailRecordTest::saveAsDraft as needed.
     *
     * @group functional
     */
    public function testSaveAsDraft()
    {
        OutboundEmailConfigurationTestHelper::setUp();
        $outboundEmailConfiguration = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
            $GLOBALS["current_user"]->id
        );

        $mailRecord              = new MailRecord();
        $mailRecord->mailConfig  = $outboundEmailConfiguration->id;
        $mailRecord->toAddresses = array(
            array(
                "name"  => "Captain Kangaroo",
                "email" => "twolf@sugarcrm.com",
            ),
            array(
                "name"  => "Mister Moose",
                "email" => "twb2@webtribune.com",
            ),
        );
        $mailRecord->ccAddresses = array(
            array(
                "name"  => "Bunny Rabbit",
                "email" => "twb3@webtribune.com",
            ),
        );
        $mailRecord->subject     = "The Funnies";
        // TODO: need input from architecture
        // Can't test attachments without the ability to either mock the filesystem or a utility for adding
        // and removing files from the filesystem.
//        $mailRecord->attachments = array(
//            array(
//                "type" => MailRecord::ATTACHMENT_TYPE_UPLOAD,
//                "name" => "rodgers.tiff",
//                "id"   => "abcd-1234",
//            ),
//        );
        $mailRecord->html_body   = to_html("<b>Hello, World!</b>");
        $mailRecord->text_body   = "Hello, World!";
        $mailRecord->related     = array(
            "type" => "Opportunities",
            "id"   => "efgh-5678",
        );
        $mailRecord->teams       = array(
            "primary" => "West",
            "others"   => array("1", "East"),
        );

        $responseRecord = $mailRecord->saveAsDraft();
        SugarTestEmailUtilities::setCreatedEmail($responseRecord['id']);

        $bean = BeanFactory::getBean('Emails', $responseRecord['id']);
        $this->assertTrue($bean instanceof Email, "The send request should have succeeded and returned the Email SugarBean.");

        $emailClone = clone $bean;
        $email      = $emailClone->toArray();
        $this->assertEquals(36, strlen($email["id"]), "The EmailId should be 36 characters.");

        $keysInEmail = array(
            "from_addr_name",
            "to_addrs_names",
            "cc_addrs_names",
            "bcc_addrs_names",
            "team_set_id",
        );

        foreach ($keysInEmail as $key) {
            $this->assertArrayHasKey($key, $email, "The {$key} key should be found in the email array.");
        }

        $valuesInEmail = array(
            "status"           => "draft",
            "type"             => "draft",
            "name"             => $mailRecord->subject,
            "description_html" => $mailRecord->html_body,
            "description"      => $mailRecord->text_body,
            "parent_id"        => $mailRecord->related["id"],
            "parent_type"      => $mailRecord->related["type"],
            "team_id"          => $mailRecord->teams["primary"],
            "assigned_user_id" => $GLOBALS["current_user"]->id,
            // attachments?
        );

        foreach ($valuesInEmail as $key => $value) {
            $this->assertEquals(
                $value,
                $email[$key],
                "The {$key} key should have the value {$value} in the email array."
            );
        }

        OutboundEmailConfigurationTestHelper::tearDown();
    }
}

class MailRecordCaller extends MailRecord
{
    public $subject = "MailRecordCaller Subject";

    public function addRecipientsCaller($recipients = array())
    {
        return $this->addRecipients($recipients);
    }

    public function splitAttachmentsCaller($attachments = array())
    {
        return $this->splitAttachments($attachments);
    }

    public function setSendRequestCaller(
        $status = "ready",
        $from = null,
        $to = "",
        $cc = "",
        $bcc = "",
        $attachments = array()
    ) {
        return $this->setupSendRequest($status, $from, $to, $cc, $bcc, $attachments);
    }
}
