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


class CustomQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    static public function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    static public function tearDownAfterClass()
    {
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    	unset($GLOBALS['current_user']);
    	unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        $contact = new Contact();
        $this->defs = $contact->field_defs;
    }

	public static function query_func($ret_array, $fielddef)
	{
	    $ret_array['select'] .= ", 2+2 four /* for {$fielddef['name']} */";
	    return $ret_array;
	}

    public function testCustomQuery()
    {
        $bean = new Contact();
        $bean->field_defs = $this->defs;
        $bean->field_defs['testquery'] = array(
          "name" => "testquery",
          "source" => "non-db",
          'type' => "custom_query",
          "query_function" => array(
                        'function_name'=>'query_func',
                        'function_class'=>get_class($this),
          ),
          'reportable'=>false,
          'duplicate_merge'=>'disabled',
      );
          $result = $bean->create_new_list_query('', '');
          $this->assertContains("2+2 four /* for testquery */", $result);
    }

    public function testCustomQueryForced()
    {
        $bean = new Contact();
        $bean->field_defs = $this->defs;
        $bean->field_defs['testquery'] = array(
          "name" => "testquery",
          "source" => "non-db",
          'type' => "custom_query",
          "query_function" => array(
                        'function_name'=>'query_func',
                        'function_class'=>get_class($this),
          ),
          'reportable'=>false,
          'duplicate_merge'=>'disabled',
          );
        $result = $bean->create_new_list_query('', '', array('id', 'name'));
        $this->assertNotContains("2+2 four /* for testquery */", $result);

        $bean->field_defs['testquery']['force_exists'] = true;
        $result = $bean->create_new_list_query('', '', array('id', 'name'));
        $this->assertContains("2+2 four /* for testquery */", $result);
    }
}