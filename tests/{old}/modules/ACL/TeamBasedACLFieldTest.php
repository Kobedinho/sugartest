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

require_once 'modules/ACLFields/actiondefs.php';

class TeamBasedACLFieldTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var string
     */
    protected $fieldName = 'industry';

    /**
     * @var SugarACLTeamBased
     */
    protected $acl;

    /**
     * @var ACLField
     */
    protected $aclField;

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

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->acl = new SugarACLTeamBased();
        $this->aclField = new ACLField();

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
        $this->aclField->clearACLCache();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->teamSetT1->mark_deleted($this->teamSetT1->id);
        $this->teamSetT2->mark_deleted($this->teamSetT2->id);
        $this->teamSetT1T2->mark_deleted($this->teamSetT1T2->id);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestHelper::tearDown();
    }

    /**
     * Refuse all permissions for non selected teamset specified.
     */
    public function testNonOwnerNoSelectedTeamSet()
    {
        $this->bean->acl_team_set_id = null;
        $this->bean->save();

        ACLField::$acl_fields[$this->user->id][$this->bean->module_dir][$this->fieldName] =
            ACL_SELECTED_TEAMS_READ_WRITE;

        $access = $this->acl->getFieldListAccess(
            $this->module,
            array($this->fieldName => $this->fieldName),
            array('bean' => $this->bean, 'user' => $this->user)
        );
        $this->assertEquals(0, $access[$this->fieldName]);
    }

    /**
     * Test that owner has access independently teams intersection.
     */
    public function testOwnerHasFullToTeamBasedACL()
    {
        $this->bean->assigned_user_id = $this->user->id;
        // The different set.
        $this->bean->acl_team_set_id = $this->teamSetT2->id;
        $this->bean->save();

        ACLField::$acl_fields[$this->user->id][$this->bean->module_dir][$this->fieldName] =
            ACL_SELECTED_TEAMS_READ_WRITE;

        $access = $this->acl->getFieldListAccess(
            $this->module,
            array($this->fieldName => $this->fieldName),
            array('bean' => $this->bean, 'user' => $this->user)
        );

        $this->assertEquals(4, $access[$this->fieldName]);
    }

    /**
     * @dataProvider aclDataProvider
     */
    public function testNonOwnerACL($access, $inSelectedTeams, $permissions)
    {
        if ($inSelectedTeams) {
            $this->bean->acl_team_set_id = $this->teamSetT1T2->id;
        } else {
            $this->bean->acl_team_set_id = $this->teamSetT2->id;
        }

        $this->bean->save();
        ACLField::$acl_fields[$this->user->id][$this->bean->module_dir][$this->fieldName] = $access;

        $access = $this->acl->getFieldListAccess(
            $this->module,
            array($this->fieldName => $this->fieldName),
            array('bean' => $this->bean, 'user' => $this->user)
        );
        $this->assertEquals($permissions, $access[$this->fieldName]);
    }

    public function aclDataProvider()
    {
        /**
         * @var int $access
         * @var bool $inSelectedTeams
         * @var int $permissions ACL_NO_ACCESS = 0; ACL_READ_ONLY = 1; ACL_READ_WRITE = 4;
         */
        return array(
            array(ACL_SELECTED_TEAMS_READ_WRITE, false, 0),
            array(ACL_SELECTED_TEAMS_READ_WRITE, true, 4),
            array(ACL_SELECTED_TEAMS_READ_OWNER_WRITE, false, 0),
            array(ACL_SELECTED_TEAMS_READ_OWNER_WRITE, true, 1),
            array(ACL_READ_SELECTED_TEAMS_WRITE, false, 1),
            array(ACL_READ_SELECTED_TEAMS_WRITE, true, 4),
            // Doesn't handle the old roles.
            array(ACL_OWNER_READ_WRITE, false, 4),
        );
    }
}
