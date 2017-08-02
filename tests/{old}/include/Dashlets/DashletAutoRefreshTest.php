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
 * @ticket 33948
 */
class DashletAutoRefreshTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
        if ( isset($GLOBALS['sugar_config']['dashlet_auto_refresh_min']) ) {
            $this->backup_dashlet_auto_refresh_min = $GLOBALS['sugar_config']['dashlet_auto_refresh_min'];
        }
        unset($GLOBALS['sugar_config']['dashlet_auto_refresh_min']);
    }
    
    public function tearDown()
    {
        if ( isset($this->backup_dashlet_auto_refresh_min) ) {
            $GLOBALS['sugar_config']['dashlet_auto_refresh_min'] = $this->backup_dashlet_auto_refresh_min;
        }
    }
    
    public function testIsAutoRefreshableIfRefreshable() 
    {
        $dashlet = new Dashlet('unit_test_run');
        $dashlet->isRefreshable = true;

        $this->assertTrue(SugarTestReflection::callProtectedMethod($dashlet, 'isAutoRefreshable'));
    }
    
    public function testIsNotAutoRefreshableIfNotRefreshable() 
    {
        $dashlet = new Dashlet('unit_test_run');
        $dashlet->isRefreshable = false;

        $this->assertFalse(SugarTestReflection::callProtectedMethod($dashlet, 'isAutoRefreshable'));
    }
  
    public function testReturnCorrectAutoRefreshOptionsWhenMinIsSet() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('dashlet_auto_refresh_options',
            array(
                '-1' 	=> 'Never',
                '30' 	=> 'Every 30 seconds',
                '60' 	=> 'Every 1 minute',
                '180' 	=> 'Every 3 minutes',
                '300' 	=> 'Every 5 minutes',
                '600' 	=> 'Every 10 minutes',
                )
            );
        $langpack->save();
    
        $GLOBALS['sugar_config']['dashlet_auto_refresh_min'] = 60;
        
        $dashlet = new Dashlet('unit_test_run');
        $options = SugarTestReflection::callProtectedMethod($dashlet, 'getAutoRefreshOptions');
        $this->assertEquals(
            array(
                '-1' 	=> 'Never',
                '60' 	=> 'Every 1 minute',
                '180' 	=> 'Every 3 minutes',
                '300' 	=> 'Every 5 minutes',
                '600' 	=> 'Every 10 minutes',
                ),
            $options
            );
        
        unset($langpack);
    }
    
    public function testReturnCorrectAutoRefreshOptionsWhenMinIsNotSet() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('dashlet_auto_refresh_options',
            array(
                '-1' 	=> 'Never',
                '30' 	=> 'Every 30 seconds',
                '60' 	=> 'Every 1 minute',
                '180' 	=> 'Every 3 minutes',
                '300' 	=> 'Every 5 minutes',
                '600' 	=> 'Every 10 minutes',
                )
            );
        $langpack->save();
    
        $dashlet = new Dashlet('unit_test_run');
        $options = SugarTestReflection::callProtectedMethod($dashlet, 'getAutoRefreshOptions');
        $this->assertEquals(
            array(
                '-1' 	=> 'Never',
                '30' 	=> 'Every 30 seconds',
                '60' 	=> 'Every 1 minute',
                '180' 	=> 'Every 3 minutes',
                '300' 	=> 'Every 5 minutes',
                '600' 	=> 'Every 10 minutes',
                ),
            $options
            );
        
        unset($langpack);
    }
    
    public function testProcessAutoRefreshReturnsAutoRefreshTemplateNormally()
    {
        $dashlet = new Dashlet('unit_test_run');
        $dashlet->isRefreshable = true;
        $_REQUEST['module'] = 'unit_test';
        $_REQUEST['action'] = 'unit_test';
        $dashlet->seedBean = new stdClass;
        $dashlet->seedBean->object_name = 'unit_test';
        
        $this->assertNotEmpty(SugarTestReflection::callProtectedMethod($dashlet, 'processAutoRefresh'));
    }
    
    public function testProcessAutoRefreshReturnsNothingIfDashletIsNotRefreshable()
    {
        $dashlet = new Dashlet('unit_test_run');
        $dashlet->isRefreshable = false;
        $_REQUEST['module'] = 'unit_test';
        $_REQUEST['action'] = 'unit_test';
        $dashlet->seedBean = new stdClass;
        $dashlet->seedBean->object_name = 'unit_test';
        
        $this->assertEmpty(SugarTestReflection::callProtectedMethod($dashlet, 'processAutoRefresh'));
    }
    
    public function testProcessAutoRefreshReturnsNothingIfAutoRefreshingIsDisabled()
    {
        $dashlet = new Dashlet('unit_test_run');
        $GLOBALS['sugar_config']['dashlet_auto_refresh_min'] = -1;
        $_REQUEST['module'] = 'unit_test';
        $_REQUEST['action'] = 'unit_test';
        $dashlet->seedBean = new stdClass;
        $dashlet->seedBean->object_name = 'unit_test';
        
        $this->assertEmpty(SugarTestReflection::callProtectedMethod($dashlet, 'processAutoRefresh'));
    }
}
