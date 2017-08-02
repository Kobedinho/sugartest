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
require_once('modules/Users/authentication/SAMLAuthenticate/saml.php');

/**
 * @ticket 57454
*/
class Bug57454Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Custom file with settings for SAMLAuthenticate
     *
     * @var string
     */
    public $customSAMLSettings = 'custom/modules/Users/authentication/SAMLAuthenticate/settings.php';

    public function setUp()
    {
        if(!function_exists('gzinflate')) {
            $this->markTestSkipped("No gzip - skipping");
        }
            if(!function_exists('simplexml_load_string')) {
            $this->markTestSkipped("No SimpleXML - skipping");
        }
        parent::setUp();
    }

    public function startUp()
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->customSAMLSettings);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testSAMLEncoding()
    {
        // FIXME TY-1326: figure out why this test is failing
        global $sugar_config;
        $sugar_config['SAML_loginurl'] = 'http://loginURL.example.com/';
        $sugar_config['SAML_X509Cert'] = 'TestCert';
        $sugar_config['SAML_issuer'] = 'testIssuer';

        if(SugarAutoLoader::fileExists($this->customSAMLSettings)) {
            // if custom file settings exists then remove it.
            SugarAutoLoader::unlink($this->customSAMLSettings);
        }

        $auth = new SAMLAuthenticate();
        $url = $auth->getLoginUrl();
        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertNotEmpty($query, 'No query part');
        parse_str($query, $components);
        $this->assertArrayHasKey('SAMLRequest', $components);
        $data = gzinflate(base64_decode(rawurldecode($components['SAMLRequest'])));
        $this->assertNotEmpty($data, "Data did not decode");
        $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NONET);
        $this->assertNotEmpty($xml, 'XML did not parse');
        $myurl = $xml['AssertionConsumerServiceURL'];
        $this->assertNotEmpty($myurl, 'URL not found');
        $this->assertEquals(parse_url($GLOBALS['sugar_config']['site_url'], PHP_URL_HOST), parse_url($myurl, PHP_URL_HOST), "Bad URL");
    }
}
