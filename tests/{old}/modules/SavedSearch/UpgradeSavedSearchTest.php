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
 
class UpgradeSavedSearchTest extends Sugar_PHPUnit_Framework_TestCase 
{
    public function setUp() 
    {
        $GLOBALS['db']->query("INSERT INTO saved_search (team_id, team_set_id, id, name, search_module, deleted, date_entered, date_modified, assigned_user_id, contents) VALUES ('1', '1', 'f25fd18a-b72b-f466-014c-48a78f0eae69', '4.5.x Ollie Search', 'Contacts', 0, '2008-08-17 02:38:33', '2008-08-17 02:38:33', '1', 'YToyMzp7czoxMzoic2VhcmNoRm9ybVRhYiI7czoxMToic2F2ZWRfdmlld3MiO3M6NToicXVlcnkiO3M6NDoidHJ1ZSI7czoxMDoiZmlyc3RfbmFtZSI7czo1OiJPbGxpZSI7czo1OiJwaG9uZSI7czowOiIiO3M6OToibGFzdF9uYW1lIjtzOjA6IiI7czo1OiJlbWFpbCI7czowOiIiO3M6MTI6ImFjY291bnRfbmFtZSI7czowOiIiO3M6OToiYXNzaXN0YW50IjtzOjA6IiI7czoxMToibGVhZF9zb3VyY2UiO3M6MDoiIjtzOjE0OiJhZGRyZXNzX3N0cmVldCI7czowOiIiO3M6MTI6ImFkZHJlc3NfY2l0eSI7czowOiIiO3M6MTM6ImFkZHJlc3Nfc3RhdGUiO3M6MDoiIjtzOjE4OiJhZGRyZXNzX3Bvc3RhbGNvZGUiO3M6MDoiIjtzOjE1OiJhZGRyZXNzX2NvdW50cnkiO3M6MDoiIjtzOjE0OiJkaXNwbGF5Q29sdW1ucyI7czo3MDoiTkFNRXxUSVRMRXxBQ0NPVU5UX05BTUV8RU1BSUwxfFBIT05FX1dPUkt8VEVBTV9OQU1FfEFTU0lHTkVEX1VTRVJfTkFNRSI7czo4OiJoaWRlVGFicyI7czozMjc6IkRFUEFSVE1FTlR8RE9fTk9UX0NBTEx8UEhPTkVfSE9NRXxQSE9ORV9NT0JJTEV8UEhPTkVfT1RIRVJ8UEhPTkVfRkFYfEVNQUlMMnxFTUFJTF9PUFRfT1VUfFBSSU1BUllfQUREUkVTU19TVFJFRVR8UFJJTUFSWV9BRERSRVNTX0NJVFl8UFJJTUFSWV9BRERSRVNTX1NUQVRFfFBSSU1BUllfQUREUkVTU19QT1NUQUxDT0RFfEFMVF9BRERSRVNTX0NPVU5UUll8QUxUX0FERFJFU1NfU1RSRUVUfEFMVF9BRERSRVNTX0NJVFl8QUxUX0FERFJFU1NfU1RBVEV8QUxUX0FERFJFU1NfUE9TVEFMQ09ERXxEQVRFX0VOVEVSRUR8Q1JFQVRFRF9CWV9OQU1FfE1PRElGSUVEX1VTRVJfTkFNRSI7czoxMjoiZGlzcGxheV90YWJzIjthOjE6e2k6MDtzOjQ6Ik5BTUUiO31zOjc6Im9yZGVyQnkiO3M6NDoiTkFNRSI7czo5OiJzb3J0T3JkZXIiO3M6MzoiQVNDIjtzOjEzOiJzZWFyY2hfbW9kdWxlIjtzOjg6IkNvbnRhY3RzIjtzOjE5OiJzYXZlZF9zZWFyY2hfYWN0aW9uIjtzOjQ6InNhdmUiO3M6MTI6ImZyb21BZHZhbmNlZCI7czo0OiJ0cnVlIjtzOjg6ImFkdmFuY2VkIjtiOjE7fQ==')");
        $GLOBALS['db']->query("INSERT INTO saved_search (team_id, team_set_id, id, name, search_module, deleted, date_entered, date_modified, assigned_user_id, contents) VALUES ('1', '1', '62ca8b0a-3ccd-0aaf-a462-49906aa9b337', '5.0 East Team Search', 'Accounts', 0, '2009-02-09 09:09:09', '2009-02-09 09:09:09', '1', 'YTozMjp7czoxMzoic2VhcmNoRm9ybVRhYiI7czoxNToiYWR2YW5jZWRfc2VhcmNoIjtzOjU6InF1ZXJ5IjtzOjQ6InRydWUiO3M6MTM6Im5hbWVfYWR2YW5jZWQiO3M6MDoiIjtzOjE4OiJ0ZWFtX25hbWVfYWR2YW5jZWQiO3M6NDoiRWFzdCI7czoyMzoiYWRkcmVzc19zdHJlZXRfYWR2YW5jZWQiO3M6MDoiIjtzOjE0OiJwaG9uZV9hZHZhbmNlZCI7czowOiIiO3M6MTY6IndlYnNpdGVfYWR2YW5jZWQiO3M6MDoiIjtzOjIxOiJhZGRyZXNzX2NpdHlfYWR2YW5jZWQiO3M6MDoiIjtzOjE0OiJlbWFpbF9hZHZhbmNlZCI7czowOiIiO3M6MjM6ImFubnVhbF9yZXZlbnVlX2FkdmFuY2VkIjtzOjA6IiI7czoyMjoiYWRkcmVzc19zdGF0ZV9hZHZhbmNlZCI7czowOiIiO3M6MTg6ImVtcGxveWVlc19hZHZhbmNlZCI7czowOiIiO3M6Mjc6ImFkZHJlc3NfcG9zdGFsY29kZV9hZHZhbmNlZCI7czowOiIiO3M6MzI6ImJpbGxpbmdfYWRkcmVzc19jb3VudHJ5X2FkdmFuY2VkIjtzOjA6IiI7czoyMjoidGlja2VyX3N5bWJvbF9hZHZhbmNlZCI7czowOiIiO3M6MTc6InNpY19jb2RlX2FkdmFuY2VkIjtzOjA6IiI7czoxNToicmF0aW5nX2FkdmFuY2VkIjtzOjA6IiI7czoxODoib3duZXJzaGlwX2FkdmFuY2VkIjtzOjA6IiI7czo5OiJzaG93U1NESVYiO3M6MzoieWVzIjtzOjE0OiJkaXNwbGF5Q29sdW1ucyI7czo2NzoiTkFNRXxCSUxMSU5HX0FERFJFU1NfQ0lUWXxQSE9ORV9PRkZJQ0V8VEVBTV9OQU1FfEFTU0lHTkVEX1VTRVJfTkFNRSI7czo4OiJoaWRlVGFicyI7czozOTg6IkFDQ09VTlRfVFlQRXxJTkRVU1RSWXxBTk5VQUxfUkVWRU5VRXxQSE9ORV9GQVh8QklMTElOR19BRERSRVNTX1NUUkVFVHxCSUxMSU5HX0FERFJFU1NfU1RBVEV8QklMTElOR19BRERSRVNTX1BPU1RBTENPREV8QklMTElOR19BRERSRVNTX0NPVU5UUll8U0hJUFBJTkdfQUREUkVTU19TVFJFRVR8U0hJUFBJTkdfQUREUkVTU19DSVRZfFNISVBQSU5HX0FERFJFU1NfU1RBVEV8U0hJUFBJTkdfQUREUkVTU19QT1NUQUxDT0RFfFNISVBQSU5HX0FERFJFU1NfQ09VTlRSWXxSQVRJTkd8UEhPTkVfQUxURVJOQVRFfFdFQlNJVEV8T1dORVJTSElQfEVNUExPWUVFU3xTSUNfQ09ERXxUSUNLRVJfU1lNQk9MfERBVEVfTU9ESUZJRUR8REFURV9FTlRFUkVEfENSRUFURURfQllfTkFNRXxNT0RJRklFRF9CWV9OQU1FIjtzOjc6Im9yZGVyQnkiO3M6NDoiTkFNRSI7czo5OiJzb3J0T3JkZXIiO3M6MzoiQVNDIjtzOjEzOiJzZWFyY2hfbW9kdWxlIjtzOjg6IkFjY291bnRzIjtzOjE5OiJzYXZlZF9zZWFyY2hfYWN0aW9uIjtzOjQ6InNhdmUiO3M6MTQ6ImNrX2xvZ2luX2lkXzIwIjtzOjE6IjEiO3M6MTc6ImNrX2xvZ2luX3RoZW1lXzIwIjtzOjU6IlN1Z2FyIjtzOjIwOiJja19sb2dpbl9sYW5ndWFnZV8yMCI7czo1OiJlbl91cyI7czoxMToic2hvd0xlZnRDb2wiO3M6NDoidHJ1ZSI7czoxNDoicm9vdF9kaXJlY3RvcnkiO3M6MjI6IkM6XHhhbXBwXGh0ZG9jc1xzb2xhbmEiO3M6MTc6ImpzX3JlYnVpbGRfY29uY2F0IjtzOjc6InJlYnVpbGQiO3M6ODoiYWR2YW5jZWQiO2I6MTt9')");
        $GLOBALS['db']->query("INSERT INTO saved_search (team_id, team_set_id, id, name, search_module, deleted, date_entered, date_modified, assigned_user_id, contents) VALUES ('1', '1', '62ca8b0a-3ccd-0aaf-a462-49906aa9b338', '4.5.x Multiple Team Search', 'Accounts', 0, '2009-02-09 09:09:09', '2009-02-09 09:09:09', '1', 'YToyNzp7czoxMzoic2VhcmNoRm9ybVRhYiI7czoxMToic2F2ZWRfdmlld3MiO3M6NToicXVlcnkiO3M6NDoidHJ1ZSI7czo0OiJuYW1lIjtzOjA6IiI7czo1OiJwaG9uZSI7czowOiIiO3M6Nzoid2Vic2l0ZSI7czowOiIiO3M6NToiZW1haWwiO3M6MDoiIjtzOjE0OiJhbm51YWxfcmV2ZW51ZSI7czowOiIiO3M6OToiZW1wbG95ZWVzIjtzOjA6IiI7czo4OiJpbmR1c3RyeSI7YToxOntpOjA7czowOiIiO31zOjc6InRlYW1faWQiO2E6Mzp7aTowO3M6NDoiRWFzdCI7aToxO3M6MToiMSI7aToyO3M6NDoiV2VzdCI7fXM6MTI6ImFjY291bnRfdHlwZSI7YToxOntpOjA7czowOiIiO31zOjEzOiJ0aWNrZXJfc3ltYm9sIjtzOjA6IiI7czo2OiJyYXRpbmciO3M6MDoiIjtzOjg6InNpY19jb2RlIjtzOjA6IiI7czoxNDoiYWRkcmVzc19zdHJlZXQiO3M6MDoiIjtzOjEyOiJhZGRyZXNzX2NpdHkiO3M6MDoiIjtzOjEzOiJhZGRyZXNzX3N0YXRlIjtzOjA6IiI7czoxODoiYWRkcmVzc19wb3N0YWxjb2RlIjtzOjA6IiI7czoxNToiYWRkcmVzc19jb3VudHJ5IjtzOjA6IiI7czoxNDoiZGlzcGxheUNvbHVtbnMiO3M6Njc6Ik5BTUV8QklMTElOR19BRERSRVNTX0NJVFl8UEhPTkVfT0ZGSUNFfFRFQU1fTkFNRXxBU1NJR05FRF9VU0VSX05BTUUiO3M6ODoiaGlkZVRhYnMiO3M6NDAwOiJBQ0NPVU5UX1RZUEV8SU5EVVNUUll8QU5OVUFMX1JFVkVOVUV8UEhPTkVfRkFYfEJJTExJTkdfQUREUkVTU19TVFJFRVR8QklMTElOR19BRERSRVNTX1NUQVRFfEJJTExJTkdfQUREUkVTU19QT1NUQUxDT0RFfEJJTExJTkdfQUREUkVTU19DT1VOVFJZfFNISVBQSU5HX0FERFJFU1NfU1RSRUVUfFNISVBQSU5HX0FERFJFU1NfQ0lUWXxTSElQUElOR19BRERSRVNTX1NUQVRFfFNISVBQSU5HX0FERFJFU1NfUE9TVEFMQ09ERXxTSElQUElOR19BRERSRVNTX0NPVU5UUll8UkFUSU5HfFBIT05FX0FMVEVSTkFURXxXRUJTSVRFfE9XTkVSU0hJUHxFTVBMT1lFRVN8U0lDX0NPREV8VElDS0VSX1NZTUJPTHxEQVRFX01PRElGSUVEfERBVEVfRU5URVJFRHxDUkVBVEVEX0JZX05BTUV8TU9ESUZJRURfVVNFUl9OQU1FIjtzOjc6Im9yZGVyQnkiO3M6NDoiTkFNRSI7czo5OiJzb3J0T3JkZXIiO3M6MzoiQVNDIjtzOjEzOiJzZWFyY2hfbW9kdWxlIjtzOjg6IkFjY291bnRzIjtzOjE5OiJzYXZlZF9zZWFyY2hfYWN0aW9uIjtzOjY6InVwZGF0ZSI7czoxMjoiZnJvbUFkdmFuY2VkIjtzOjQ6InRydWUiO3M6ODoiYWR2YW5jZWQiO2I6MTt9')");
           
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
        $GLOBALS['current_user']->save();
    }
    
    public function tearDown() 
    {
        $GLOBALS['db']->query("DELETE FROM saved_search where id in ('f25fd18a-b72b-f466-014c-48a78f0eae69', '62ca8b0a-3ccd-0aaf-a462-49906aa9b337', '62ca8b0a-3ccd-0aaf-a462-49906aa9b338')");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testUpgradingAdvancedSearch() 
    {
        $x = new UpgradeSavedSearch();
        
	    $focus = new SavedSearch();
	    
		$focus->retrieve('f25fd18a-b72b-f466-014c-48a78f0eae69');
		$contents = unserialize(base64_decode($focus->contents));
		$this->assertEquals($contents['searchFormTab'],'advanced_search', "Check that the searchFormTab index has been created");
    }
    
    public function testUpgradingTeamSearch() 
    {
        $x = new UpgradeSavedSearch();
        
	    $focus = new SavedSearch();
	    
		$focus->retrieve('62ca8b0a-3ccd-0aaf-a462-49906aa9b337');
		$contents = unserialize(base64_decode($focus->contents));
		
		//Field was removed
		$this->assertTrue(!isset($contents['team_name_advance']), "Check that team_name_advanced key was removed from saved search contents");
		//Field was updated
		$this->assertTrue(isset($contents['id_team_name_advanced_collection_0']), "Check that id_team_name_advanced_collection_0 key exists");
		$this->assertEquals($contents['id_team_name_advanced_collection_0'], 'East', "Check that id_team_name_advanced_collection_0 has value of 'East'");  	
    }
}
