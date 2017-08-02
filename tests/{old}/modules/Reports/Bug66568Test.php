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

require_once('modules/Reports/templates/templates_reports.php');

/**
 * Test all cases if user is allowed to export a report
 *
 * @see hasExportAccess()
 */
class Bug66568Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $args;
    private $reportDef = array(
        'display_columns' => array(),
        'summary_columns' => array(),
        'group_defs' => array(),
        'filters_def' => array(),
        'module' => 'Accounts',
        'assigned_user_id' => '1',
        'report_type' => 'tabular',
        'full_table_list' => array(
            'self' => array(
                'value' => 'Accounts',
                'module' => 'Accounts',
                'label' => 'Accounts',
            ),
        ),
    );

    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->role = new ACLRole();
        $this->role->name = 'Bug66568 Test';
        $this->role->save();

        $aclActions = $this->role->getRoleActions($this->role->id);
        $this->role->setAction($this->role->id, $aclActions['Accounts']['module']['export']['id'], ACL_ALLOW_ALL);

        $this->role->load_relationship('users');
        $this->role->users->add($GLOBALS['current_user']);

        $this->args = $args = array(
            'reporter' => new Report(json_encode($this->reportDef))
        );
    }

    public function tearDown()
    {
        global $sugar_config;

        unset($sugar_config['disable_export']);
        unset($sugar_config['admin_export_only']);

        $this->role->mark_deleted($this->role->id);
        SugarTestHelper::tearDown();
    }

    /**
     * Check if proper value is returned when reports export is disabled/enabled
     */
    public function testDisableExportFlag()
    {
        global $sugar_config;

        $sugar_config['disable_export'] = true;
        $this->assertEquals(false, hasExportAccess($this->args), "Exports disabled, shouldn't allow exports");

        $sugar_config['disable_export'] = false;
        $this->assertEquals(true, hasExportAccess($this->args), "Exports enabled, should allow exports");
    }

    /**
     * Check if proper report type is being exported
     */
    public function testReportType()
    {
        $this->args['reporter']->report_def['report_type'] = 'summary';
        $this->assertEquals(false, hasExportAccess($this->args), "Export not tabular, shouldn't allow exports");

        $this->args['reporter']->report_def['report_type'] = 'tabular';
        $this->assertEquals(true, hasExportAccess($this->args), "Exports tabular, should allow exports");
    }

    /**
     * Check if user has proper ACL Roles
     */
    public function testUserRoles()
    {
        $this->assertEquals(true, hasExportAccess($this->args), "User has rights, should allow exports");

        $aclActions = $this->role->getRoleActions($this->role->id);
        $this->role->setAction($this->role->id, $aclActions['Accounts']['module']['export']['id'], ACL_ALLOW_NONE);
        // Clear ACL cache
        $action = BeanFactory::newBean('ACLActions');
        $action->clearACLCache();

        $this->assertEquals(false, hasExportAccess($this->args), "User doesn't have rights, shouldn't allow exports");
    }

    /**
     * Check if only admin export is allowed
     */
    public function testAdminExport()
    {
        global $sugar_config;

        $sugar_config['admin_export_only'] = true;
        $this->assertEquals(false, hasExportAccess($this->args), "User is not admin, shouldn't allow exports");

        SugarTestHelper::setUp('current_user', array(true, 1));
        $this->assertEquals(true, hasExportAccess($this->args), "User is admin, should allow exports");
    }
}
