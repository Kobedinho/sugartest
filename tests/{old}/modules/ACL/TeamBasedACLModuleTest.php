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

require_once 'modules/ACLActions/actiondefs.php';

class TeamBasedACLModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var SugarACLTeamBased
     */
    protected $acl;

    /**
     * @var TeamSet
     */
    protected $teamSetT1;

    /**
     * @var TeamSet
     */
    protected $teamSetT2;

    /**
     * @var TeamSet
     */
    protected $teamSetT1T2;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var SugarBean
     */
    protected $bean;

    protected $defaultAccessList = array(
        'access' => true,
        'view' => true,
        'list' => true,
        'edit' => true,
        'delete' => true,
        'import' => true,
        'export' => true,
        'massupdate' => true,
    );

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->acl = new SugarACLTeamBased();

        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();

        $this->teamSetT1 = BeanFactory::newBean('TeamSets');
        $this->teamSetT1->addTeams(array($team1->id));

        $this->teamSetT2 = BeanFactory::newBean('TeamSets');
        $this->teamSetT2->addTeams(array($team2->id));

        $this->teamSetT1T2 = BeanFactory::newBean('TeamSets');
        $this->teamSetT1T2->addTeams(array($team1->id, $team2->id));

        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $team1->add_user_to_team($this->user->id);

        $this->bean = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        $aclActions = new ACLAction();
        $aclActions->clearACLCache();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->teamSetT1->mark_deleted($this->teamSetT1->id);
        $this->teamSetT2->mark_deleted($this->teamSetT2->id);
        $this->teamSetT1T2->mark_deleted($this->teamSetT1T2->id);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestHelper::tearDown();
    }

    /**
     * Without a bean all access is granted.
     */
    public function testNonBeanAccess()
    {
        $actualList = $this->acl->getUserAccess(
            $this->module,
            $this->defaultAccessList,
            array('user' => $this->user)
        );
        $this->assertEquals($this->defaultAccessList, $actualList, '', 0.0, 10, true);
    }

    /**
     * Test no access for non-owner when a bean is not passed.
     * Teams intersections is ignored.
     */
    public function testTBANoBeanOwner()
    {
        $action = 'view';
        $context = array();
        // The user is in the TeamSetT1.
        $this->bean->acl_team_set_id = $this->teamSetT1T2->id;
        $this->bean->save();

        $aclData['module'][$action]['aclaccess'] = ACL_ALLOW_SELECTED_TEAMS;
        ACLAction::setACLData($this->user->id, $this->module, $aclData);

        $acl = $this->createPartialMock('SugarACLTeamBased', array('getCurrentUser'));
        $acl->expects($this->any())->method('getCurrentUser')->will($this->returnValue($this->user));

        $actualAccess = $acl->checkAccess($this->module, $action, $context);

        $this->assertEquals(false, $actualAccess);
    }

    /**
     * The TBA handle the Delete, Edit, Export, List and View actions only.
     * @dataProvider aclDataProvider
     */
    public function testNonOwnerACL($access, $action, $inSelectedTeams, $permissions)
    {
        // The user is in the TeamSetT1.
        if ($inSelectedTeams) {
            $this->bean->acl_team_set_id = $this->teamSetT1T2->id;
        } else {
            $this->bean->acl_team_set_id = $this->teamSetT2->id;
        }
        $this->bean->save();

        $aclData['module'][$action]['aclaccess'] = $access;
        ACLAction::setACLData($this->user->id, $this->module, $aclData);
        $context = array('bean' => $this->bean, 'user' => $this->user);

        $actualUserAccess = $this->acl->getUserAccess(
            $this->module,
            array($action => true),
            $context
        );
        $actualAccess = $this->acl->checkAccess($this->module, $action, $context);

        $this->assertEquals($permissions, $actualUserAccess[$action]);
        $this->assertEquals($permissions, $actualAccess);
    }

    /**
     * Test that cache considers about date modified.
     */
    public function testCacheByDateModified()
    {
        $action = 'edit';
        $context = array('bean' => $this->bean);
        // Give access.
        $this->bean->acl_team_set_id = $this->teamSetT1T2->id;
        $this->bean->save();

        $aclData['module'][$action]['aclaccess'] = ACL_ALLOW_SELECTED_TEAMS;
        ACLAction::setACLData($this->user->id, $this->module, $aclData);

        $acl = $this->createPartialMock('SugarACLTeamBased', array('getCurrentUser'));
        $acl->expects($this->any())->method('getCurrentUser')->will($this->returnValue($this->user));

        $actualAccess = $acl->checkAccess($this->module, $action, $context);
        $this->assertEquals(true, $actualAccess);

        // No access.
        $this->bean->acl_team_set_id = $this->teamSetT2->id;
        $this->bean->name = 'test';
        $this->bean->save();

        // Make sure "fetched_row" is populated. Date modified should force the cache.
        $this->bean->retrieve($this->bean->id);

        $actualAccess = $acl->checkAccess($this->module, $action, $context);
        $this->assertEquals(false, $actualAccess);
    }

    public function aclDataProvider()
    {
        /**
         * @var int $access
         * @var string $action
         * @var boolean $inSelectedTeams
         * @var boolean $permissions
         */
        return array(
            array(ACL_ALLOW_SELECTED_TEAMS, 'view', false, false),
            array(ACL_ALLOW_SELECTED_TEAMS, 'view', true, true),
            array(ACL_ALLOW_SELECTED_TEAMS, 'delete', false, false),
            array(ACL_ALLOW_SELECTED_TEAMS, 'delete', true, true),
            array(ACL_ALLOW_SELECTED_TEAMS, 'export', false, false),
            array(ACL_ALLOW_SELECTED_TEAMS, 'export', true, true),
            array(ACL_ALLOW_SELECTED_TEAMS, 'list', false, false),
            array(ACL_ALLOW_SELECTED_TEAMS, 'list', true, true),
            array(ACL_ALLOW_SELECTED_TEAMS, 'edit', false, false),
            array(ACL_ALLOW_SELECTED_TEAMS, 'edit', true, true),
            // Do not appear in role management, might be needed in future.
            array(ACL_ALLOW_SELECTED_TEAMS, 'access', false, false),
            array(ACL_ALLOW_SELECTED_TEAMS, 'import', false, false),
            array(ACL_ALLOW_SELECTED_TEAMS, 'massupdate', false, false),
            // Should not handle the rest roles.
            array(ACL_ALLOW_OWNER, 'view', false, true),
            array(ACL_ALLOW_NONE, 'view', false, true),
        );
    }
}
