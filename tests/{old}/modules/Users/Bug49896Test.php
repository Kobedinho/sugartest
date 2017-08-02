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

class Bug49896Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $_passwordSetting;
    var $_currentUser;

    public function setUp()
    {
        if(isset($GLOBALS['sugar_config']['passwordsetting']))
        {
            $this->_passwordSetting = $GLOBALS['sugar_config']['passwordsetting'];
        }
        $GLOBALS['sugar_config']['passwordsetting'] = array('onenumber'=>1,
                'onelower'=>1,
                'oneupper'=>1,
                'onespecial'=>1,
                'minpwdlength'=>6,
                'maxpwdlength'=>15);
        $this->_currentUser = SugarTestUserUtilities::createAnonymousUser(false);        
    }

    public function tearDown()
    {
        if(!empty($this->_passwordSetting))
        {
            $GLOBALS['sugar_config']['passwordsetting'] = $this->_passwordSetting;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testMinLength()
    {
        $result = $this->_currentUser->check_password_rules('Tes1!');
        $this->assertEquals(false, $result, 'Assert that min length rule is checked');
    }

    public function testMaxLength()
    {
        $result = $this->_currentUser->check_password_rules('Tester123456789!');
        $this->assertEquals(false, $result, 'Assert that max length rule is checked');
    }
        
    public function testOneNumber()
    {
        $result = $this->_currentUser->check_password_rules('Tester!');
        $this->assertEquals(false, $result, 'Assert that one number rule is checked');
    }

    public function testOneLower()
    {
        $result = $this->_currentUser->check_password_rules('TESTER1!');
        $this->assertEquals(false, $result, 'Assert that one lower rule is checked');
    }
    
    public function testOneUpper()
    {
        $result = $this->_currentUser->check_password_rules('tester1!');
        $this->assertEquals(false, $result, 'Assert that one upper rule is checked');
    } 
    
    public function testOneSpecial()
    {
        $result = $this->_currentUser->check_password_rules('Tester1');
        $this->assertEquals(false, $result, 'Assert that one special rule is checked');
    }  
    
    public function testCustomRegex()
    {
        $GLOBALS['sugar_config']['passwordsetting']['customregex'] = '/^T/';
        $result = $this->_currentUser->check_password_rules('tester1!');
        $this->assertEquals(false, $result, 'Assert that custom regex is checked');
    } 

    public function testAllCombinations()
    {
        $result = $this->_currentUser->check_password_rules('Tester1!');
        $this->assertEquals(true, $result, 'Assert that all rules are checked and passed');
    }    
}
?>
