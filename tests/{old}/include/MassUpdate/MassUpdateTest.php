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
 
require_once 'include/dir_inc.php';

class MassUpdateTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		$GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 12300
     */
    public function testAdddateWorksWithMultiByteCharacters()
    {
        $mass = new MassUpdate();
        $displayname = "开始日期:";
        $varname = "date_start";
        
        $result = $mass->addDate($displayname , $varname);
        $pos_f = strrpos($result, $GLOBALS['app_strings']['LBL_MASSUPDATE_DATE']);
        $this->assertTrue((bool) $pos_f);
    }
    
    /**
     * @ticket 23900
     */
    public function testAddStatus() 
    {
        $mass = new MassUpdate();
        $options = array (
            '' => '',
            '10' => 'ten',
            '20' => 'twenty',
            '30' => 'thirty',
            );
        $result = $mass->addStatus('test_dom', 'test_dom', $options);
        preg_match_all('/value=[\'\"].*?[\'\"]/si', $result, $matches);
       /* $this->assertTrue(isset($matches));
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'"); */
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][1] == "value='__SugarMassUpdateClearField__'");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'");
        $this->assertTrue($matches[0][4] == "value='30'");       	
    }
    
    /**
     * @ticket 23900
     */
    public function testAddStatusMulti() 
    {
        $mass = new MassUpdate();
        $options = array (
            '10' => 'ten',
            '20' => 'twenty',
            '30' => 'thirty',
            );
        
        $result = $mass->addStatusMulti('test_dom', 'test_dom', $options);
        preg_match_all('/value=[\'\"].*?[\'\"]/si', $result, $matches);
        $this->assertTrue(isset($matches));
        /*$this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'"); */
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][1] == "value='__SugarMassUpdateClearField__'");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'");
        $this->assertTrue($matches[0][4] == "value='30'");       	
    }

    /**
     * @dataProvider setMassUpdateFielddefsProvider
     * @param $fielddefs
     * @param $module
     * @param $resultFieldDefs
     */
    public function testSetMassUpdateFielddefs($fielddefs, $module, $resultFieldDefs)
    {
        $result = MassUpdate::setMassUpdateFielddefs($fielddefs, $module);
        $this->assertEquals($resultFieldDefs, $result);
    }

    public function setMassUpdateFielddefsProvider(){
        return array(
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'readonly' => true)),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'readonly' => true, 'massupdate' => false))
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'calculated' => true, 'enforced' => true)),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'calculated' => true, 'enforced' => true, 'massupdate' => false))
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'calculated' => true, 'enforced' => false)),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'calculated' => true, 'enforced' => false, 'massupdate' => true))
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => true)),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => 'true')),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => 1)),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => 0)),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => false)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => 'false')),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool', 'massupdate' => false)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'date_modified')),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'date_modified')
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'contact_id')),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'contact_id', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'assigned_user_name')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'assigned_user_name', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'account_id')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'account_id', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'account_name')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'account_name', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'bool')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'parent')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'parent', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'enum')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'enum', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'multienum')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'multienum', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'radioenum')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'radioenum', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'datetime')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'datetime', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'date')),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'date', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'int', 'massupdate' => true)),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'int', 'massupdate' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'int', 'auto_increment' => true)),
                    'Foo',
                    array('test' => array(
                        'name' => 'foofield', 'type' => 'int', 'auto_increment' => true)
                ),
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'relate')),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'relate'))
            ),
            array(
                array('test' => array(
                    'name' => 'foofield', 'type' => 'relate', 'id_name' => 'bar')),
                'Foo',
                array('test' => array(
                    'name' => 'foofield', 'type' => 'relate', 'id_name' => 'bar', 'massupdate' => true))
            ),
            array(
                array('sync_contact' => array()),
                'Contacts',
                array('sync_contact' => array(
                    'massupdate' => true)
                ),
            ),
            array(
                array('employee_status' => array()),
                'Employees',
                array('employee_status' => array(
                    'massupdate' => true, 'type' => 'enum', 'options' => 'employee_status_dom')
                ),
            ),
            array(
                array('status' => array()),
                'InboundEmail',
                array('status' => array(
                    'massupdate' => true, 'type' => 'enum', 'options' => 'user_status_dom')
                ),
            ),

        );
    }
}
