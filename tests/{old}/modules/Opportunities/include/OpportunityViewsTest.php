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
 * @coversDefaultClass \OpportunityViews
 */
class OpportunityViewsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarAutoLoader::load('modules/Opportunities/include/OpportunityViews.php');
    }

    /**
     * @covers ::processList
     */
    public function testProcessList()
    {
        $impl = $this->getMockBuilder('DeployedMetaDataImplementation')
            ->disableOriginalConstructor()
            ->setMethods(array('getPanelDefsPath'))
            ->getMock();

        $impl->expects($this->any())
            ->method('getPanelDefsPath')
            ->willReturn(array('base', 'view', 'list'));

        $parser = $this->getMockBuilder('SidecarListLayoutMetaDataParser')
            ->disableOriginalConstructor()
            ->setMethods(array('handleSave', 'generateFieldDef'))
            ->getMock();

        $parser->client = 'base';
        SugarTestReflection::setProtectedValue($parser, 'implementation', $impl);

        // Create a map of arguments to return values.
        $map = array(
            array('test_2', array('name' => 'test_2')),
            array('test_4', array('name' => 'test_4'))
        );

        $parser->expects($this->any())
            ->method('generateFieldDef')
            ->willReturnMap($map);

        $opp_setup = $this->getMockBuilder('OpportunityViews')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $bean = $this->getMockBuilder('Opportunity')
            ->setMethods(array('getFieldDefinition'))
            ->getMock();

        $bean->expects($this->any())
            ->method('getFieldDefinition')
            ->willReturn(true);

        SugarTestReflection::setProtectedValue($opp_setup, 'bean', $bean);

        $panel = array(
            array(
                'fields' => array(
                    'test_1' => array(
                        'name' => 'test_1'
                    ),
                    'test_3' => array(
                        'name' => 'test_3'
                    )
                )
            )
        );


        $parser->_viewdefs = array('base' => array('view' => array('list' => array('panels' => $panel))));

        $args = array(
            array(
                'test_1' => 'test_2',  // test switch field
                'test_3' => false, // test remove field
                'test_4' => true, // test add field
            ),
            $panel,
            $parser
        );

        SugarTestReflection::callProtectedMethod($opp_setup, 'processList', $args);

        $this->assertEquals(2, count($parser->_paneldefs[0]['fields']));
        $this->assertEquals('test_2', $parser->_paneldefs[0]['fields'][0]['name']);
        $this->assertEquals('test_4', $parser->_paneldefs[0]['fields'][1]['name']);
    }

    public function testProcessListRunsBWCWhenParserIsForBWCModule()
    {
        SugarAutoLoader::load('modules/ModuleBuilder/parsers/views/SubpanelMetaDataParser.php');
        $parser = $this->getMockBuilder('SubpanelMetaDataParser')
            ->disableOriginalConstructor()
            ->setMethods(array('handleSave', 'generateFieldDef'))
            ->getMock();

        $opp_setup = $this->getMockBuilder('OpportunityViews')
            ->disableOriginalConstructor()
            ->setMethods(array('processBWCList'))
            ->getMock();

        $args = array(
            array(
                'test_1' => 'test_2',  // test switch field
                'test_3' => false, // test remove field
                'test_4' => true, // test add field
            ),
            array(),
            $parser
        );

        $opp_setup->expects($this->once())
            ->method('processBWCList')
            ->with($args[0], $parser);

        SugarTestReflection::callProtectedMethod($opp_setup, 'processList', $args);
    }

    /**
     * @covers ::processBWCList
     */
    public function testProcessBWCListReturnsFalse()
    {
        $impl = $this->getMockBuilder('DeployedMetaDataImplementation')
            ->disableOriginalConstructor()
            ->setMethods(array('getPanelDefsPath'))
            ->getMock();

        $impl->expects($this->any())
            ->method('getPanelDefsPath')
            ->willReturn(array('base', 'view', 'list'));

        $parser = $this->getMockBuilder('ListLayoutMetaDataParser')
            ->disableOriginalConstructor()
            ->setMethods(array('handleSave', 'generateFieldDef'))
            ->getMock();

        $opp_setup = $this->getMockBuilder('OpportunityViews')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $bean = $this->getMockBuilder('Opportunity')
            ->setMethods(array('getFieldDefinition'))
            ->getMock();

        $bean->expects($this->any())
            ->method('getFieldDefinition')
            ->willReturn(true);

        $parser->client = 'base';
        SugarTestReflection::setProtectedValue($parser, 'implementation', $impl);

        $args = array(
            array(
                'test_1' => 'test_2',  // test switch field
                'test_3' => false, // test remove field
                'test_4' => true, // test add field
            ),
            $parser
        );

        $ret = SugarTestReflection::callProtectedMethod($opp_setup, 'processBWCList', $args);

        $this->assertFalse($ret);
    }

    /**
     * @covers ::processBWCList
     */
    public function testProcessBWCListSavesCorrectListFields()
    {
        $impl = $this->getMockBuilder('DeployedMetaDataImplementation')
            ->disableOriginalConstructor()
            ->setMethods(array('getPanelDefsPath'))
            ->getMock();

        $impl->expects($this->any())
            ->method('getPanelDefsPath')
            ->willReturn(array('base', 'view', 'list'));

        $parser = $this->getMockBuilder('ListLayoutMetaDataParser')
            ->disableOriginalConstructor()
            ->setMethods(array('handleSave', 'generateFieldDef', 'getModuleName'))
            ->getMock();

        $opp_setup = $this->getMockBuilder('OpportunityViews')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $bean = $this->getMockBuilder('Opportunity')
            ->setMethods(array('getFieldDefinition'))
            ->getMock();

        $bean->expects($this->any())
            ->method('getFieldDefinition')
            ->willReturn(true);

        SugarTestReflection::setProtectedValue($opp_setup, 'bean', $bean);

        $parser->client = 'base';
        SugarTestReflection::setProtectedValue($parser, 'implementation', $impl);

        $parser->_viewdefs = array(
            'test_1' => array(),
            'test_3' => array(),
        );

        $args = array(
            array(
                'test_1' => 'test_2',  // test switch field
                'test_3' => false, // test remove field
                'test_4' => true, // test add field
            ),
            $parser
        );

        $parser->expects($this->once())
            ->method('handleSave');
        $parser->expects($this->once())
            ->method('getModuleName')
            ->willReturn('Contracts');

        $ret = SugarTestReflection::callProtectedMethod($opp_setup, 'processBWCList', $args);

        $expected = array(
            'view_module' => 'Contracts',
            'group_0' => array(
                0 => 'test_2',
                1 => 'test_4'
            )
        );

        $this->assertSame($expected, $ret);
    }
}
