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
 * @coversDefaultClass StudioModule
 */

class StudioModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

    }
    
    public static function tearDownAfterClass()
    {
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_list_strings']);
    }

    /**
     * @ticket 39407
     *
     */
    public function testRemoveFieldFromLayoutsDocumentsException()
    {
        $this->markTestSkipped('Skip this test');
    	$SM = new StudioModule("Documents");
        try {
            $SM->removeFieldFromLayouts("aFieldThatDoesntExist");
            $this->assertTrue(true);
        } catch (Exception $e) {
            //Studio module threw exception
            $this->assertTrue(true);
        }
    }

    public function providerGetType()
    {
        return array(
            array('Meetings', 'basic'),
            array('Calls', 'basic'),
            array('Accounts', 'company'),
            array('Contacts', 'person'),
            array('Leads', 'person'),
            array('Cases', 'basic'),
        );
    }

    /**
     * @ticket 50977
     *
     * @dataProvider providerGetType
     */
    public function testGetTypeFunction($module, $type) {
        $SM = new StudioModule($module);
        $this->assertEquals($type, $SM->getType(), 'Failed asserting that module:' . $module . ' is of type:' . $type);
    }


    public function providerBWCHasSearch()
    {
        return array(
            array('Meetings', true),
            array('Accounts', false),
            array('Documents', true),
            array('Calls', false),
        );
    }
    /**
    * @dataProvider providerBWCHasSearch
    * @bug SC-519
    */
    public function testBWCHasSearch($module, $isBWC)
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $SM = new StudioModule($module);
        $layouts = $SM->getLayouts();
        $this->assertEquals($isBWC, !empty($layouts[translate('LBL_SEARCH', "ModuleBuilder")]),
            'Failed asserting that module:' . $module . ' has a search layout when BWC');
    }

    /**
     * @covers ::getModuleSubpanels
     * @dataProvider providerGetModuleSubpanels
     */
    public function testGetModuleSubpanels($defs, $sourceModule, $expected)
    {
        $stub = $this->getStudioModuleMock();

        $result = SugarTestReflection::callProtectedMethod(
            $stub,
            'getModuleSubpanels',
            array($defs, $sourceModule)
        );

        $this->assertEquals($result, $expected);
    }

    public function providerGetModuleSubpanels()
    {
        return array(
            array(
                array(
                    "accounts_meetings_1" => array("module" => "Accounts"),
                    "notes_meetings_1" => array("module" => "Notes"),
                ),
                "Accounts",
                array('accounts_meetings_1'),
            ),
            array(
                array(
                    "notes" => array("module" => "Notes")
                ),
                "Accounts",
                array(),
            ),
            array(
                array(),
                "Accounts",
                array(),
            ),
        );
    }

    /**
     * @return StudioModule
     */
    protected function getStudioModuleMock(array $methods = null)
    {
        return $this->getMockBuilder('StudioModule')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
