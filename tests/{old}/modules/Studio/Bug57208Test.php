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


class Bug57208Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_testModule = 'Bug57208Test';
    
    public function setUp() 
    {
        sugar_mkdir("modules/{$this->_testModule}");
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
    }
    
    public function tearDown()
    {
        rmdir("modules/{$this->_testModule}");
        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug57208
     */
    public function testModuleTypeIsBasicForModuleWithNoBeanListEntry()
    {
        $sm = new StudioModule($this->_testModule);
        $type = $sm->getType();
        
        $this->assertEquals('basic', $type, "Type should be 'basic' but '$type' was returned from StudioModule :: getType()");
    }
}