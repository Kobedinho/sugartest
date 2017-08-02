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
 * @group ApiTests
 */
class CurrentUserPortalApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var CurrentUserPortalApi
     */
    public $currentUserApi;

    /**
     * @var Contact
     */
    public $contact;

    /**
     * @var ServiceBase
     */
    public $service;

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->contact = SugarTestContactUtilities::createContact(
            '',
            array(
                'first_name' => 'testfirst',
                'last_name' => 'testlast',
                'picture' => 'testpicture',
                'portal_active' => 1,
                'portal_name' => 'testportal',
                'disable_custom_fields' => true,
            )
        );

        $_SESSION['contact_id'] = $this->contact->id;

        $this->currentUserApi= new CurrentUserPortalApi();
        $this->currentUserApi->portal_contact = $this->contact;
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        $this->service = null;
        $this->currentUserApi = null;
        $this->contact = null;

        unset($_SESSION['contact_id']);

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();

        parent::tearDown();
    }

    /**
     * Test api method retrieveCurrentUser
     */
    public function testRetrieveCurrentUser()
    {
        $result = $this->currentUserApi->retrieveCurrentUser($this->service, array());

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('current_user', $result);
        $this->assertInternalType('array', $result['current_user']);
        $this->assertArrayHasKey('preferences', $result['current_user']);
        $this->assertArrayHasKey('module_list', $result['current_user']);
        $this->assertArrayHasKey('type', $result['current_user']);
        $this->assertArrayHasKey('user_id', $result['current_user']);
        $this->assertArrayHasKey('user_name', $result['current_user']);
        $this->assertArrayHasKey('id', $result['current_user']);
        $this->assertArrayHasKey('full_name', $result['current_user']);
        $this->assertArrayHasKey('picture', $result['current_user']);
        $this->assertArrayHasKey('portal_name', $result['current_user']);
    }

    /**
     * @return array
     */
    public function updateCurrentUserDataProvider()
    {
        return array(
            // portal name
            array(
                array(
                    'portal_name' => 'TEST_ME',
                ),
                array(
                    'portal_name' => 'TEST_ME',
                ),
            ),
            // first/last names
            array(
                array(
                    'first_name' => 'test first name',
                    'last_name' => 'test last name',
                ),
                array(
                    'full_name' => 'test first name test last name',
                ),
            ),
        );
    }

    /**
     * Test api method updateCurrentUser
     *
     * @dataProvider updateCurrentUserDataProvider
     */
    public function testUpdateCurrentUser(array $args, array $expected)
    {
        // FIXME TY-1321: figure out why this test fails
        $result = $this->currentUserApi->updateCurrentUser($this->service, $args);

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('current_user', $result);
        $this->assertInternalType('array', $result['current_user']);
        $this->assertArrayHasKey('preferences', $result['current_user']);
        $this->assertArrayHasKey('module_list', $result['current_user']);
        $this->assertArrayHasKey('type', $result['current_user']);
        $this->assertArrayHasKey('user_id', $result['current_user']);
        $this->assertArrayHasKey('user_name', $result['current_user']);
        $this->assertArrayHasKey('id', $result['current_user']);
        $this->assertArrayHasKey('full_name', $result['current_user']);
        $this->assertArrayHasKey('picture', $result['current_user']);
        $this->assertArrayHasKey('portal_name', $result['current_user']);

        foreach ($expected as $k => $v) {
            $this->assertArrayHasKey($k, $result['current_user']);
            $this->assertEquals($v, $result['current_user'][$k]);
        }
    }

    /**
     * Tests current user for using picture from contact
     */
    public function testContactPicture()
    {
        $result = $this->currentUserApi->retrieveCurrentUser($this->service, array());

        $this->assertArrayHasKey('picture', $result['current_user']);
        $this->assertNotEmpty($result['current_user']['picture']);
        $this->assertEquals('testpicture', $result['current_user']['picture']);
    }
}
