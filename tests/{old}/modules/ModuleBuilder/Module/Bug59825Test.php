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


class Bug59825Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * The test module
     * 
     * @var string
     */
    protected static $_module = 'Bugs';

    /**
     * Rather than setting up and tearing down for each iteration of the data 
     * provider, set up once and tear down once, as these are used as-is throughout
     * each test.
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user', array(true, true)); // Admin user
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
    }
    
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests not null parsers for views
     * 
     * @param string $type A type of view to get a parser for
     * @dataProvider _layoutProvider
     */
    public function testParserIsNotNullForLayoutType($type)
    {
        $parser = ParserFactory::getParser($type, self::$_module);
        $this->assertNotNull($parser, "Portal parser for $type in Bugs module is null");
    }

    /**
     * Gets a list of 'types' of metadata views to be used in the test. Includes
     * basic layouts from all OOTB and, where applicable, wireless and portal
     * layouts.
     * 
     * @return array
     */
    public function _layoutProvider()
    {
        return array(
            // Basic types for all OOTB installations
            // This simulates StudioModule::getViewMetadataSources()
            array('type' => MB_EDITVIEW),
            array('type' => MB_DETAILVIEW),
            array('type' => MB_LISTVIEW),
            array('type' => MB_BASICSEARCH),
            array('type' => MB_ADVANCEDSEARCH),
            array('type' => MB_DASHLET),
            array('type' => MB_DASHLETSEARCH),
            array('type' => MB_POPUPLIST),
            array('type' => MB_QUICKCREATE),
            // Wireless types
            // This simulates StudioModule::getWirelessLayouts()
            array('type' => MB_WIRELESSEDITVIEW),
            array('type' => MB_WIRELESSDETAILVIEW),
            array('type' => MB_WIRELESSLISTVIEW),
            // Portal types not including search, which was the cause of the bug
            // This simulates StudioModule::getPortalLayoutSources()
            array('type' => MB_PORTALRECORDVIEW),
            array('type' => MB_PORTALLISTVIEW),
        );
    }
}