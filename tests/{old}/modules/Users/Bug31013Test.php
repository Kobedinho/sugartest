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
 * @ticket 31013
 */
class Bug31013Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;

	public function setUp() 
    {
    	$this->_user = SugarTestUserUtilities::createAnonymousUser(false);
    	$this->_user->portal_only = true;
    	$this->_user->save();
	}

	public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}

	public function testPrivateTeamForPortalUserNotCreated() 
    {
    	$result = $GLOBALS['db']->query("SELECT count(*) AS TOTAL FROM teams WHERE associated_user_id = '{$this->_user->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertTrue(empty($row['TOTAL']), "Assert that the private team was not created for portal user");
    }

}

