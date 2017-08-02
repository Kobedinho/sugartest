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
 * Bug50780 - Due to the recent security fixes for the web services, subqueries on tables no longer work as expected.
 * This has presented a problem to some of our partner's since they were relying on the subqueries to return
 * relationship information on records. We could resolve this by adding back more tables to the allowed list of tables
 * to be queried in a subquery, but this has been voted against from most of the engineering staff. Another approach
 * would be to add enhancements to our API to allow for querying limited result sets for relationship data. Adding
 * offset and limit support to the get_relationships call is one such approach.
 */

require_once('vendor/nusoap//nusoap.php');
require_once 'tests/{old}/service/SOAPTestCase.php';

class Bug50780Test extends SOAPTestCase
{
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::setUp();
        $this->_login(); // Logging in just before the SOAP call as this will also commit any pending DB changes


        for ($x = 0; $x < 4; $x++) {
            $mid = SugarTestMeetingUtilities::createMeeting();
            SugarTestMeetingUtilities::addMeetingUserRelation($mid->id, self::$_user->id);
        }

        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingUsers();

        parent::tearDown();
    }


    public function testGetRelationshipReturnAllMeetings()
    {
        $result = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 0,
                'limit' => false)
        );

        $this->assertEquals(4, count($result['entry_list']));
    }

    public function testGetRelationshipReturnNothingWithOffsetSetHigh()
    {
        $result = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 5,
                'limit' => 4)
        );

        $this->assertEquals(0, count($result['entry_list']));
    }

    public function testGetRelationshipReturnThirdMeeting()
    {
        $result = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 2,
                'limit' => 1)
        );

        $this->assertEquals(1, count($result['entry_list']));
    }

    public function testGetRelationshipOffsetDoesntReturnSameRecords()
    {
        $result1 = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name', 'date_entered'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 0,
                'limit' => 2)
        );

        $this->assertEquals(2, count($result1['entry_list']));

        $result2 = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name', 'date_entered'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 2,
                'limit' => 2)
        );

        $this->assertEquals(2, count($result2['entry_list']));
    }
}