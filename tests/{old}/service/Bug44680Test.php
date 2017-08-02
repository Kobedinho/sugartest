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

/**
 * @group bug44680
 */
class Bug44680Test extends SOAPTestCase
{
    var $testUser;
	var $testAccount;
	var $teamSet;
    var $testTeam;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
		parent::setUp();
        $this->testUser = SugarTestUserUtilities::createAnonymousUser();
		$GLOBALS['current_user'] = $this->testUser;
		$this->testAccount = SugarTestAccountUtilities::createAccount();

        $this->testTeam = SugarTestTeamUtilities::createAnonymousTeam();

        $this->teamSet = BeanFactory::newBean('TeamSets');
        $this->teamSet->addTeams(array($this->testTeam->id, $this->testUser->getPrivateTeamID()));


		$this->testAccount->team_id = $this->testUser->getPrivateTeamID();
		$this->testAccount->team_set_id = $this->teamSet->id;
		$this->testAccount->assigned_user_id = $this->testUser->id;
		$this->testAccount->save();
        $GLOBALS['db']->commit();
    }

    public function  tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        parent::tearDown();
    }

    public function testSetEntryHasAccess()
    {
        $time = mt_rand();
        $oldName = $this->testAccount->name;
        $result = $this->_login();

        $result = $this->_soapClient->call('set_entry',array('session'=> $this->_sessionId,'module_name'=>'Accounts', 'name_value_list'=>array(array('name'=>'id' , 'value'=>$this->testAccount->id),array('name'=>'name' , 'value'=>"$time Account SINGLE"))));

        $this->assertEquals($this->testAccount->id, $result['id'], "Did not update the Account as expected.");
    }

    public function testSetEntryNoAccess()
    {
        $teamSet = BeanFactory::newBean('TeamSets');
        $teamSet->addTeams(array($this->testTeam->id));
        $this->testAccount->team_id = $this->testTeam->id;
		$this->testAccount->team_set_id = $teamSet->id;
		$this->testAccount->assigned_user_id = '1';
		$this->testAccount->save();

        $this->testTeam->remove_user_from_team($this->testUser->id);

        $time = mt_rand();
        $oldName = $this->testAccount->name;
        $this->_login();
        $result = $this->_soapClient->call('set_entry',array('session'=> $this->_sessionId,'module_name'=>'Accounts', 'name_value_list'=>array(array('name'=>'id' , 'value'=>$this->testAccount->id),array('name'=>'name' , 'value'=>"$time Account SINGLE"))));
        $this->assertEquals(-1, $result['id'], "Should not have updated the Account.");
    }
}
