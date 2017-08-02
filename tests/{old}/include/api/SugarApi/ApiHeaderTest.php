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


class ApiHeaderTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->headers = array(
            'Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Expires', 'pageload + 4 hours',
            'Pragma', 'nocache',
            );
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');

    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testSetHeaders() {
        $api = new RestServiceMock();

        foreach($this->headers AS $header => $info) {
            $api->setHeader($header, $info);
        }

        $this->assertEquals($this->headers, $api->getResponseHeaders(), "The Headers Do Not Match");

    }

    public function testSendHeaders() {
        $api = new RestServiceMock();

        $expected_return = '';
        foreach($this->headers AS $header => $info) {
            $api->setHeader($header, $info);
            $expected_return = "{$header}:{$info}\r\n";
        }

        $return = $api->sendHeaders();

        $this->assertEquals($expected_return, $return, "The Headers Sent were incorrect");

    }

    public function testRequestHeaders() {

        $api = new RestServiceMock();

        $headers = $api->getRequest()->request_headers;

        $this->assertNotEmpty($headers, "The Request Headers Are Empty");
    }
}

class RestServiceMock extends RestService
{
    public function __construct()
    {
        $this->response = new RestResponse(array());
    }

    public function getResponseHeaders() {
        return $this->response->getHeaders();
    }
    // overloading to return the headers it would send as a string to verify it working
    public function sendHeaders() {
        $return = '';
        foreach($this->getResponseHeaders() AS $header => $info) {
            $return = "{$header}:{$info}\r\n";
        }
        return $return;
    }
}
