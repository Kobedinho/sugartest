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


require_once('modules/ACLFields/actiondefs.php');

/**
 * Bug #62906 unit test
 *
 * @ticked 62906
 */
class Bug62906Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $lead = null;
    protected $task = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->lead = SugarTestLeadUtilities::createLead();
        $this->task = SugarTestTaskUtilities::createTask();
    }

    public function tearDown()
    {
        unset($_SESSION['ACL']);
        ACLField::$acl_fields = array();

        SugarTestHelper::tearDown();

        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestTaskUtilities::removeAllCreatedTasks();
    }

    /**
     * data provider
     * @return array
     */
    public function permissionDataProvider()
    {
        // should be false if either one is read only
        return array(
            array(ACL_READ_WRITE, ACL_READ_WRITE, true),
            array(ACL_READ_ONLY, ACL_READ_WRITE, false),
            array(ACL_READ_WRITE, ACL_READ_ONLY, false),
        );
    }

    /**
     * Test to check if the user has unlink permission
     *
     * @dataProvider permissionDataProvider
     *
     * @group 62906
     * @return void
     */
    public function testUnlinkPermission($parentIDPermission, $parentTypePermission, $expected)
    {
        global $current_user;

        $listview = new ListViewMock();

        // setting acl values
        ACLField::$acl_fields[$current_user->id]['Tasks']['parent_id'] = $parentIDPermission;
        ACLField::$acl_fields[$current_user->id]['Tasks']['parent_type'] = $parentTypePermission;
        $_SESSION['ACL'][$current_user->id]['Tasks']['fields']['parent_id'] = $parentIDPermission;
        $_SESSION['ACL'][$current_user->id]['Tasks']['fields']['parent_type'] = $parentTypePermission;

        $permission = $listview->checkUnlinkPermission('tasks', $this->task, $this->lead);

        $this->assertEquals($expected, $permission, 'Incorrect permission.');
    }
}

class ListViewMock extends ListView
{
    public function checkUnlinkPermission($linked_field, $aItem, $parentBean) {
        return parent::checkUnlinkPermission($linked_field, $aItem, $parentBean);
    }
}
