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

// need to make sure SugarApi is included when extending DuplicateCheckStrategy to avoid a fatal error
require_once("tests/{old}/SugarTestRestUtilities.php");
/**
 * @group api
 * @group duplicatecheck
 */
class DuplicateCheckApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $copyOfLeadsDuplicateCheckVarDef,
            $mockLeadsDuplicateCheckVarDef = array(
        'enabled' => true,
        'FilterDuplicateCheck' => array(
            'filter_template' => array(
                array(
                    '$and' => array(
                        array(
                            '$or' => array(
                                array(
                                    'status' => array(
                                        '$not_equals' => 'Converted',
                                    ),
                                ),
                                array(
                                    'status' => array(
                                        '$is_null' => '',
                                    ),
                                ),
                            ),
                        ),
                        array(
                            '$or' => array(
                                array(
                                    '$and' => array(
                                        array(
                                            'first_name' => array(
                                                '$starts' => '$first_name',
                                            ),
                                        ),
                                        array(
                                            'last_name' => array(
                                                '$starts' => '$last_name',
                                            ),
                                        ),
                                    ),
                                ),
                                array(
                                    'phone_work' => array(
                                        '$equals' => '$phone_work',
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'account_name' => array(
                                '$equals' => '$account_name',
                            ),
                        ),
                    ),
                ),
            ),
            'ranking_fields'  => array(
                array(
                    'in_field_name'   => 'last_name',
                    'dupe_field_name' => 'last_name',
                ),
                array(
                    'in_field_name'   => 'first_name',
                    'dupe_field_name' => 'first_name',
                ),
            ),
        ),
    );

    private $api,
            $duplicateCheckApi,
            $convertedLead,
            $newLead,
            $newLead2,
            $newLeadFirstName  = "SugarLeadNewFirst",
            $newLeadLastName   = "SugarLeadLast",
            $newLead2FirstName = "SugarLeadNewFirst2", // different first name
            $newLead2LastName  = "SugarLeadLast"; // same last name

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('ACLStatic');

        $this->copyOfLeadsDuplicateCheckVarDef = $GLOBALS["dictionary"]["Lead"]["duplicate_check"];
        $GLOBALS["dictionary"]["Lead"]["duplicate_check"] = $this->mockLeadsDuplicateCheckVarDef;

        $GLOBALS["current_user"] = SugarTestUserUtilities::createAnonymousUser();

        $this->api               = SugarTestRestUtilities::getRestServiceMock();
        $this->duplicateCheckApi = new DuplicateCheckApi();

        //make sure any left over test leads from failed tests are removed
        $GLOBALS['db']->query('DELETE FROM leads WHERE last_name LIKE (\'SugarLead%\')');

        //create test leads
        $this->convertedLead             = SugarTestLeadUtilities::createLead();
        $this->convertedLead->first_name = 'SugarLeadConvertFirst';
        $this->convertedLead->last_name  = 'SugarLeadLast';
        $this->convertedLead->status     = 'Converted';
        $this->convertedLead->save();

        $this->newLead             = SugarTestLeadUtilities::createLead();
        $this->newLead->first_name = $this->newLeadFirstName;
        $this->newLead->last_name  = $this->newLeadLastName;
        $this->newLead->save();

        $this->newLead2             = SugarTestLeadUtilities::createLead();
        $this->newLead2->first_name = $this->newLead2FirstName;
        $this->newLead2->last_name  = $this->newLead2LastName;
        $this->newLead2->status     = 'New'; // non-empty, non-Converted status
        $this->newLead2->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        $GLOBALS["dictionary"]["Lead"]["duplicate_check"] = $this->copyOfLeadsDuplicateCheckVarDef;
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @dataProvider duplicatesProvider
     */
    public function testCheckForDuplicates($args, $expected, $message)
    {
        $args["module"] = "Leads";
        $results = $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
        $actual  = count($results["records"]);
        self::assertEquals($expected, $actual, $message);
    }

    public function duplicatesProvider() {
        return array(
            array(
                array(
                    "first_name" => $this->newLeadFirstName,
                    "last_name"  => $this->newLeadLastName,
                ),
                2,
                "Two fields passed in; should match two Leads",
            ),
            array(
                array(
                    "first_name" => $this->newLead2FirstName,
                    "last_name"  => $this->newLead2LastName,
                ),
                1,
                "Two fields passed in; should match one Lead",
            ),
            array(
                array(
                    "first_name" => "",
                    "last_name"  => $this->newLeadLastName,
                ),
                2,
                "One of the two fields passed in is blank; should match two Leads",
            ),
            array(
                array(
                    "last_name" => $this->newLeadLastName,
                ),
                2,
                "Filter omits 'first_name' since field is not passed in; should match two Leads",
            ),
            array(
                array(
                    "last_name" => 'DO NOT MATCH ANY LAST NAMES',
                ),
                0,
                "No duplicate matches, should returns 0 results",
            ),
        );
    }

    public function testCheckForDuplicates_AllFilterArgumentsAreEmpty_ReturnsEmptyResultSet() {
        $GLOBALS["dictionary"]["Lead"]["duplicate_check"] = array(
            'FilterDuplicateCheck' => array(
                'filter_template' => array(
                    array(
                        'last_name' => array(
                            '$starts' => '$last_name',
                        ),
                    )
                )
            ),
        );

        $args = array(
            'module' => 'Leads',
            'last_name' => ''
        );
        $results = $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
        self::assertEquals(array(), $results, 'When all arguments expected by the filter are empty, no records should be returned');
    }

    public function testCheckForDuplicates_EmptyBean()
    {
        $args = array(
            "module" => "FooModule"
        );

        $this->setExpectedException('SugarApiExceptionInvalidParameter');
        $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
    }

    public function testCheckForDuplicates_NotAuthorized()
    {
        $args = array(
            "module" => "Leads"
        );
        //Setting access to be denied for read
        $acldata['module']['access']['aclaccess'] = ACL_ALLOW_DISABLED;
        ACLAction::setACLData($GLOBALS['current_user']->id, $args['module'], $acldata);
        // reset cached ACLs
        SugarACL::$acls = array();

        $this->setExpectedException('SugarApiExceptionNotAuthorized');
        $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
    }

    public function testCheckForDuplicates_InvalidParameter()
    {
        $args = array(
            "module" => "Leads"
        );

        $this->setExpectedException('SugarApiExceptionInvalidParameter');
        $duplicateCheckApi = $this->createPartialMock('DuplicateCheckApi', array('populateFromApi'));
        $duplicateCheckApi->expects($this->any())
                          ->method('populateFromApi')
                          ->will($this->returnValue(array()));
        $duplicateCheckApi->checkForDuplicates($this->api, $args);
    }
}
