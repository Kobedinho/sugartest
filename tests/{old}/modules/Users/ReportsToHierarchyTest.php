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


class ReportsToHierarchyTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $employee1;
    private $employee2;
    private $employee3;
    private $employee4;

    public function setUp()
    {
        $GLOBALS['db']->preInstall();
        global $beanList, $beanFiles, $current_user;
        require('include/modules.php');

        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->user_name = 'employee0';
        $current_user->save();

        $this->employee1 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee1->reports_to_id = $current_user->id;
        $this->employee1->user_name = 'employee1';
        $this->employee1->save();

        $this->employee2 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee2->reports_to_id = $current_user->id;
        $this->employee2->user_name = 'employee2';
        $this->employee2->save();

        $this->employee3 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee3->reports_to_id = $this->employee2->id;
        $this->employee3->user_name = 'employee3';
        $this->employee3->save();

        $this->employee4 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee4->reports_to_id = $this->employee3->id;
        $this->employee4->user_name = 'employee4';
        $this->employee4->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group hierarchies
     */
    public function testReportsToHierarchyFunction()
    {
        $this->markTestSkipped('Skip for 6.7 versions.  When we re-introduce using the hierarchical queries we will re-enable this test');
        global $current_user;

        $ids = $current_user->get_reports_to_hierarchy();
        $this->assertEquals(5, count($ids));

        $ids = $current_user->get_reports_to_hierarchy(false, false);
        $this->assertEquals(4, count($ids));

        $ids = $this->employee1->get_reports_to_hierarchy();
        $this->assertEquals(1, count($ids));

        $ids = $this->employee1->get_reports_to_hierarchy(false, false);
        $this->assertEquals(0, count($ids));

        $ids = $this->employee2->get_reports_to_hierarchy();
        $this->assertEquals(3, count($ids));

        $ids = $this->employee2->get_reports_to_hierarchy(false, false);
        $this->assertEquals(2, count($ids));

        $ids = $this->employee3->get_reports_to_hierarchy();
        $this->assertEquals(2, count($ids));

        $ids = $this->employee3->get_reports_to_hierarchy(false, false);
        $this->assertEquals(1, count($ids));

        $ids = $this->employee4->get_reports_to_hierarchy();
        $this->assertEquals(1, count($ids));

        $ids = $this->employee4->get_reports_to_hierarchy(false, false);
        $this->assertEquals(0, count($ids));
    }
}
