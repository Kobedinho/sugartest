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

require_once "include/SearchForm/SearchForm2.php";

class Bug44858Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        //$this->useOutputBuffering = true;
	}

	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
	}
    
    /**
     * @ticket 44858
     */
    public function testGeneratedWhereClauseDoesNotHaveValueOfFieldNotSetInSearchForm()
    {
        //test to check that if value of a dropdown field is already set in REQUEST object (from any form such as mass update form instead of search form)
        //i.e. search is made on empty string, but REQUEST object gets value of that dropdown field from some other form on the same page
        //then on clicking serach button, value of that field should not be used as filter in where clause
        $this->markTestIncomplete('This test should actually check that the $whereArray is indeed populated');
        return;
        
    	//array to simulate REQUEST object
    	$requestArray['module'] = 'Accounts';
    	$requestArray['action'] = 'index';
    	$requestArray['searchFormTab'] = 'basic_search';
    	$requestArray['account_type'] = 'Analyst'; //value of a dropdown field set in REQUEST object
    	$requestArray['query'] = 'true';
    	$requestArray['button']  = 'Search';
    	$requestArray['globalLinksOpen']='true';
    	$requestArray['current_user_only_basic'] = 0;
    	
    	$account = SugarTestAccountUtilities::createAccount();
    	$searchForm = new SearchForm($account,'Accounts');
    	
    	require 'modules/Accounts/vardefs.php';
    	require 'modules/Accounts/metadata/SearchFields.php';
    	require 'modules/Accounts/metadata/searchdefs.php';
        $searchForm->searchFields = $searchFields[$searchForm->module]; 
        $searchForm->searchdefs = $searchdefs[$searchForm->module];                          
    	$searchForm->populateFromArray($requestArray,'basic_search',false);
    	$whereArray = $searchForm->generateSearchWhere(true, $account->module_dir);
    	//echo var_export($whereArray, true);
    	$this->assertEquals(0, count($whereArray));

    }
}
