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
require_once('tests/{old}/modules/Users/AuthenticateTest.php');

/**
 * Bug50936Test.php
 * This tests that we can correctly load a custom settings.php file for SAMLAuthentication when called from
 * modules/Users/authentication/SAMLAuthenticate/index.php
 *
 * This tests mimics the contents of modules/Users/authentication/SAMLAuthenticate/index.php by placing it
 * in a custom directory minus the header() function call.  We can't include that because it'd just cause other issues
 */
class Bug50936Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $customContents;

	public function setUp()
    {
        $GLOBALS['app'] = new SugarApplication();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('files');
    	$_REQUEST['user_name'] = 'foo';
    	$_REQUEST['user_password'] = 'bar';
    	$_SESSION['authenticated_user_id'] = true;
    	$_SESSION['hasExpiredPassword'] = false;
    	$_SESSION['isMobile'] = null;
        $GLOBALS['sugar_config']['authenticationClass'] = 'SAMLAuthenticate';
        $GLOBALS['sugar_config']['SAML_X509Cert'] = 'Bug50936_X509Cert';

        //Create the custom directory if it does not exist
        if(!is_dir('custom/modules/Users/authentication/SAMLAuthenticate')) {
           mkdir_recursive('custom/modules/Users/authentication/SAMLAuthenticate');
        }

$contents = <<<EOQ
<?php
    require_once 'modules/Users/authentication/SAMLAuthenticate/saml.php';

    \$authrequest = new OneLogin_Saml_AuthRequest(SAMLAuthenticate::loadSettings());
    echo \$authrequest->getRedirectUrl();
EOQ;
        SugarTestHelper::saveFile('custom/modules/Users/authentication/SAMLAuthenticate/index.php');
        SugarAutoLoader::put('custom/modules/Users/authentication/SAMLAuthenticate/index.php', $contents);

$contents = <<<EOQ
<?php
                // this function should be modified to return the SAML settings for the current use
                \$settings = new SamlSettings();
                // when using Service Provider Initiated SSO (starting at index.php), this URL asks the IdP to authenticate the user.
                \$settings->idp_sso_target_url = 'http://www.sugarcrm.com/';

                // the certificate for the users account in the IdP
                \$settings->x509certificate = \$GLOBALS['sugar_config']['SAML_X509Cert'];

                // The URL where to the SAML Response/SAML Assertion will be posted
                \$settings->assertion_consumer_service_url = \$GLOBALS['sugar_config']['site_url']. "/index.php?module=Users&action=Authenticate";

                // Name of this application
                \$settings->issuer = "php-saml";

                // Tells the IdP to return the email address of the current user
                \$settings->name_identifier_format = "urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress";

        ?>
EOQ;
        SugarTestHelper::saveFile('custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
        SugarAutoLoader::put('custom/modules/Users/authentication/SAMLAuthenticate/settings.php', $contents, false);
	}

	public function tearDown()
	{
	    SugarTestHelper::tearDown();

	    unset($_REQUEST['login_module']);
        unset($_REQUEST['login_action']);
        unset($_REQUEST['login_record']);
        unset($_REQUEST['user_name']);
        unset($_REQUEST['user_password']);
        unset($_SESSION['authenticated_user_id']);
        unset($_SESSION['hasExpiredPassword']);
        unset($_SESSION['isMobile']);
	}

    public function testLoadCustomSettingsFromIndex()
    {
        // FIXME TY-1325: figure out why this test is failing
        require('custom/modules/Users/authentication/SAMLAuthenticate/index.php');
        $this->expectOutputRegex('/www\.sugarcrm\.com/', 'Failed to override custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
    }


}
