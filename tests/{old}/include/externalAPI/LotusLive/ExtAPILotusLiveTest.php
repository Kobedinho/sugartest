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
 * ExtAPILotusLiveTest.php
 *
 * This test is for the ExtAPILotusLive.php class and the related functionality towards the Lotus Live web service
 *
 * @author Collin Lee
 *
 */

require_once('tests/{old}/include/externalAPI/LotusLive/ExtAPILotusLiveMock.php');

class ExtAPILotusLiveTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        global $app_strings;
      	$app_strings = return_application_language('en_us');
    }

    /**
     * testUploadDocConflictErrorMessage
     *
     * This method tests that we get a unique error message for duplicate upload document conflicts
     *
     */
    public function testUploadDocConflictErrorMessage()
    {
        /*
        $responseMock = $this->createPartialMock('Response', array('getBody', 'getMessage', 'isSuccessful'));
        $responseMock->expects($this->any())
            ->method('isSuccessful')
            ->with($this->any())
            ->will($this->returnValue(false));
        $responseMock->expects($this->any())
            ->method('getBody')
            ->with($this->any())
            ->will($this->returnValue('<?xml version="1.0" encoding="UTF-8"?><lcmis:error xmlns:lcmis="http://www.ibm.com/xmlns/prod/sn/cmis"><lcmis:code>contentAlreadyExists</lcmis:code><lcmis:message>EJPVJ9037E: Unable to add media.</lcmis:message><lcmis:userAction></lcmis:userAction></lcmis:error>'));
        $responseMock->expects($this->any())
            ->method('getMessage')
            ->with($this->any())
            ->will($this->returnValue('Conflict'));

        $clientMock3 = $this->createPartialMock('Client', array('request'));
        $clientMock3->expects($this->any())
            ->method('request')
            ->with($this->any())
            ->will($this->returnValue($responseMock));

        $clientMock2 = $this->createPartialMock('Client', array('setHeaders'));
        $clientMock2->expects($this->any())
            ->method('setHeaders')
            ->with($this->any())
            ->will($this->returnValue($clientMock3));

        $clientMock = $this->createPartialMock('Client', array('setRawData'));
        $clientMock->expects($this->any())
            ->method('setRawData')
            ->with($this->any())
            ->will($this->returnValue($clientMock2));

        $oauthMock = $this->createPartialMock('SugarOAuth', array('setUri'));
        $oauthMock->expects($this->any())
            ->method('setUri')
            ->will($this->returnValue($clientMock));

        $externalAPILotusLiveMock = new ExtAPILotusLiveMock();
        ExtAPILotusLiveMock::$llMimeWhitelist = array();
        $externalAPILotusLiveMock->sugarOauthMock = $oauthMock;

        //$result = $externalAPILotusLiveMock->uploadDoc(new Document(), 'data/SugarBean.php', 'Bug50322Test.doc', 'application/msword');
        */

        $externalAPILotusLiveMock = new ExtAPILotusLiveMock();
        $msg = $externalAPILotusLiveMock->getErrorStringFromCode('Conflict');
        $this->assertEquals('A file with the same name already exists in the system.', $msg);

        $msg = $externalAPILotusLiveMock->getErrorStringFromCode();
        $this->assertEquals('An error occurred when trying to save to the external account.', $msg);

        $msg = $externalAPILotusLiveMock->getErrorStringFromCode(array());
        $this->assertEquals('An error occurred when trying to save to the external account.', $msg);
    }

}

