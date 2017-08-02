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
 * @issue BR-1756
 * Can not test logout() rest API properly due to cookies/sessions handling there
 */
class SamlLogoutTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function testLogoutURL()
    {
        // FIXME TY-1328: figure out why this test is failing
        global $sugar_config;

        $sugar_config['SAML_loginurl'] = 'http://loginURL.example.com/';
        $sugar_config['SAML_X509Cert'] = 'TestCert';
        $sugar_config['SAML_issuer'] = 'testIssuer';
        $sugar_config['SAML_SLO'] = 'http://logoutURL.example.com/';

        $authc = new AuthenticationController('SAMLAuthenticate');
        $logout = $authc->getLogoutUrl();
        $this->assertContains($sugar_config['SAML_SLO'], $logout);
        $this->assertContains('?SAMLRequest=', $logout);
    }
}
