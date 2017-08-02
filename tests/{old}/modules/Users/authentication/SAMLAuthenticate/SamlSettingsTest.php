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


class SamlAuthTest extends  Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Custom file with settings for SAMLAuthenticate
     *
     * @var string
     */
    public $customSAMLSettings = 'custom/modules/Users/authentication/SAMLAuthenticate/settings.php';

    public function startUp()
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->customSAMLSettings);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testSettingsConfig()
    {
        global $sugar_config;

        if(SugarAutoLoader::fileExists($this->customSAMLSettings)) {
            // if custom file settings exists then remove it.
            SugarAutoLoader::unlink($this->customSAMLSettings);
        }

        $sugar_config['SAML_loginurl'] = 'loginURL';
        $sugar_config['SAML_X509Cert'] = 'TestCert';
        $sugar_config['SAML_issuer'] = 'testIssuer';
        $settings = SAMLAuthenticate::loadSettings();
        $this->assertEquals('loginURL', $settings->idpSingleSignOnUrl);
        $this->assertEquals('TestCert', $settings->idpPublicCertificate);
        $this->assertEquals('testIssuer', $settings->spIssuer);
    }

    public function testSettingsBC()
    {
        // FIXME TY-1332: figure out why this test is failing
        $contents = <<<EOQ
<?php
\$settings = new SamlSettings();
\$settings->idp_sso_target_url = 'www.sugarcrm.com';
\$settings->x509certificate = 'TestCert';
\$settings->assertion_consumer_service_url = 'testURL';
\$settings->issuer = "php-saml";
\$settings->name_identifier_format = "testID";
\$settings->saml_settings['check']['user_name'] = '//root';
EOQ;
       SugarAutoLoader::put($this->customSAMLSettings, $contents);
       $settings = SAMLAuthenticate::loadSettings();
       $this->assertEquals('www.sugarcrm.com', $settings->idpSingleSignOnUrl);
       $this->assertEquals('TestCert', $settings->idpPublicCertificate);
       $this->assertEquals('testURL', $settings->spReturnUrl);
       $this->assertEquals('testID', $settings->requestedNameIdFormat);
       $this->assertTrue($settings->useXML);
       $this->assertEquals('//root', $settings->saml2_settings['check']['user_name']);
    }

    public function testSettingsIssuer()
    {
        // FIXME TY-1332: figure out why this test is failing
        global $sugar_config;
        $sugar_config['SAML_issuer'] = 'testIssuer';
        $contents = <<<EOQ
<?php
\$settings = new SamlSettings();
\$settings->idp_sso_target_url = 'www.sugarcrm.com';
\$settings->x509certificate = 'TestCert';
\$settings->assertion_consumer_service_url = 'testURL';
\$settings->issuer = "php-saml";
\$settings->name_identifier_format = "testID";
EOQ;
        SugarAutoLoader::put($this->customSAMLSettings, $contents);
        $settings = SAMLAuthenticate::loadSettings();
        $this->assertObjectNotHasAttribute('useXML', $settings);
        $this->assertEquals('testIssuer', $settings->spIssuer);
    }

}
