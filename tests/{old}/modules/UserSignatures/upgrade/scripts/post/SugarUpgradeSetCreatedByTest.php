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

class SugarUpgradeSetCreatedByTest extends UpgradeTestCase
{
    private $createdSignatures = array();

    public function setUp()
    {
        parent::setUp();
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $fields = array(
            'id',
            'name',
            'date_entered',
            'date_modified',
            'deleted',
            'user_id',
            'signature',
            'signature_html',
        );
        $now = $GLOBALS['timedate']->nowDb();
        $signatures = array(
            array(create_guid(), 'foo', $now, $now, 0, $user1->id, 'foo', '<b>foo</b>'),
            array(create_guid(), 'bar', $now, $now, 0, $user1->id, 'bar', '<b>bar</b>'),
            array(create_guid(), 'biz', $now, $now, 0, $user2->id, 'biz', '<b>biz</b>'),
            array(create_guid(), 'baz', $now, $now, 1, $user2->id, 'baz', '<b>baz</b>'),
        );
        foreach ($signatures as $signature) {
            $values = implode("', '", $signature);
            $GLOBALS['db']->query("INSERT INTO users_signatures (" . implode(',', $fields) . ") VALUES ('{$values}')");
            $this->createdSignatures[] = $signature[0];
        }
    }

    public function tearDown()
    {
        if (!empty($this->createdSignatures)) {
            $ids = implode("','", $this->createdSignatures);
            $GLOBALS['db']->query("DELETE FROM users_signatures WHERE id IN ('{$ids}')");
        }
        parent::tearDown();
    }

    public function testRun_UpgradesAllRows()
    {
        $beans = $this->findSignaturesWhereCreatedByIsEmpty();
        $this->assertNotEmpty($beans, 'Should find signatures where created_by is empty before upgrade');
        $this->upgrader->setVersions('6.7.4', 'ent', '7.2.1', 'ent');
        $script = $this->upgrader->getScript('post', '4_SetCreatedBy');
        $script->run();
        $beans = $this->findSignaturesWhereCreatedByIsEmpty();
        $this->assertEmpty($beans, 'Should not find any signatures where created_by is empty after upgrade');
    }

    private function findSignaturesWhereCreatedByIsEmpty()
    {
        $seed = BeanFactory::newBean('UserSignatures');
        $q = new SugarQuery();
        $options = array(
            'add_deleted' => false,
            'team_security' => false,
        );
        $q->from($seed, $options)->where()->queryOr()->isNull('created_by')->equals('created_by', '');
        return $seed->fetchFromQuery($q);
    }
}
