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

use Sugarcrm\Sugarcrm\Security\Crypto\Blowfish;
 

class SugarFieldEncryptTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function _providerEmailTemplateFormat()
    {
        return array(
            array(Blowfish::encode(Blowfish::getKey('encrypt_field'), 'Test value'), 'Test value'),
            );
    }
    
    /**
     * @dataProvider _providerEmailTemplateFormat
     */
    public function testEmailTemplateFormat($unformattedValue, $expectedValue)
	{
   		$sfr = SugarFieldHandler::getSugarField('encrypt');
        $formattedValue = $sfr->getEmailTemplateValue($unformattedValue,array(), array('notify_user' => $GLOBALS['current_user']));
        $this->assertEquals($expectedValue, $formattedValue);    	
    }
}

?>
