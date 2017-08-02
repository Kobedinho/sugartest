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
 

$timedate = TimeDate::getInstance();

class Bug28260Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $user;
    
	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->user = $GLOBALS['current_user'];
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);
    }
    
    public function _providerEmailTemplateFormat()
    {
        return array(
            array('10/11/2010 13:00','10/11/2010 13:00', 'm/d/Y', 'H:i' ), 
            array('11/10/2010 13:00','11/10/2010 13:00', 'd/m/Y', 'H:i' ), 
            array('2010-10-11 13:00:00','10/11/2010 13:00', 'm/d/Y', 'H:i' ), 
            array('2010-10-11 13:00:00','11/10/2010 13:00', 'd/m/Y', 'H:i' ), 
            array('2010-10-11 13:00:00','10-11-2010 13:00', 'm-d-Y', 'H:i' ), 
            array('2010-10-11 13:00:00','11-10-2010 13:00', 'd-m-Y', 'H:i' ), 
            array('2010-10-11 13:00:00','2010-10-11 13:00', 'Y-m-d', 'H:i' )                
        );   
    }
    
     /**
     * @dataProvider _providerEmailTemplateFormat
     */
	public function testEmailTemplateFormat($unformattedValue, $expectedValue, $dateFormat, $timeFormat)
	{
	    $GLOBALS['sugar_config']['default_date_format'] = $dateFormat;
		$GLOBALS['sugar_config']['default_time_format'] = $timeFormat;
        $GLOBALS['current_user']->setPreference('datef', $dateFormat);
		$GLOBALS['current_user']->setPreference('timef', $timeFormat);
		
   		$sfr = SugarFieldHandler::getSugarField('datetime');
    	$formattedValue = $sfr->getEmailTemplateValue($unformattedValue,array('type'=>'datetime'), array('notify_user' => $this->user));
    	
   	 	$this->assertSame($expectedValue, $formattedValue);
    }
}