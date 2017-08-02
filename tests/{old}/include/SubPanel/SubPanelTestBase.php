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
 

class SubPanelTestBase extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_tabController;
    protected $_currentTabs;
    protected $_currentSubpanels = array('hidden' => array(), 'shown' => array());
    protected $_modListGlobal;
    protected $_subPanelDefinitions;
    protected $_testDefs;
    protected $_exemptModules;
    protected $_testModule; // This needs to be set in the child
    protected $_testBean; // Will be created from the testModule
    
    public function setUp() {
        // Globals setup
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        
        // @hack - Projects totally overrides the exempt module list in its subpanel
        // viewdefs, so to run this test effectively, Projects needs to be 
        // disabled if it is enabled. - rgonzalez
        $this->_modListGlobal = $GLOBALS['moduleList'];
        $key = array_search('Project', $GLOBALS['moduleList']);
        unset($GLOBALS['moduleList'][$key]);
        
        // Setup the test bean
        $this->_testBean = BeanFactory::newBean($this->_testModule);
        // Get the current module and subpanel settings
        $this->_tabController = new TabController();
        $this->_currentTabs = $this->_tabController->get_system_tabs();
        $this->_subPanelDefinitions = new SubPanelDefinitions($this->_testBean);
        $subpanels = $this->_subPanelDefinitions->get_all_subpanels();
        $subpanels_hidden = $this->_subPanelDefinitions->get_hidden_subpanels();

        if (!empty($subpanels)) {
            $this->_currentSubpanels['shown'] = $subpanels;
        }
        
        if (!empty($subpanels_hidden)) {
            $this->_currentSubpanels['hidden'] = $subpanels_hidden;
        }
        
        // Handle exempt modules, since this global gets set in other places in
        // the code base and is causing the last unit test to fail because of the
        // override that happens in the Project module subpaneldefs.php file.
        $this->_exemptModules = empty($GLOBALS['modules_exempt_from_availability_check']) ? array() : $GLOBALS['modules_exempt_from_availability_check'];
        unset($GLOBALS['modules_exempt_from_availability_check']);
        
        // Copied from include/utils/security_utils.php
        $modules_exempt_from_availability_check['Activities']='Activities';
        $modules_exempt_from_availability_check['History']='History';
        $modules_exempt_from_availability_check['Calls']='Calls';
        $modules_exempt_from_availability_check['Meetings']='Meetings';
        $modules_exempt_from_availability_check['Tasks']='Tasks';
        
        $modules_exempt_from_availability_check['CampaignLog']='CampaignLog';
        $modules_exempt_from_availability_check['CampaignTrackers']='CampaignTrackers';
        $modules_exempt_from_availability_check['Prospects']='Prospects';
        $modules_exempt_from_availability_check['ProspectLists']='ProspectLists';
        $modules_exempt_from_availability_check['EmailMarketing']='EmailMarketing';
        $modules_exempt_from_availability_check['EmailMan']='EmailMan';
        $modules_exempt_from_availability_check['ProjectTask']='ProjectTask';
        $modules_exempt_from_availability_check['Users']='Users';
        $modules_exempt_from_availability_check['Teams']='Teams';
        $modules_exempt_from_availability_check['SchedulersJobs']='SchedulersJobs';
        $modules_exempt_from_availability_check['DocumentRevisions']='DocumentRevisions';
        
        $GLOBALS['modules_exempt_from_availability_check'] = $modules_exempt_from_availability_check;
    }
    
    public function tearDown() {
        // Restore the globals 
        $GLOBALS['moduleList'] = $this->_modListGlobal;
        if (!empty($this->_exemptModules)) {
            $GLOBALS['modules_exempt_from_availability_check'] = $this->_exemptModules;
        }
        
        // Restore the system tabs to pre-test state
        $this->_tabController->set_system_tabs($this->_currentTabs);
        
        // Restore the hidden subpanels to pre-test state
        $this->_subPanelDefinitions->set_hidden_subpanels($this->_currentSubpanels['hidden']);
        
        // Clean up the rest
        SugarTestHelper::tearDown();
    }
}