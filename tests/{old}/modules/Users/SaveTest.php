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


/**
 * User profile Save tests
 *
 * @author mmarum
 */
class SaveTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $current_user;
    protected $tabs;
    protected $savedTabs;

    public function setUp()
    {
        parent::setUp();
        $this->current_user = SugarTestHelper::setUp('current_user', array(true, 1));
        $this->tabs = new TabController();
        $this->savedTabs = $this->tabs->get_user_tabs($this->current_user);
    }

    public function tearDown()
    {
        $this->tabs->set_user_tabs($this->savedTabs, $this->current_user, "display");
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Home always needs to be first display tab
     */
    public function testAddHomeToDisplayTabsOnSave()
    {
        $current_user = $this->current_user;
        $_POST['record'] = $current_user->id;
        $_REQUEST['display_tabs_def'] = 'display_tabs[]=Leads';  //Save only included Leads
        include('modules/Users/Save.php');
        //Home was prepended
        $this->assertEquals(array('Home' => 'Home', 'Leads' => 'Leads'), $this->tabs->get_user_tabs($focus));
    }
}
