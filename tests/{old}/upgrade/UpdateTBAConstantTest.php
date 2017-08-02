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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';

/**
 * UpdateTBAConstantTest
 */
class UpdateTBAConstantTest extends UpgradeTestCase
{
    const OLD_CONST = 72;
    const NEW_CONST = 78;

    /**
     * @var DBManager
     */
    protected $db;

    /**
     * @var array
     */
    protected $existingIds = array();

    /**
     * @var string
     */
    protected $recId = 'tbaupgradetest1';

    public function setUp()
    {
        parent::setUp();
        $this->db
            = $this->upgrader->db
            = DBManagerFactory::getInstance();

        // save ids to rollback test changes
        $this->existingIds = $this->getAclRolesActionsIds(self::OLD_CONST);
    }

    public function tearDown()
    {
        parent::tearDown();

        $db = $this->db;

        // remove test record
        $db->query("DELETE FROM acl_roles_actions WHERE id = '{$db->quote($this->recId)}'");

        // rollback access_override vales
        if (!empty($this->existingIds)) {
            $ids = $this->existingIds;
            array_walk($ids, function (&$v) use ($db) {
                $v = "'" . $db->quote($v) . "'";
            });
            $ids = implode(',', $ids);
            $this->db->query(
                "UPDATE acl_roles_actions SET access_override = '{$db->quote(self::OLD_CONST)}' WHERE id IN ({$ids})"
            );
        }
    }

    /**
     * Test 9_UpdateTBAConstant script.
     */
    public function testUpgradeTBAConstant()
    {
        global $dictionary;
        $db = $this->db;

        // insert fake records
        $db->insertParams('acl_roles_actions', $dictionary['acl_roles_actions']['fields'], array(
            'id' => $this->recId,
            'role_id' => 'somerole',
            'action_id' => 'someaction',
            'access_override' => self::OLD_CONST,
            'date_modified' => $GLOBALS['timedate']->nowDb(),
            'deleted' => 0,
        ));

        // check set up
        $this->assertGreaterThanOrEqual(1, $this->getAclRolesActionsIds(self::OLD_CONST));

        // run upgrade script
        $script = $this->upgrader->getScript('post', '9_UpdateTBAConstant');
        $script->from_version = '7.8.0.0.RC.1';
        $script->to_version = '7.8.0.0.RC.3';
        $script->run();

        // check if there are still records with old access level
        $this->assertEmpty($this->getAclRolesActionsIds(self::OLD_CONST));

        // there are should be records with new access level
        $this->assertNotEmpty($this->getAclRolesActionsIds(self::NEW_CONST));
    }

    /**
     * Returns ids of acl_roles_actions with provided $accessLevel.
     *
     * @param int $accessLevel
     * @return array
     */
    protected function getAclRolesActionsIds($accessLevel)
    {
        $ids = array();
        $result = $this->db->query(
            "SELECT id FROM acl_roles_actions WHERE access_override = '{$this->db->quote($accessLevel)}'"
        );
        while ($row = $this->db->fetchRow($result)) {
            $ids[] = $row['id'];
        }

        return $ids;
    }
}
