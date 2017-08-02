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

class UserTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected $_user = null;

    public function setUp()
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testSettingAUserPreference()
    {
        $this->_user->setPreference('test_pref', 'dog');

        $this->assertEquals('dog', $this->_user->getPreference('test_pref'));
    }

    public function testRemoveAUserPreference()
    {
        $this->_user->setPreference('test_pref2', 'DeleteThis');

        $this->assertEquals('DeleteThis', $this->_user->getPreference('test_pref2'));

        $this->_user->removePreference('test_pref2');

        $this->assertEmpty($this->_user->getPreference('test_pref2'));
    }

    public function testGettingSystemPreferenceWhenNoUserPreferenceExists()
    {
        $GLOBALS['sugar_config']['somewhackypreference'] = 'somewhackyvalue';

        $result = $this->_user->getPreference('somewhackypreference');

        unset($GLOBALS['sugar_config']['somewhackypreference']);

        $this->assertEquals('somewhackyvalue', $result);
    }

    /**
     * @ticket 42667
     */
    public function testGettingSystemPreferenceWhenNoUserPreferenceExistsForEmailDefaultClient()
    {
        if (isset($GLOBALS['sugar_config']['email_default_client'])) {
            $oldvalue = $GLOBALS['sugar_config']['email_default_client'];
        }
        $GLOBALS['sugar_config']['email_default_client'] = 'somewhackyvalue';

        $result = $this->_user->getPreference('email_link_type');

        if (isset($oldvalue)) {
            $GLOBALS['sugar_config']['email_default_client'] = $oldvalue;
        } else {
            unset($GLOBALS['sugar_config']['email_default_client']);
        }

        $this->assertEquals('somewhackyvalue', $result);
    }

    public function testResetingUserPreferences()
    {
        $this->_user->setPreference('test_pref', 'dog');

        $this->_user->resetPreferences();

        $this->assertNull($this->_user->getPreference('test_pref'));
    }

    /**
     * @ticket 36657
     */
    public function testCertainPrefsAreNotResetWhenResetingUserPreferences()
    {
        $this->_user->setPreference('ut', '1');
        $this->_user->setPreference('timezone', 'GMT');

        $this->_user->resetPreferences();

        $this->assertEquals('1', $this->_user->getPreference('ut'));
        $this->assertEquals('GMT', $this->_user->getPreference('timezone'));
    }

    public function testSavingToMultipleUserPreferenceCategories()
    {
        $this->_user->setPreference('test_pref1', 'dog', 0, 'cat1');
        $this->_user->setPreference('test_pref2', 'dog', 0, 'cat2');

        $this->_user->savePreferencesToDB();

        $this->assertEquals(
            'cat1',
            $GLOBALS['db']->getOne(
                "SELECT category FROM user_preferences WHERE assigned_user_id = '{$this->_user->id}' AND category = 'cat1'"
            )
        );

        $this->assertEquals(
            'cat2',
            $GLOBALS['db']->getOne(
                "SELECT category FROM user_preferences WHERE assigned_user_id = '{$this->_user->id}' AND category = 'cat2'"
            )
        );
    }

    public function testGetReporteesWithLeafCount()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $subManager2->id;
        $rep3->save();

        //get leaf arrays
        $managerReportees = User::getReporteesWithLeafCount($manager->id);
        $subManager1Reportees = User::getReporteesWithLeafCount($subManager1->id);

        //check normal scenario
        $this->assertEquals("1", $managerReportees[$subManager1->id], "SubManager leaf count did not match");
        $this->assertEquals("0", $subManager1Reportees[$rep1->id], "Rep leaf count did not match");

        //now delete one so we can check the delete code.
        $rep1->mark_deleted($rep1->id);
        $rep1->save();

        //first w/o deleted rows
        $managerReportees = User::getReporteesWithLeafCount($manager->id);
        $this->assertEquals(
            "0",
            $managerReportees[$subManager1->id],
            "SubManager leaf count did not match after delete"
        );
        //now with deleted rows
        $managerReportees = User::getReporteesWithLeafCount($manager->id, true);
        $this->assertEquals("1", $managerReportees[$subManager1->id], "SubManager leaf count did not match");
    }

    /**
     * @group user
     */
    public function testGetReporteesWithLeafCountWithAdditionalFields()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $subManager2->id;
        $rep3->save();

        //get leaf arrays
        $managerReportees = User::getReporteesWithLeafCount($manager->id, false, ['first_name']);

        //check normal scenario
        $this->assertEquals("1", $managerReportees[$subManager1->id]['total'], "SubManager leaf count did not match");
        $this->assertEquals($subManager1->first_name, $managerReportees[$subManager1->id]['first_name']);
    }

    public function testGetReporteeManagers()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $manager->id;
        $rep3->save();

        $managers = User::getReporteeManagers($manager->id);
        $this->assertEquals("2", count($managers), "Submanager count did not match");
    }

    public function testGetReporteeReps()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();
        $rep4 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $manager->id;
        $rep2->save();
        $rep3->reports_to_id = $manager->id;
        $rep3->save();

        $rep4->status = 'Inactive';
        $rep4->reports_to_id = $manager->id;
        $rep4->save();

        $reps = User::getReporteeReps($manager->id);
        $this->assertEquals("2", count($reps), "Rep count did not match");
    }

    public function datProviderForTestGetEmailClientPreference()
    {
        return array(
            array("sugar", "foo", "sugar"),
            array("", "foo", "foo"),
        );
    }

    /**
     * @dataProvider datProviderForTestGetEmailClientPreference
     */
    public function testGetEmailClientPreference($emailLinkType, $emailDefaultClient, $expected)
    {
        $oc = $this->backUpConfig("email_default_client"); // original client
        $op = $this->_user->getPreference("email_link_type"); // original preference
        $os = $this->backUpSession("isMobile"); // original session

        $GLOBALS['sugar_config']['email_default_client'] = $emailDefaultClient;
        $this->_user->setPreference("email_link_type", $emailLinkType);
        unset($_SESSION["isMobile"]);

        $actual = $this->_user->getEmailClientPreference();
        $this->assertEquals($expected, $actual);

        $this->restoreConfig("email_default_client", $oc);
        $this->_user->setPreference("email_link_type", $op);
        $this->restoreSession("isMobile", $os);
    }

    public function testGetEmailClientPreference_SessionIsMobile()
    {
        $oc = $this->backUpConfig("email_default_client"); // original client
        $op = $this->_user->getPreference("email_link_type"); // original preference
        $os = $this->backUpSession("isMobile"); // original session

        $GLOBALS['sugar_config']['email_default_client'] = "sugar";
        $this->_user->setPreference("email_link_type", "sugar");
        $_SESSION["isMobile"] = true;

        $expected = "other";
        $actual   = $this->_user->getEmailClientPreference();
        $this->assertEquals($expected, $actual, "Should have returned {$expected} when the session is mobile and PRO+");


        $this->restoreConfig("email_default_client", $oc);
        $this->_user->setPreference("email_link_type", $op);
        $this->restoreSession("isMobile", $os);
    }

    public function testPrimaryEmailShouldBeCaseInsensitive()
    {
        $this->_user->email1 = 'example@example.com';
        $this->assertTrue($this->_user->isPrimaryEmail('EXAMPLE@example.com'));
    }

    public function testUserPictureIsEmptyWhenItDoesntExist()
    {
        $this->_user->picture = 'thisdoesntexist';
        $this->_user->save();

        $tuser = $this->_user->retrieve($this->_user->id);

        $this->assertEmpty($tuser->picture);
    }

    public function testUserPictureIsSetWhenFileExists()
    {
        touch('upload/test_user_picture');
        $this->_user->picture = 'test_user_picture';
        $this->_user->save();

        $tuser = $this->_user->retrieve($this->_user->id);

        $this->assertEquals('test_user_picture', $tuser->picture);

        unlink('upload/test_user_picture');
    }

    public function testSetUserTimezonePreference_GetUserTimeZone_CorrectTimezoneReturned()
    {
        $dateTime = new SugarDateTime('2015-01-01 12:00:00', new DateTimeZone('UTC'));
        $offsetGMT = $dateTime->getOffset() / 60;

        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->setPreference("timezone", "America/New_York");
        $timezone = $user->getTimeZone();
        $dateTime->setTimeZone($timezone);
        $offsetNY = $dateTime->getOffset() / 60;

        $this->assertEquals(300, ($offsetGMT - $offsetNY), "Unexpected Timezone returned for User");
    }

    /**
     * @param boolean $isWorkFlowModule The return value of User::isWorkFlowModule
     * @param array $modules Module list returned by getAdminModules
     * @param boolean $expected Expected return value
     *
     * @dataProvider isAdminOrDeveloperForModuleProvider
     * @covers User::isAdminForModule
     */
    public function testIsAdminForModule($isWorkFlowModule, array $modules, $expected)
    {
        $this->isAdminOrDeveloperForModule(
            'isAdminForModule',
            'getAdminModules',
            $isWorkFlowModule,
            $modules,
            $expected
        );
    }

    /**
     * @param boolean $isWorkFlowModule The return value of User::isWorkFlowModule
     * @param array $modules Module list returned by getDeveloperModules
     * @param boolean $expected Expected return value
     *
     * @dataProvider isAdminOrDeveloperForModuleProvider
     * @covers User::isDeveloperForModule
     */
    public function testIsDeveloperForModule($isWorkFlowModule, array $modules, $expected)
    {
        $this->isAdminOrDeveloperForModule(
            'isDeveloperForModule',
            'getDeveloperModules',
            $isWorkFlowModule,
            $modules,
            $expected
        );
    }

    /**
     * @param string $testMethod Method to be tested
     * @param string $getModules Method that returns module list
     * @param boolean $isWorkFlowModule The return value of User::isWorkFlowModule
     * @param array $modules Module list returned by $getModules
     * @param boolean $expected Expected return value
     */
    private function isAdminOrDeveloperForModule($testMethod, $getModules, $isWorkFlowModule, array $modules, $expected)
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder('User')
            ->setMethods(array($getModules, 'isWorkFlowModule'))
            ->disableOriginalConstructor()
            ->getMock();
        $user->id = 'TEST';

        $user->expects($this->any())
            ->method($getModules)
            ->will($this->returnValue($modules));

        $module = 'SomeModule';
        $user->expects($this->any())
            ->method('isWorkFlowModule')
            ->with($module)
            ->will($this->returnValue($isWorkFlowModule));

        $this->assertEquals($expected, $user->$testMethod($module));
    }

    /**
     * @group BR-1721
     */
    public function testUpdateLastLogin()
    {

        $now = TimeDate::getInstance()->nowDb();

        $last_login = $this->_user->updateLastLogin();

        $this->assertEquals($now, $last_login);
    }

    public function isAdminOrDeveloperForModuleProvider()
    {
        return array(
            // current module is a workflow module, but there are no developer or admin modules
            array(
                true,
                array(),
                false,
            ),
            // there are developer or admin modules, but current module is not a workflow module
            array(
                false,
                array('Accounts'),
                false,
            ),
            // current module is a workflow module, and there are developer or admin modules
            array(
                true,
                array('Accounts'),
                true,
            ),
        );
    }

    private function backUpConfig($name)
    {
        $config = null;

        if (isset($GLOBALS['sugar_config'][$name])) {
            $config = $GLOBALS['sugar_config'][$name];
        }

        return $config;
    }

    private function restoreConfig($name, $value = null)
    {
        if (!is_null($value)) {
            $GLOBALS['sugar_config'][$name] = $value;
        } else {
            unset($GLOBALS['sugar_config'][$name]);
        }
    }

    private function backUpSession($name)
    {
        $session = null;

        if (isset($_SESSION[$name])) {
            $session = $_SESSION[$name];
        }

        return $session;
    }

    private function restoreSession($name, $value = null)
    {
        if (!is_null($value)) {
            $_SESSION[$name] = $value;
        } else {
            unset($_SESSION[$name]);
        }
    }
}

