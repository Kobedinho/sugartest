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

require_once 'modules/MailMerge/merge_query.php';


/**
 * RS-147: Prepare MailMerge Module.
 */
class RS147Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array Beans created in tests.
     */
    protected static $createdBeans = array();

    /**
     * @var DBManager
     */
    protected static $db;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        self::$db = DBManagerFactory::getInstance();
    }

    public static function tearDownAfterClass()
    {
        $_REQUEST = array();
        foreach (self::$createdBeans as $bean) {
            $bean->mark_deleted($bean->id);
        }
        self::$createdBeans = array();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testSearch()
    {
        $bean = BeanFactory::newBean('Contacts');
        $bean->first_name = 'RS147Test';
        $bean->save(false);
        array_push(self::$createdBeans, $bean);
        $_REQUEST = array(
            'term' => 'RS147Test',
            'qModule' => 'Contacts'
        );
        $controller = new MailMergeController();
        $this->expectOutputRegex('/"value":"RS147Test"/');
        $controller->action_search();
    }

    public function testModulesMerge()
    {
        $bean = BeanFactory::newBean('Contacts');
        $bean->save(false);
        $merge = BeanFactory::newBean('Notes');
        $merge->save(false);
        $bean->load_relationship('notes');
        $bean->notes->add($merge);
        array_push(self::$createdBeans, $bean, $merge);
        $query = get_merge_query($bean, 'Notes', $merge->id);
        $result = self::$db->query($query);
        $cnt = 0;
        while ($row = self::$db->fetchByAssoc($result)) {
            $cnt++;
        }
        $this->assertEquals(0, $cnt);
    }

    /**
     * @param string $to Module name to get info.
     * @param string $from Related module name to get info.
     * @param array $fieldsFrom Additional fields to initialize.
     * @param array $fieldsTo Additional fields to initialize.
     * @param string $rel Related field to connect to modules.
     * @dataProvider provider
     */
    public function testMerge($to, $from, $fieldsTo, $fieldsFrom, $rel)
    {
        $bean = BeanFactory::newBean($to);
        foreach ($fieldsTo as $field => $value) {
            $bean->$field = $value;
        }
        $bean->save(false);
        $merge = BeanFactory::newBean($from);
        foreach ($fieldsFrom as $field => $value) {
            $merge->$field = $value;
        }
        $merge->save(false);
        array_push(self::$createdBeans, $bean, $merge);
        $bean->load_relationship($rel);
        $bean->$rel->add($merge);
        $query = get_merge_query($bean, $from, $merge->id);
        $result = self::$db->query($query);
        $cnt = 0;
        while ($row = self::$db->fetchByAssoc($result)) {
            $cnt++;
        }
        $this->assertEquals(1, $cnt);
    }

    public function provider()
    {
        return array(
            array(
                'Contacts',
                'Accounts',
                array(),
                array(),
                'accounts'
            ),
            array(
                'Contacts',
                'Opportunities',
                array(),
                array(),
                'opportunities'
            ),
            array(
                'Contacts',
                'Cases',
                array(),
                array(),
                'cases'
            ),
            array(
                'Contacts',
                'Bugs',
                array(),
                array(),
                'bugs'
            ),
            array(
                'Contacts',
                'Quotes',
                array(),
                array(),
                'quotes'
            ),
            array(
                'Opportunities',
                'Accounts',
                array(),
                array(),
                'accounts'
            ),
            array(
                'Accounts',
                'Opportunities',
                array(),
                array(),
                'opportunities'
            )
        );
    }
}
