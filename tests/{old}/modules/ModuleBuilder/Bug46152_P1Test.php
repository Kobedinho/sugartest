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


require_once 'modules/ModuleBuilder/MB/ModuleBuilder.php';

class Bug46152_P1Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function getModuleAliasesData()
    {
        return array(
            array(
                'Users',
                array('Users', 'Employees'),
            ),
            array(
                'Employees',
                array('Users', 'Employees'),
            ),
            array(
                'Notes',
                array('Notes'),
            ),

        );
    }

    /**
     * Testing ModuleBuilder::getModuleAliases
     * 
     * @dataProvider getModuleAliasesData
     * @group 46152
     */
    public function testGetModuleAliases($module, $needAliases)
    {
        $aliases = ModuleBuilder::getModuleAliases($module);
        foreach ($needAliases AS $needAlias) {
            $this->assertContains($needAlias, $aliases);
        }

    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('dictionary');
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

}
