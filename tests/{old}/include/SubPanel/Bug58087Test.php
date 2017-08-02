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
 
require_once 'tests/{old}/include/SubPanel/SubPanelTestBase.php';

/**
 * Bug 58087 - Compose Email in activities sub panel
 * 
 * Tests the presence of the notes module in subpanels for offline client. Extends
 * the SubPanelTestBase which handle most of the setup and tear down.
 */
class Bug58087Test extends SubPanelTestBase
{
    protected $_modListHeaderGlobal = array();
    protected $_sugarConfig;
    protected $_testModule = 'Accounts';
    
    public function setUp() {
        parent::setUp();
        
        // Set up our test defs - borrowed from Accounts subpaneldefs
        $this->_testDefs = array(
            'order' => 10,
            'sort_order' => 'desc',
            'sort_by' => 'date_start',
            'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'activities',   //this values is not associated with a physical file.
            'header_definition_from_subpanel'=> 'meetings',
            'module'=>'Activities',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateTaskButton'),
                array('widget_class' => 'SubPanelTopScheduleMeetingButton'),
                array('widget_class' => 'SubPanelTopScheduleCallButton'),
                array('widget_class' => 'SubPanelTopComposeEmailButton'),
            ),
            'collection_list' => array(
                'tasks' => array(
                    'module' => 'Tasks',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'tasks',
                ),
                'meetings' => array(
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'meetings',
                ),
                'calls' => array(
                    'module' => 'Calls',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'calls',
                ),
            ),
        );
        
        // This test requires modListHeader
        if (!empty($GLOBALS['modListHeader'])) {
            $this->_modListHeaderGlobal = $GLOBALS['modListHeader'];
        }
        
        $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);
        
        // One test will modify sugar_config
        $this->_sugarConfig = $GLOBALS['sugar_config'];
    }
    
    public function tearDown()
    {
        parent::tearDown();
        
        if (!empty($this->_modListHeaderGlobal)) {
            $GLOBALS['modListHeader'] = $this->_modListHeaderGlobal;
        }
        
        $GLOBALS['sugar_config'] = $this->_sugarConfig;
    }

    /**
     * @group Bug58087
     */
    public function testEmailActionMenuItemExistsInSubpanelActionsOnDefaultInstallation()
    {
        $subpanel = new aSubPanel('activities', $this->_testDefs, $this->_testBean);
        $buttons = $subpanel->get_buttons();
        $test = $this->_hasEmailAction($buttons);
        $this->assertTrue($test, "Compose Email action missing when it was expected");
    }

    /**
     * @group Bug58087
     */
    public function testEmailActionMenuItemDoesNotExistInSubpanelActionsWhenInOfflineClient() 
    {
        $GLOBALS['sugar_config']['disc_client'] = true;
        $GLOBALS['sugar_config']['oc_converted'] = true;
        
        // Test it
        $subpanel = new aSubPanel('activities', $this->_testDefs, $this->_testBean);
        $buttons = $subpanel->get_buttons();
        $test = $this->_hasEmailAction($buttons);
        $this->assertFalse($test, "Compose Email button returned when it was supposed to be excluded");
    }

    /**
     * Helper method that scans an array and checks for the presence of a value
     * 
     * @param array $buttons
     * @return bool
     */
    protected function _hasEmailAction($buttons) 
    {
        foreach ($buttons as $button) {
            if (isset($button['widget_class']) && $button['widget_class'] == 'SubPanelTopComposeEmailButton') {
                return true;
            }
        }
        
        return false;
    }
}