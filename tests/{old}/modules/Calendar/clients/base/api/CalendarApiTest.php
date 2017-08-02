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
 * @group api
 * @group calendar
 */
class CalendarApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api, $calendarApi;

    public function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user']->getSystemUser();
        $GLOBALS['current_user'] = $this->api->user;

        $this->calendarApi = new CalendarApi();
    }

    public function testBuildSearchParams_ConvertsRestArgsToLegacyParams()
    {
        $this->calendarApi = new CalendarApiTest_CalendarApi();

        $args = array(
            'q' => 'woo',
            'module_list' => 'Foo,Bar',
            'search_fields' => 'foo_search_field,bar_search_field',
            'fields' => 'foo_field,bar_field',
        );

        $expectedParams = array(
            array(
                'modules' => array('Foo', 'Bar'),
                'group' => 'or',
                'field_list' => array(
                    'foo_field',
                    'bar_field',
                    'foo_search_field',
                    'bar_search_field',
                ),
                'conditions' => array(
                    array(
                        'name' => 'foo_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ),
                    array(
                        'name' => 'bar_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ),
                ),
            ),
        );

        $this->assertEquals(
            $expectedParams,
            $this->calendarApi->publicBuildSearchParams($args),
            'Rest API args should be transformed correctly into legacy query params'
        );
    }

    public function testTransformInvitees_ConvertsLegacyResultsToUnifiedSearchForm()
    {
        $this->calendarApi = new CalendarApiTest_CalendarApi();
        $args = array(
            'q' => 'bar',
            'fields' => 'first_name,last_name,email,account_name',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $bean = new SugarBean(); //dummy, mocking out formatBean anyway
        $formattedBean = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'foo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $this->calendarApi = $this->createPartialMock(
            'CalendarApiTest_CalendarApi',
            array('formatBean')
        );
        $this->calendarApi->expects($this->once())
            ->method('formatBean')
            ->will($this->returnValue($formattedBean));

        $searchResults = array(
            'result' => array(
                'list' => array(
                    array('bean' => $bean)
                ),
            ),
        );

        $expectedInvitee = array_merge($formattedBean, array(
            '_search' => array(
                'highlighted' => array(
                    'last_name' => array(
                        'text' => 'Bar',
                        'module' => 'Contacts',
                        'label' => 'LBL_LAST_NAME',
                    ),
                ),
            ),
        ));

        $expectedInvitees = array(
            'next_offset' => -1,
            'records' => array(
                $expectedInvitee
            ),
        );;

        $this->assertEquals(
            $expectedInvitees,
            $this->calendarApi->publicTransformInvitees($this->api, $args, $searchResults),
            'Legacy search results should be transformed correctly into unified search format'
        );
    }

    public function testGetMatchedFields_MatchesRegularFieldsCorrectly()
    {
        $this->calendarApi = new CalendarApiTest_CalendarApi();

        $args = array(
            'q' => 'foo',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $record = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'woo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $expectedMatchedFields = array(
            'first_name' => array(
                'text' => 'Foo',
                'module' => 'Contacts',
                'label' => 'LBL_FIRST_NAME',
            ),
        );

        $this->assertEquals(
            $expectedMatchedFields,
            $this->calendarApi->publicGetMatchedFields($args, $record, 1),
            'Should match search query to field containing search text'
        );
    }

    public function testGetMatchedFields_MatchesEmailFieldCorrectly()
    {
        $this->calendarApi = new CalendarApiTest_CalendarApi();

        $args = array(
            'q' => 'woo',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $record = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'woo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $expectedMatchedFields = array(
            'email' => array(
                'text' => 'woo@baz.com',
                'module' => 'Contacts',
                'label' => 'LBL_ANY_EMAIL',
            ),
        );

        $this->assertEquals(
            $expectedMatchedFields,
            $this->calendarApi->publicGetMatchedFields($args, $record, 1),
            'Should match search query to field containing search text'
        );
    }
}

class CalendarApiTest_CalendarApi extends CalendarApi
{
    public function publicBuildSearchParams($args)
    {
        return $this->buildSearchParams($args);
    }

    public function publicTransformInvitees($api, $args, $searchResults)
    {
        return $this->transformInvitees($api, $args, $searchResults);
    }

    public function publicGetMatchedFields($args, $record, $maxFields)
    {
        return $this->getMatchedFields($args, $record, $maxFields);
    }
}

