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
 * @group leadconvert
 * @group Studio
 */
class ConvertLayoutMetadataParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $parser;
    protected $contactDef = array(
        'module' => 'Contacts',
        'required' => true,
        'copyData' => true,
        'duplicateCheckOnStart' => true,
    );
    protected $accountDef = array(
        'module' => 'Accounts',
        'required' => true,
        'copyData' => true,
        'duplicateCheckOnStart' => true,
    );

    public function setUp()
    {
        parent::setUp();
        $this->parser = new TestConvertLayoutMetadataParser('Contacts');
        $this->parser->setConvertDefs(array(
                'modules' => array(
                    $this->contactDef,
                    $this->accountDef,
                )
            ));
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers ConvertLayoutMetadataParser::updateConvertDef
     */
    public function testUpdateConvertDef_WithExistingDef_UpdatesDef()
    {
        $this->parser->updateConvertDef(array(
                $this->contactDef,
                array(
                    'module' => 'Accounts',
                    'required' => false,
                    'copyData' => false,
                ),
            ));

        $expectedAccountDef = $this->accountDef;
        $expectedAccountDef['required'] = false;
        $expectedAccountDef['copyData'] = false;
        $expectedModules = array(
            'modules' => array(
                $this->contactDef,
                $expectedAccountDef
            )
        );

        $this->assertEquals($expectedModules, $this->parser->getConvertDefs(), 'Account def should be updated');
    }

    /**
     * @covers ConvertLayoutMetadataParser::updateConvertDef
     */
    public function testUpdateConvertDef_WithNewDef_AddsDef()
    {
        $fooDef = array(
            'module' => 'Foo',
            'required' => false,
            'copyData' => false,
        );

        $this->parser->updateConvertDef(array(
                $this->contactDef,
                $this->accountDef,
                $fooDef,
            ));

        $expectedModules = array(
            'modules' => array(
                $this->contactDef,
                $this->accountDef,
                $fooDef,
            )
        );

        $this->assertEquals($expectedModules, $this->parser->getConvertDefs(), 'Foo def should be added');
    }

    /**
     * @covers ConvertLayoutMetadataParser::updateConvertDef
     */
    public function testUpdateConvertDef_WithAccountAndOpp_ForcesAccountRequired()
    {
        $oppDef = array(
            'module' => 'Opportunities',
            'required' => true,
        );
        $this->parser->updateConvertDef(array(
                $this->contactDef,
                array(
                    'module' => 'Accounts',
                    'required' => false,
                ),
                $oppDef,
            ));

        $expectedAccountDef = $this->accountDef;
        $expectedAccountDef['required'] = true; //force required
        $expectedModules = array(
            'modules' => array(
                $this->contactDef,
                $expectedAccountDef,
                $oppDef
            )
        );

        $this->assertEquals($expectedModules, $this->parser->getConvertDefs(), 'Account def should be forced to required');
    }

    /**
     * @covers ConvertLayoutMetadataParser::applyDependenciesAndHiddenFields
     */
    public function testApplyDependenciesAndHiddenFields_DependenciesApply_AddedToDef()
    {
        $dependency = array('Bar' => array());
        $this->parser->mockOriginalDef['dependentModules'] = $dependency;
        $def = array(
            'module' => 'Foo',
            'required' => true,
        );
        $includedModules = array('Foo', 'Bar');
        $resultDef = $this->parser->applyDependenciesAndHiddenFields($def, $includedModules);
        $this->assertEquals($dependency, $resultDef['dependentModules'], 'Dependency should be set');
    }

    /**
     * @covers ConvertLayoutMetadataParser::applyDependenciesAndHiddenFields
     */
    public function testApplyDependenciesAndHiddenFields_DependencyDoesNotApply_NotAddedToDef()
    {
        $dependency = array('Bar' => array());
        $this->parser->mockOriginalDef['dependentModules'] = $dependency;
        $def = array(
            'module' => 'Foo',
            'required' => true,
        );
        $includedModules = array('Foo'); //Bar not included
        $resultDef = $this->parser->applyDependenciesAndHiddenFields($def, $includedModules);
        $this->assertFalse(isset($resultDef['dependentModules']), 'Dependency should not be set');
    }

    /**
     * @covers ConvertLayoutMetadataParser::applyDependenciesAndHiddenFields
     */
    public function testApplyDependenciesAndHiddenFields_HiddenFieldsApply_AddedToDef()
    {
        $hiddenFields = array('baz' => 'Bar');
        $this->parser->mockOriginalDef['hiddenFields'] = $hiddenFields;
        $def = array(
            'module' => 'Foo',
            'required' => true,
        );
        $includedModules = array('Foo', 'Bar');
        $resultDef = $this->parser->applyDependenciesAndHiddenFields($def, $includedModules);
        $this->assertEquals($hiddenFields, $resultDef['hiddenFields'], 'Hidden fields should be set');
    }

    /**
     * @covers ConvertLayoutMetadataParser::applyDependenciesAndHiddenFields
     */
    public function testApplyDependenciesAndHiddenFields_ExcludedFieldsApply_AddedToDef()
    {
        $hiddenFields = array('baz' => 'Bar');
        $this->parser->setExcludedFields(array(
            'Foo' => array(
                'repeat_type' => 'Bar',
            )
        ));
        $this->parser->mockOriginalDef['hiddenFields'] = $hiddenFields;
        $def = array(
            'module' => 'Foo',
            'required' => true,
        );
        $includedModules = array('Foo', 'Bar');
        $resultDef = $this->parser->applyDependenciesAndHiddenFields($def, $includedModules);
        $this->assertArrayHasKey('baz', $resultDef['hiddenFields'], 'Hidden fields should be set');
        $this->assertArrayHasKey('repeat_type', $resultDef['hiddenFields'], 'Hidden fields should be set');
    }

    /**
     * @covers ConvertLayoutMetadataParser::removeLayout
     */
    public function testRemoveLayout()
    {
        $this->parser->removeLayout('Accounts');
        $expectedModules = array(
            'modules' => array(
                $this->contactDef,
            ),
        );
        $this->assertEquals($expectedModules, $this->parser->getConvertDefs(), 'Account def should be removed');
    }

    /**
     * @covers ConvertLayoutMetadataParser::deploy
     */
    public function testDeploy()
    {
        $this->parser->deploy();
        $this->assertEquals(1, $this->parser->saveToFileCallCount, 'saveToFile() should be called once');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefForModule
     */
    public function testGetDefForModule_WithConvertDefsPassed_ReturnsCorrectModuleDef()
    {
        $fooDef = array('module'=>'Foo');
        $testModules = array(
            'modules' => array(
                $fooDef,
            ),
        );
        $actualDef = $this->parser->getDefForModule('Foo', $testModules);
        $this->assertEquals($fooDef, $actualDef, 'Foo def should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefForModule
     */
    public function testGetDefForModules_WithNoConvertDefsPassed_ReturnsCorrectModuleDef()
    {
        $actualDef = $this->parser->getDefForModule('Accounts');
        $this->assertEquals($this->accountDef, $actualDef, 'Accounts def should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefaultDefForModule
     */
    public function testGetDefaultDefForModules_ForModuleInOriginalViewDefs_ReturnsOriginalValues()
    {
        $actualDef = $this->parser->getDefaultDefForModule('Foo');
        $this->assertEquals($this->parser->mockOriginalDef, $actualDef, 'Original Foo def should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefaultDefForModule
     */
    public function testGetDefaultDefForModules_ForModuleNotInOriginalViewDefs_ReturnsDefaultValues()
    {
        $actualDef = $this->parser->getDefaultDefForModule('Bar');
        $defaultSettings = $this->parser->getDefaultModuleDefSettings();
        $expectedDef = array_merge(array('module' => 'Bar'), $defaultSettings);
        $this->assertEquals($expectedDef, $actualDef, 'Default settings should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefaultDefForModule
     */
    public function testGetDefaultDefForModules_ForModuleNotInOriginalAndDupeCheckEnabled_ReturnsDefaultWithDupeOnStart()
    {
        $this->parser->mockDupeCheckEnabledFlag = true;
        $actualDef = $this->parser->getDefaultDefForModule('Bar');
        $defaultSettings = $this->parser->getDefaultModuleDefSettings();
        $defaultSettings['duplicateCheckOnStart'] = true;
        $expectedDef = array_merge(array('module' => 'Bar'), $defaultSettings);
        $this->assertEquals($expectedDef, $actualDef, 'Default settings should be returned');
    }
}

class TestConvertLayoutMetadataParser extends ConvertLayoutMetadataParser
{
    public $saveToFileCallCount = 0;
    public $mockOriginalDef = array(
        'module' => 'Foo',
        'required' => 'ohyeah',
    );
    public $mockDupeCheckEnabledFlag = false;

    public $mockExcludedFields = array();

    protected function loadViewDefs()
    {
        //defer loading of the view defs for testing
        $this->_viewdefs = array();
        $this->_convertdefs = array();
    }

    public function deploy()
    {
        parent::deploy();
    }

    public function applyDependenciesAndHiddenFields($def, $includedModules)
    {
        return parent::applyDependenciesAndHiddenFields($def, $includedModules);
    }

    protected function _saveToFile($filename, $defs)
    {
        //stub out the actual saving of the file for testing
        $this->saveToFileCallCount++;
    }

    public function getConvertDefs()
    {
        return $this->_convertdefs;
    }

    public function setConvertDefs($convertdefs)
    {
        $this->_convertdefs = $convertdefs;
    }

    public function getOriginalViewDefs()
    {
        $viewdefs = array();
        $viewdefs['Leads']['base']['layout']['convert-main'] = array(
            'modules' => array($this->mockOriginalDef),
        );
        return $viewdefs;
    }

    public function getDefaultModuleDefSettings()
    {
        return $this->defaultModuleDefSettings;
    }

    protected function isDupeCheckEnabledForModule($module)
    {
        return $this->mockDupeCheckEnabledFlag;
    }

    public function setExcludedFields($excludedFields) {
        $this->excludedFields = $excludedFields;
    }
}
