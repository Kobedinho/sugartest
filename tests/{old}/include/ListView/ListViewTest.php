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
 

class ListViewTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_lv = new ListView();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    public function tearDown()
    {
        unset($this->_lv);
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    	unset($GLOBALS['current_user']);
    	unset($GLOBALS['app_strings']);
    }

    public function sortOrderProvider()
    {
        // test data in order (request,session,subpaneldefs,default,expected return)
        return array (
            array('asc' ,'desc' ,'desc' ,'desc' ,'asc'),
            array('desc','asc'  ,'asc'  ,'asc'  ,'desc'),
            array(null  ,'asc'  ,'desc' ,'desc' ,'asc'),
            array(null  ,'desc' ,'asc'  ,'asc'  ,'desc'),
            array(null  ,null   ,'asc'  ,'desc' ,'asc'),
            array(null  ,null   ,'desc' ,'asc'  ,'desc'),
            array(null  ,null   ,null   ,'asc'  ,'asc'),
            array(null  ,null   ,null   ,'desc' ,'desc')
        ) ;
    }
    /**
     * @group bug48665
     * @dataProvider sortOrderProvider
     */
    public function testCalculateSortOrder($req,$sess,$subpdefs,$default,$expected)
    {
        $sortOrder = array(
            'request' => $req,
            'session' => $sess,
            'subpaneldefs' => $subpdefs,
            'default' => $default,
        );
        $actual = $this->_lv->calculateSortOrder($sortOrder);
        $this->assertEquals($expected, $actual, 'Sort order is wrong');
    }

}
