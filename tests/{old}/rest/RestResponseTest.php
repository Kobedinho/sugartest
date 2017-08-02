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
require_once('include/download_file.php');

class RestResponseTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Find specific header in an array
     * @param array $headers
     * @param string $header
     * @return string|false
     */
    protected function findHeader($headers, $header)
    {
        foreach($headers as $h) {
            $kv = explode($h, ':', 1);
            if(count($kv) < 2)  continue;
            if($kv[0] == $header) {
                return $kv[1];
            }
        }
        return false;
    }

    /**
     * @dataProvider versionProvider
     */
    public function testHttpVersion($server, $version)
    {
        $r = new RestResponse($server);
        $this->assertEquals($version, $r->getVersion());
    }

    public function versionProvider()
    {
        return array(
            array(array(), '1.1'),
            array(array('SERVER_PROTOCOL' => 'HTTP/1.0'), '1.0'),
            array(array('SERVER_PROTOCOL' => 'HTTP/2.0'), '2.0'),
        );
    }

    public function testHeader()
    {
        $r = new RestResponse(array());
        $r->setHeader('Test-Header', 'foo');
        $this->assertEquals('foo', $r->getHeader('Test-Header'));
        $this->assertFalse($r->hasHeader('Content-Type'));
        $this->assertTrue($r->hasHeader('Test-Header'));
        // For now, we don't lowercase
        $this->assertFalse($r->hasHeader('test-header'));
        $r->setHeader('test-header', 'bar');
        $this->assertEquals('bar', $r->getHeader('test-header'));
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testProcessJSON($content, $output)
    {
        $r = new RestResponse(array());
        $r->setType(RestResponse::JSON);
        $r->setContent($content);
        $response = $r->processContent();

        $this->assertEquals($output, $response);
        if(!empty($response)) {
            $this->assertEquals(strlen($response), $r->getHeader('Content-Length'));
        }
        $this->assertEquals("application/json", $r->getHeader('Content-Type'));

    }

    public function jsonProvider()
    {
        return array(
                array('', '""'),
                array("test", '"test"'),
                array(123, '123'),
                array(array("foo"=>"bar", "test"=> array('"foo"', "'<test>'")), '{"foo":"bar","test":["\u0022foo\u0022","\u0027\u003Ctest\u003E\u0027"]}'),
        );
    }

    public function testProcessJSONHTML()
    {
        $r = new RestResponse(array());
        $r->setType(RestResponse::JSON_HTML);
        $r->setContent(array("foo"=>"bar", "test"=> array('"foo"', "'<test>'")));
        $response = $r->processContent();

        $this->assertEquals('{&quot;foo&quot;:&quot;bar&quot;,&quot;test&quot;:[&quot;\&quot;foo\&quot;&quot;,&quot;&#039;&lt;test&gt;&#039;&quot;]}', $response);
        $this->assertEquals(strlen($response), $r->getHeader('Content-Length'));
        $this->assertEquals("text/html", $r->getHeader('Content-Type'));

        $r->setHeader('Content-Type', "text/plain");
        $r->processContent();
        $this->assertEquals("text/plain", $r->getHeader('Content-Type'));
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testProcessRaw($content, $output)
    {
        $r = new RestResponse(array());
        $r->setType(RestResponse::RAW);
        $r->setContent($content);
        $response = $r->processContent();

        $this->assertEquals($content, $response);
    }

    public function testHeaders()
    {
        $r = new MockRestResponse(array());
        $r->setType(RestResponse::JSON);
        $r->setHeader("Content-Type", "text/plain");
        $r->sendHeaders();

        $this->assertContains("Content-Type: text/plain", $r->sent_headers);

        $r->sent_headers = array();
        $r->headers_sent = true;
        $r->sendHeaders();
        $this->assertEmpty($r->sent_headers);
    }

    public function testErrors()
    {
        $r = new MockRestResponse(array());
        $r->setType(RestResponse::JSON);
        $r->setStatus(404);
        $r->sendHeaders();

        $this->assertContains("HTTP/1.1 404 Not Found", $r->sent_headers[0]);
        $this->assertContains("Status: 404 Not Found", $r->sent_headers);
    }

    public function testEtag()
    {
        $r = new RestResponse(array());
        $r->setType(RestResponse::JSON);
        $data = "test 134";
        $dataArray = array("test"=>"data");
        $r->setContent($data);

        // JSON/array tests
        $r = new RestResponse(array('HTTP_IF_NONE_MATCH' => md5(json_encode($dataArray))));
        $r->setType(RestResponse::JSON);
        $r->setContent($dataArray);
        $this->assertTrue($r->generateETagHeader());
        $this->assertEquals(304, $r->getStatus());
        
        $r = new RestResponse(array());
        $r->setType(RestResponse::JSON);
        $r->setContent($dataArray);
        $this->assertFalse($r->generateETagHeader());
        
        // text tests
        $this->assertFalse($r->generateETagHeader(""));
        $this->assertFalse($r->generateETagHeader(md5($data)));

        $r = new RestResponse(array('HTTP_IF_NONE_MATCH' => md5($data)));
        $r->setType(RestResponse::JSON);
        $r->setContent($data);
        $this->assertTrue($r->generateETagHeader());
        $this->assertEmpty($r->getBody());
        $this->assertEquals(304, $r->getStatus());
        
        $r = new RestResponse(array());
        $r->setType(RestResponse::RAW);
        $r->setContent($data);
        // no header
        $this->assertFalse($r->generateETagHeader(md5($data)));

        // not matching data
        $r = new RestResponse(array('HTTP_IF_NONE_MATCH' => md5($data.$data)));
        $r->setType(RestResponse::RAW);
        $r->setContent($data);
        $this->assertFalse($r->generateETagHeader(md5($data)));

        // matching data
        $r = new RestResponse(array('HTTP_IF_NONE_MATCH' => md5($data)));
        $r->setType(RestResponse::RAW);
        $r->setContent($data);
        $this->assertTrue($r->generateETagHeader(md5($data)));
        $this->assertEmpty($r->getBody());
        $this->assertEquals(304, $r->getStatus());

        $r = new RestResponse(array('HTTP_IF_NONE_MATCH' => md5($data)));
        $r->setType(RestResponse::RAW);
        $r->setContent($data);
        $this->assertTrue($r->generateETagHeader());
        $this->assertEmpty($r->getBody());
        $this->assertEquals(304, $r->getStatus());
    }

    public function testDownload()
    {
        $rs = new RestService();
        $r = new RestResponse(array());
        $rs->setResponse($r);
        $down = new DownloadFileApi($rs);
        $down->outputFile("image", array("path" => "/tmp/test", "name" => "test.png"));

        $this->assertEquals(RestResponse::FILE, $r->getType());
        $h = $r->getHeaders();
        $this->assertEquals("application/octet-stream", $h['Content-Type']);
        $this->assertTrue($r->hasHeader('Expires'));
        $this->assertEquals("nosniff", $h['X-Content-Type-Options']);

        $down->outputFile("image", array("path" => "/tmp/test", "name" => "test.png", "content-type" => "image/png"));
        $h = $r->getHeaders();
        $this->assertEquals("image/png", $h['Content-Type']);

        $down->outputFile("file", array("path" => "/tmp/test", "name" => "test.png", "content-type" => "image/png"));
        $h = $r->getHeaders();
        $this->assertEquals("application/force-download", $h['Content-Type']);
        $this->assertContains('filename="test.png"', $h['Content-Disposition']);
    }

    public function testSendWrongFile()
    {
        $r = new MockRestResponse(array());
        $r->setType(RestResponse::FILE)->setFilename("/blah/blah/nosuchfile");
        $r->send();
        $this->assertContains("HTTP/1.1 404 Not Found", $r->sent_headers[0]);
    }

}

class MockRestResponse extends RestResponse
{
    public $sent_headers = array();
    public $headers_sent = false;

    protected function sendHeader($h)
    {
        $this->sent_headers[] = $h;
    }

    protected function headersSent()
    {
        return $this->headers_sent;
    }

}