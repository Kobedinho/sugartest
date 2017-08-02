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
require_once 'include/SearchForm/SearchForm2.php';

class EmployeesViewListTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $sugarConfigBackup = array();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        global $sugar_config;
        $this->sugarConfigBackup = $sugar_config;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        global $sugar_config;
        $sugar_config = $this->sugarConfigBackup;
    }

    /**
     * Data provider for testListViewExportButtons.
     *
     * @see EmployeesViewListTest::testListViewExportButtons
     * @return array
     */
    public static function listViewExportButtonsDataProvider()
    {
        return array(
            // Admin user will see template with export options if admin_export_only is checked
            array(true, array('disable_export' => false, 'admin_export_only' => true), true),
            // Admin user will not see template with export options if disable_export is checked
            array(true, array('disable_export' => true, 'admin_export_only' => true), false),
            // Regular user will not see template with export options if admin_export_only is checked
            array(false, array('disable_export' => false, 'admin_export_only' => true), false),
            // Regular user shouldn't see template with export options if disable_export is checked
            array(false, array('disable_export' => true, 'admin_export_only' => false), false),
            // Regular user will not see template with export options
            // if disable_export is not checked and admin_export_only is not checked
            array(false, array('disable_export' => false, 'admin_export_only' => false), false),
        );
    }

    /**
     * Check possible options of view.list templates
     *
     * @dataProvider listViewExportButtonsDataProvider
     * @covers EmployeesViewList::listViewProcess
     * @param bool $isAdmin
     * @param array $config
     * @param string $expected Expected result
     */
    public function testListViewExportButtons($isAdmin, $config, $expected)
    {
        SugarTestHelper::setUp('current_user', array(true, $isAdmin));

        // Set config parameters
        global $sugar_config;
        foreach ($config as $key => $value) {
            $sugar_config[$key] = $value;
        }
        /** @var Employee $bean */
        $bean = BeanFactory::newBean('Employees');
        $searchForm = new SearchForm($bean, 'Employees');

        /** @var ListViewSmarty|PHPUnit_Framework_MockObject_MockObject $lvMock */
        $lvMock = $this->getMockBuilder('ListViewSmarty')->setMethods(array('display'))->getMock();

        /** @var EmployeesViewList|PHPUnit_Framework_MockObject_MockObject $employeesListViewMock */
        $employeesListViewMock = $this->createPartialMock('EmployeesViewList', array('processSearchForm'));
        $employeesListViewMock->searchForm = $searchForm;
        $employeesListViewMock->headers = true;
        $employeesListViewMock->seed = $bean;

        $employeesListViewMock->preDisplay();
        $employeesListViewMock->lv = $lvMock;
        $employeesListViewMock->lv->displayColumns = array();

        $employeesListViewMock->listViewProcess();

        // Check if export button exists in template
        $buttonExport = $this->hasButton($employeesListViewMock);

        // Compare expected result with actual
        $this->assertEquals($expected, $buttonExport);
    }

    /**
     * Check if export button exists in template
     *
     * @param EmployeesViewList|PHPUnit_Framework_MockObject_MockObject $employeesListViewMock
     * @return bool
     */
    protected function hasButton($employeesListViewMock)
    {
        // Get list of available buttons from template
        /** @var array $actionsTop */
        $actionsTop = $employeesListViewMock->lv->ss->get_template_vars('actionsLinkTop');
        $buttons = $actionsTop['buttons'];
        $buttonExport = false;

        foreach ($buttons as $button) {
            if (strpos($button, 'index.php?entryPoint=export')) {
                $buttonExport = true;
            }
        }

        return $buttonExport;
    }
}
