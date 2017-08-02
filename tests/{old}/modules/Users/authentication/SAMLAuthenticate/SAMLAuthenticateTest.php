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
 * @covers SAMLAuthenticate
 */
class SAMLAuthenticateTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider addQueryVarsProvider
     */
    public function testAddQueryVars($url, $vars, $expected)
    {
        $auth = new SAMLAuthenticate();
        $actual = SugarTestReflection::callProtectedMethod($auth, 'addQueryVars', array($url, $vars));

        $this->assertEquals($expected, $actual);
    }

    protected function tearDown()
    {
        AuthenticationController::setInstance(null);
        parent::tearDown();
    }

    public function testLoginUrl()
    {
        // FIXME TY-1327: figure out why this test is failing
        global $sugar_config;

        $sugar_config['SAML_loginurl'] = 'http://loginURL.example.com/';
        $sugar_config['SAML_X509Cert'] = 'TestCert';
        $sugar_config['SAML_issuer'] = 'testIssuer';

        $authc = new AuthenticationController('SAMLAuthenticate');
        $login = $authc->getLoginUrl(array("platform" => "myplatform", "other" => "stuff"));
        $this->assertContains('loginURL', $login);
        $vars = array();
        parse_str(parse_url($login, PHP_URL_QUERY), $vars);
        $this->assertArrayHasKey("SAMLRequest", $vars);
        $data = gzinflate(base64_decode($vars['SAMLRequest']));
        $this->assertContains("platform=myplatform", $data);
        $this->assertContains("other=stuff", $data);
    }

    public function testNeedLogin()
    {
        $mockauth = $this->createPartialMock('AuthenticationController', array('getLoginUrl', 'isExternal'));
        $rest = SugarTestRestUtilities::getRestServiceMock();

        $mockauth->expects($this->once())
            ->method('isExternal')
            ->will($this->returnValue(true));

        $mockauth->expects($this->once())
            ->method('getLoginUrl')
            ->will($this->returnValue('LoginURLString'));

        AuthenticationController::setInstance($mockauth);

        $e = null;
        try {
            $rest->needLogin();
        } catch (SugarApiExceptionNeedLogin $e) {
        }
        $this->assertNotEmpty($e);

        $this->assertArrayHasKey('url', $e->extraData);
        $this->assertArrayHasKey('platform', $e->extraData);
        $this->assertContains('LoginURLString', $e->extraData['url']);
    }


    public static function addQueryVarsProvider()
    {
        return array(
            'empty-vars-url-unchanged' => array(
                'http://example.com/',
                array(),
                'http://example.com/',
            ),
            'vars-appended-with-?' => array(
                'http://example.com/',
                array('param1' => 'value1'),
                'http://example.com/?param1=value1',
            ),
            'vars-appended-with-&' => array(
                'http://example.com/?param1=value1',
                array('param2' => 'value2'),
                'http://example.com/?param1=value1&param2=value2',
            ),
            'vars-escaped' => array(
                'http://example.com/',
                array('!' => '@'),
                'http://example.com/?%21=%40',
            ),
        );
    }
}
