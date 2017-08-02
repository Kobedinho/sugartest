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

class Bug35014Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $campaign_id;

	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $campaign = SugarTestCampaignUtilities::createCampaign();
        $this->campaign_id = $campaign->id;
	}

    public function tearDown()
    {
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testLeadCapture_ShortQueryString_ReturnsRedirectLocation()
    {
        $this->markTestIncomplete("Marking incomplete and notifying MAR team, as it fails in strict mode.");
        // SET GLOBAL PHP VARIABLES
        $_POST = array
        (
            'first_name' => 'Sadek',
            'last_name' => 'Baroudi',
            'campaign_id' => $this->campaign_id,
            'redirect_url' => 'http://www.sugarcrm.com/index.php',
            'assigned_user_id' => '1',
            'team_id' => '1',
            'team_set_id' => 'Global',
            'req_id' => 'last_name;',
        );

        $postString = '';
        foreach($_POST as $k => $v)
        {
            $postString .= "{$k}=".urlencode($v)."&";
        }
        $postString = rtrim($postString, "&");

        $ch = curl_init("{$GLOBALS['sugar_config']['site_url']}/index.php?entryPoint=WebToLeadCapture");
        curl_setopt($ch, CURLOPT_POST, count($_POST) + 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        ob_start();
        $return = curl_exec($ch);
        $output = ob_get_clean();

        $matches = array();
        preg_match('/form name="redirect"/', $output, $matches);
        $this->assertTrue(count($matches) == 0, "Output Should Not have a form - since we do not have a long get string");

        $matches = array();
        preg_match("/Location: .*/", $output, $matches);
        $this->assertTrue(count($matches) > 0, "Could not get the header information for the response");

        $location = '';
        if(count($matches) > 0){
            $location = str_replace("Location :", "", $matches[0]);
        }

        $query_string = substr($location, strpos($location, "?") + 1);
        $query_string_array = explode("&", $query_string);

        $post_compare_array = array();
        $expectedKeys = array('first_name', 'last_name', 'campaign_id', 'redirect_url', 'assigned_user_id', 'team_id', 'team_set_id', 'req_id');
        foreach($query_string_array as $key_val)
        {
            $key_val_array = explode("=", $key_val);
            if(in_array($key_val_array[0], $expectedKeys)) {
                $post_compare_array[$key_val_array[0]] = '' . urldecode($key_val_array[1]);
            }
        }

        $this->assertEquals($_POST, $post_compare_array, "The returned get location doesn't match that of the post passed in");
    }


    public function testLeadCapture_LongQueryString_ReturnsForm()
    {
        $this->markTestIncomplete("Marking incomplete and notifying MAR team, as it fails in strict mode.");
        // SET GLOBAL PHP VARIABLES
        $_POST = array
        (
            'first_name' => 'Sadek',
            'last_name' => 'Baroudi',
            'campaign_id' => $this->campaign_id,
            'redirect_url' => 'http://www.sugarcrm.com/index.php',
            'assigned_user_id' => 1,
            'team_id' => '1',
            'team_set_id' => 'Global',
            'req_id' => 'last_name;',
            'SuperLongGetVar' =>
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis',
        );


        $postString = '';
        foreach($_POST as $k => $v)
        {
            $postString .= "{$k}=".urlencode($v)."&";
        }
        $postString = rtrim($postString, "&");

        $ch = curl_init("{$GLOBALS['sugar_config']['site_url']}/index.php?entryPoint=WebToLeadCapture");
        curl_setopt($ch, CURLOPT_POST, count($_POST) + 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        ob_start();
        $return = curl_exec($ch);
        $output = ob_get_clean();

        $matches = array();
        preg_match("/Location: .*/", $output, $matches);
        $this->assertTrue(count($matches) == 0, "Should not have a Location redirect header - query string was not too long.");

        $matches = array();
        preg_match('/form name="redirect"/', $output, $matches);
        $this->assertTrue(count($matches) > 0, "Output Should have a form since we have a long get string");
    }
}
?>
