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
require_once 'tests/{old}/service/SOAPTestCase.php';
class CanGetUserListTest extends SOAPTestCase
{
    /**
     * Create test user
     *
     */
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();

        self::$_user->is_admin = 1;
        self::$_user->save();
        $GLOBALS['db']->commit();
    }

    public function testGetUserList()
    {
        // FIXME TY-1317: investigate why this test fails
        $this->_login();
        $result = $this->_soapClient->call('get_entry_list',
                                           array('session'=>$this->_sessionId,
                                                 "module_name" => 'Users',
                                                 "query" => "id='".self::$_user->id."'",
                                                 "order_by"=>"date_modified",
                                                 "offset"=>0,
                                                 "select_fields" => array('id'),
                                                 "max_results" => 10,
                                                 "deleted" => 0,
                                               ));
        

        $this->assertFalse(isset($result['error']['name']),"There is an error set, the error value is: ".$result['error']['name']);
        
        
    }
}
