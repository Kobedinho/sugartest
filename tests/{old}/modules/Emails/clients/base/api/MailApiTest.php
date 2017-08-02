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
 * @group api
 * @group email
 */
class MailApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api,
            $mailApi,
            $emailUI,
            $userCacheDir;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp('app_list_strings');
        $this->api     = SugarTestRestUtilities::getRestServiceMock();
        $this->mailApi = $this->createPartialMock('MailApi', array("initMailRecord", "getEmailRecipientsService", "getEmailBean"));

        $this->emailUI = new EmailUI();
        $this->emailUI->preflightUserCache();
        $this->userCacheDir = $this->emailUI->userCacheDir;
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
        if (file_exists($this->userCacheDir)) {
            rmdir_recursive($this->userCacheDir);
        }
    }

    public function testArchiveMail_StatusIsArchive_CallsMailRecordArchive()
    {
        $args = array(
            MailApi::STATUS       => "archive",
            MailApi::DATE_SENT    => "2014-12-25T18:30:00",
            MailApi::FROM_ADDRESS => "John Doe <x@y.z>",
            MailApi::TO_ADDRESSES => array(array("email" => "a@b.c")),
            MailApi::SUBJECT => 'foo',
        );

        $mailRecordMock = $this->createPartialMock('MailRecord', array("archive"));
        $mailRecordMock->expects($this->once())
            ->method("archive");

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $this->mailApi->archiveMail($this->api, $args);
    }

    public function testCreateMail_StatusIsSaveAsDraft_CallsMailRecordSaveAsDraft()
    {
        $args = array(
            MailApi::STATUS => "draft",
        );

        $mockResult = array(
            "id" => '1234567890',
        );

        $mailRecordMock = $this->createPartialMock('MailRecord', array("saveAsDraft"));
        $mailRecordMock->expects($this->once())
            ->method("saveAsDraft")
            ->will($this->returnValue($mockResult));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $this->mailApi->createMail($this->api, $args);
    }

    public function testCreateMail_StatusIsReady_CallsMailRecordSend()
    {
        $args = array(
            MailApi::STATUS       => "ready",
            MailApi::EMAIL_CONFIG => "foo",
            MailApi::TO_ADDRESSES => array(array("email" => "a@b.c")),
        );

        $mockResult = array(
            "id" => '1234567890',
        );

        $mailRecordMock = $this->createPartialMock('MailRecord', array("send"));
        $mailRecordMock->expects($this->once())
            ->method("send")
            ->will($this->returnValue($mockResult));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $this->mailApi->createMail($this->api, $args);
    }

    public function testRecipientLookup_AttemptToResolveTenRecipients_CallsLookupTenTimes()
    {
        $expected = 10;
        $args     = array();

        for ($i = 0; $i < $expected; $i++) {
            $args[] = array("email" => "recipient{$i}");
        }

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', array("lookup"));
        $emailRecipientsServiceMock->expects($this->exactly($expected))
            ->method("lookup")
            ->will($this->returnArgument(0));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $actual = $this->mailApi->recipientLookup($this->api, $args);
        $this->assertEquals($args, $actual, "Should have returned an array matching \$args.");
    }

    public function testValidateEmailAddresses_OneIsValidAndOneIsInvalid()
    {
        $args = array(
            "foo@bar.com",
            "foo",
        );
        $actual = $this->mailApi->validateEmailAddresses($this->api, $args);
        $this->assertTrue($actual[$args[0]], "Should have set the value for key '{$args[0]}' to true.");
        $this->assertFalse($actual[$args[1]], "Should have set the value for key '{$args[1]}' to false.");
    }

    public function testFindRecipients_NextOffsetIsLessThanTotalRecords_ReturnsRealNextOffset()
    {
        $args = array(
            "offset"  => 0,
            "max_num" => 5,
        );

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->any())
            ->method("find")
            ->will($this->returnValue(array_pad(array(10), 10, 0)));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = 5;
        $actual   = $response["next_offset"];
        $this->assertEquals($expected, $actual, "The next offset should be {$expected}.");
    }

    public function testFindRecipients_NextOffsetIsGreaterThanTotalRecords_ReturnsNextOffsetAsNegativeOne()
    {
        $args = array(
            "offset"  => 5,
            "max_num" => 5,
        );

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->any())
            ->method("findCount")
            ->will($this->returnValue(4));
        $emailRecipientsServiceMock->expects($this->any())
            ->method("find")
            ->will($this->returnValue(array()));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = -1;
        $actual   = $response["next_offset"];
        $this->assertEquals($expected, $actual, "The next offset should be -1.");
    }

    public function testFindRecipients_OffsetIsEnd_ReturnsNextOffsetAsNegativeOne()
    {
        $args = array(
            "offset" => "end",
        );

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->never())->method("findCount");
        $emailRecipientsServiceMock->expects($this->never())->method("find");

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = -1;
        $actual   = $response["next_offset"];
        $this->assertEquals($expected, $actual, "The next offset should be -1.");
    }

    public function testFindRecipients_NoArguments_CallsFindCountAndFindWithDefaults()
    {
        $args = array();

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->once())
            ->method("find")
            ->with($this->isEmpty(),
                $this->equalTo("LBL_DROPDOWN_LIST_ALL"),
                $this->isEmpty(),
                $this->equalTo(21),
                $this->equalTo(0))
            ->will($this->returnValue(array()));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
    }

    public function testFindRecipients_HasAllArguments_CallsFindCountAndFindWithArguments()
    {
        $args = array(
            "q"           => "foo",
            "module_list" => "contacts",
            "order_by"    => "name,email:desc",
            "max_num"     => 5,
            "offset"      => 3,
        );

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->once())
            ->method("find")
            ->with($this->equalTo($args["q"]),
                $this->equalTo($args["module_list"]),
                $this->equalTo(array("name" => "ASC", "email" => "DESC")),
                $this->equalTo(6),
                $this->equalTo(3))
            ->will($this->returnValue(array()));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
    }

    /**
     * @group mailattachment
     */
    public function testClearUserCache_UserCacheDirDoesNotExist_CreatedSuccessfully()
    {
        if (file_exists($this->userCacheDir)) {
            rmdir_recursive($this->userCacheDir);
        }
        $this->mailApi->clearUserCache($this->api, array());
        $this->_assertCacheDirCreated();
        $this->_assertCacheDirEmpty();
    }

    /**
     * @group mailattachment
     */
    public function testClearUserCache_UserCacheDirContainsFiles_ClearedSuccessfully()
    {
        sugar_file_put_contents($this->userCacheDir . "/test.txt", create_guid());
        $this->mailApi->clearUserCache($this->api, array());
        $this->_assertCacheDirCreated();
        $this->_assertCacheDirEmpty();
    }

    /**
     * @group mailattachment
     */
    public function testSaveAttachment_CallsAppropriateEmailFunction()
    {
        $mockResult = array('name' => 'foo');

        $emailMock = $this->createPartialMock("Email", array("email2init", "email2saveAttachment"));
        $emailMock->expects($this->once())
            ->method("email2init");
        $emailMock->expects($this->once())
            ->method("email2saveAttachment")
            ->will($this->returnValue($mockResult));

        $api = $this->getMockBuilder('MailApi')
            ->disableOriginalConstructor()
            ->setMethods(array('checkPostRequestBody', 'getEmailBean'))
            ->getMock();
        $api->expects($this->once())->method('checkPostRequestBody')->willReturn(true);
        $api->expects($this->once())->method('getEmailBean')->willReturn($emailMock);

        $result = $api->saveAttachment($this->api, array());

        $this->assertEquals($mockResult, $result, "Should return the response from email2saveAttachment");
    }

    /**
     * @group mailattachment
     * @expectedException SugarApiExceptionRequestTooLarge
     */
    public function testSaveAttachment_AttachmentIsTooLarge_ThrowsException()
    {
        $_files = $_FILES;
        $_FILES = array();

        $api = $this->getMockBuilder('MailApi')
            ->disableOriginalConstructor()
            ->setMethods(array('getContentLength', 'getPostMaxSize'))
            ->getMock();
        $api->expects($this->once())->method('getContentLength')->willReturn(500);
        $api->expects($this->once())->method('getPostMaxSize')->willReturn(100);
        $api->saveAttachment($this->api, array());

        $_FILES = $_files;
    }

    /**
     * @group mailattachment
     */
    public function testRemoveAttachment_FileExists_RemovedSuccessfully()
    {
        //clear the cache first
        $em = new EmailUI();
        $em->preflightUserCache();

        //create the test attachment to be removed
        $fileGuid = create_guid();
        sugar_file_put_contents($this->userCacheDir . '/' . $fileGuid, create_guid());

        $this->mailApi->expects($this->once())
            ->method("getEmailBean")
            ->will($this->returnValue(new Email()));

        $this->mailApi->removeAttachment($this->api, array('file_guid' => $fileGuid));

        //verify it was removed
        $this->_assertCacheDirEmpty();
    }

    /**
     * @dataProvider validationProvider
     */
    public function testMailApi_run_validation($args, $exceptionExpected, $exceptionArgs = null)
    {
        $mailApiMock = $this->createPartialMock('MailApi', array("invalidParameter"));
        if (!empty($exceptionExpected)) {
            $mailApiMock->expects($this->once())
                ->method("invalidParameter")
                ->with($exceptionExpected, $exceptionArgs)
                ->will($this->throwException(new SugarApiExceptionInvalidParameter($exceptionExpected)));
            $this->setExpectedException("SugarApiExceptionInvalidParameter");
        } else {
            $mailApiMock->expects($this->never())
                ->method("invalidParameter");
        }

        $data = array(
            MailApi::STATUS       => "ready",
            MailApi::EMAIL_CONFIG => "1234567890",
            MailApi::TO_ADDRESSES => array(array("email" => "a@b.c")),
        );
        $arguments = array_merge($data,$args);

        $mailApiMock->validateArguments($arguments);
    }

    public function validationProvider() {
        return array(
            0 => array(
                array(),
                false,
            ),
            1 => array(
                array(MailApi::STATUS => 'draft'),
                false,
            ),
            2 => array(
                array(MailApi::STATUS => 'qwerty'),
                'LBL_MAILAPI_INVALID_ARGUMENT_VALUE',
                array(MailApi::STATUS),
            ),
            3 => array(
                array(MailApi::TO_ADDRESSES => array()),
                'LBL_MAILAPI_NO_RECIPIENTS',
                null,
            ),
            4 => array(
                array(
                    MailApi::TO_ADDRESSES => array(),
                    MailApi::CC_ADDRESSES => array(array("email" => "a@b.c")),
                ),
                false,
            ),
            5 => array(
                array(
                    MailApi::TO_ADDRESSES  => array(),
                    MailApi::BCC_ADDRESSES => array(array("email" => "a@b.c")),
                ),
                false,
            ),
            6 => array(
                array(
                    MailApi::STATUS       => 'draft',
                    MailApi::TO_ADDRESSES => array(),
                ),
                false,
            ),
            7 => array(
                array(MailApi::TO_ADDRESSES => array(array("email" => null))),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TO_ADDRESSES, 'email'),
            ),
            8 => array(
                array(MailApi::CC_ADDRESSES => array(array("email" => array()))),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::CC_ADDRESSES, 'email'),
            ),
            9 => array(
                array(MailApi::BCC_ADDRESSES => array(array("email" => true))),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::BCC_ADDRESSES, 'email'),
            ),
            10 => array(
                array(MailApi::CC_ADDRESSES => array(array("email" => new stdClass()))),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::CC_ADDRESSES, 'email'),
            ),
            11 => array(
                array(MailApi::ATTACHMENTS => '1234567890'),
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                array(MailApi::ATTACHMENTS),
            ),
            12 => array(
                array(
                    MailApi::ATTACHMENTS => array(
                        array(),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::ATTACHMENTS, 'type'),
            ),
            13 => array(
                array(
                    MailApi::ATTACHMENTS => array(
                        array("type" => "document"),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::ATTACHMENTS, 'id'),
            ),
            14 => array(
                array(
                    MailApi::ATTACHMENTS => array(
                        array(
                            "type" => "upload",
                            "id"   => "1234567890",
                        ),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::ATTACHMENTS, 'name'),
            ),
            15 => array(
                array(
                    MailApi::TEAMS => "1",
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                array(MailApi::TEAMS),
            ),
            16 => array(
                array(
                    MailApi::TEAMS => array(
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TEAMS, 'primary'),
            ),
            17 => array(
                array(
                    MailApi::TEAMS => array(
                        "others"  => array(
                            array(),
                        ),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TEAMS, 'primary'),
            ),
            18 => array(
                array(
                    MailApi::TEAMS => array(
                        "primary" => '',
                        "others"  => array(
                            '1234-1234-1234',
                        ),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TEAMS, 'primary'),
            ),
            19 => array(
                array(
                    MailApi::TEAMS => array(
                        "primary" => 123,
                        "others"  => array(
                            '1234-1234-1234',
                        ),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TEAMS, 'primary'),
            ),
            20 => array(
                array(
                    MailApi::TEAMS => array(
                        "primary" => '1234567890',
                        "others"  => array(
                            array(),
                        ),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TEAMS, 'others'),
            ),
            21 => array(
                array(
                    MailApi::TEAMS => array(
                        "primary" => '1234567890',
                        "others"  => array(
                            '',
                        ),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TEAMS, 'others'),
            ),
            22 => array(
                array(
                    MailApi::TEAMS => array(
                        "primary" => '1234567890',
                        "others"  => array(
                            new stdClass(),
                        ),
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TEAMS, 'others'),
            ),
            23 => array(
                array(
                    MailApi::TEAMS => array(
                        "primary" => '1234567890',
                        "others"  => array(
                            '1234-1234-1234',
                        ),
                    ),
                ),
                false,
            ),
            24 => array(
                array(MailApi::RELATED => '1234567890'),
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                array(MailApi::RELATED),
            ),
            25 => array(
                array(
                    MailApi::RELATED => array(
                        "type" => "Contacts",
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::RELATED, "id"),
            ),
            26 => array(
                array(
                    MailApi::RELATED => array(
                        "type" => "Contacts",
                        "id"   => "1234567890",
                    ),
                ),
                false,
            ),
            27 => array(
                array(
                    MailApi::RELATED => array(
                         "type" => "Widgets",
                         "id"   => "1234567890",
                    ),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::RELATED, "type"),
            ),
            28 => array(
                array(MailApi::SUBJECT => 'Email Subject'),
                false,
            ),
            29 => array(
                array(MailApi::SUBJECT => array()),
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                array(MailApi::SUBJECT),
            ),
            30 => array(
                array(MailApi::HTML_BODY => 'HTML Body'),
                false,
            ),
            31 => array(
                array(MailApi::HTML_BODY => new stdClass()),
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                array(MailApi::HTML_BODY),
            ),
            32 => array(
                array(MailApi::TEXT_BODY => 'TEXT Body'),
                false,
            ),
            33 => array(
                array(MailApi::TEXT_BODY => false),
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                array(MailApi::TEXT_BODY),
            ),
            /* 'Archive' has some specific requirements */
            34 => array(
                array(
                    MailApi::STATUS => 'archive',
                    MailApi::FROM_ADDRESS => 'John Doe <john@doe.com>',
                    MailApi::DATE_SENT => '2014-12-25T18:30:00',
                    MailApi::TO_ADDRESSES => array(array("email" => "a@b.c")),
                    MailApi::SUBJECT => 'foo',
                ),
                false,
            ),
            35 => array(
                array(
                    MailApi::STATUS => 'archive',
                    MailApi::DATE_SENT => '2014-12-25T18:30:00',
                    MailApi::TO_ADDRESSES => array(array("email" => "a@b.c")),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_VALUE',
                array(MailApi::FROM_ADDRESS),
            ),
            36 => array(
                array(
                    MailApi::STATUS => 'archive',
                    MailApi::FROM_ADDRESS => 'John Doe <john@doe.com>',
                    MailApi::TO_ADDRESSES => array(array("email" => "a@b.c")),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_VALUE',
                array(MailApi::DATE_SENT),
            ),
            37 => array(
                array(
                    MailApi::STATUS => 'archive',
                    MailApi::FROM_ADDRESS => 'John Doe <john@doe.com>',
                    MailApi::DATE_SENT => '2014-12-25T18:30:00',
                    MailApi::TO_ADDRESSES => array(array('name' => 'John')),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                array(MailApi::TO_ADDRESSES, 'email'),
            ),
            38 => array(
                array(
                    MailApi::STATUS => 'archive',
                    MailApi::FROM_ADDRESS => 'John Doe <john@doe.com>',
                    MailApi::DATE_SENT => '2014-12-25T18:30:00',
                    MailApi::TO_ADDRESSES => array(array("email" => "a@b.c")),
                ),
                'LBL_MAILAPI_INVALID_ARGUMENT_VALUE',
                array(MailApi::SUBJECT),
            ),
        );
    }

    /**
     * Check to make sure path is created
     */
    protected function _assertCacheDirCreated()
    {
        $this->assertTrue(file_exists($this->userCacheDir), "Cache directory should exist");
    }

    /**
     * Check to make sure path is empty
     */
    protected function _assertCacheDirEmpty()
    {
        $files = findAllFiles($this->userCacheDir, array());
        $this->assertEquals(0, count($files), "Cache directory should be empty");
    }
}
