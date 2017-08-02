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

require_once 'upgrade/scripts/post/1_ClearVarDefs.php';
require_once 'SugarTestReflection.php';

/**
 * Test asserts correct removal of wrong field definitions from a bean.
 */
class ClearVarDefsTest extends Sugar_PHPUnit_Framework_TestCase
{
    const MODULE = 'PreScript';

    public function setUp()
    {
        global $beanList, $dictionary;
        parent::setUp();
        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user', array(false, true));
        $beanList = array(
            self::MODULE => 'PreScriptBean',
            'Users' => 'ClearVardefsMockUser'
        );
        $dictionary[self::MODULE] = array(
            'relationships' => array(
                //need to be fixed with correct module name
                'b' => array(
                    'lhs_module' => 'Users',
                    'rhs_module' => 'prescript',
                    'relationship_type' => 'one-to-many',
                ),
            ),
            'fields' => array(
                //bad relationship
                'a' => array(
                    'type' => 'link',
                    'name' => 'a',
                    'relationship' => 'b1'
                ),
                //relate type with wrong link
                'c' => array(
                    'name' => 'c',
                    'type' => 'relate',
                    'link' => 'a',
                ),
                //fields contains wrong field
                'd' => array(
                    'name' => 'd',
                    'type' => 'text',
                    'fields' => array('e', 'g'),
                    'db_concat_fields' => array('e', 'g'),
                ),
                //normal field
                'e' => array(
                    'name' => 'e',
                    'type' => 'bool'
                ),
                //field with no type
                'g' => array(
                    'name' => 'g',
                ),
                //good relate field
                'h' => array(
                    'name' => 'f',
                    'relationship' => 'b',
                    'id_name' => 'i',
                    'type' => 'relate',
                    'link' => 'link',
                ),
                //field type should be `id`
                'i' => array(
                    'name' => 'i',
                    'relationship' => 'b',
                    'type' => 'link'
                ),
                //good link
                'link' => array(
                    'type' => 'link',
                    'relationship' => 'b',
                    'side' => 'RHS',
                )
            )
        );
        SugarRelationshipFactory::deleteCache();
        SugarRelationshipFactory::rebuildCache();
        VardefManager::$linkFields = array();
    }

    protected function tearDown()
    {
        global $dictionary;
        unset($dictionary[self::MODULE]);
        SugarRelationshipFactory::deleteCache();
        SugarRelationshipFactory::rebuildCache();
        SugarTestHelper::tearDown();
    }

    /**
     * Test script results.
     */
    public function testRun()
    {
        global $current_user;
        $path = sugar_cached(__CLASS__);
        $upgradeDriver = new TestUpgrader($current_user);
        $upgradeDriver->context = array(
            'source_dir' => $path
        );

        $script = $this->getMockBuilder('SugarUpgradeClearVarDefs')
            ->setMethods(array(
                'cleanCache',
                'deleteFieldFile',
                'deleteRelationshipFiles',
                'writeDef',
                'updateLinks',
                'updateRelationshipDefinition'
            ))
            ->setConstructorArgs(array($upgradeDriver))
            ->getMock();

        $script->expects($this->once())
            ->method('cleanCache');

        $script->expects($this->exactly(3))
            ->method('deleteFieldFile');

        $script->expects($this->once())
            ->method('deleteRelationshipFiles')
            ->with($this->isInstanceOf('PreScriptBean'), 'b1');

        $script->expects($this->once())
            ->method('updateLinks')
            ->with(array('i' => 1), $this->isInstanceOf('PreScriptBean'));

        $script->expects($this->once())
            ->method('writeDef')
            ->with(
                array(
                    'fields' => array('e'),
                    'db_concat_fields' => array('e'),
                    'name' => 'd',
                    'type' => 'text'
                ),
                ''
            );

        $script->expects($this->once())
            ->method('updateRelationshipDefinition')
            ->with(
                $this->isInstanceOf('PreScriptBean'),
                array(
                    'lhs_module' => 'Users',
                    'rhs_module' => 'PreScript',
                    'relationship_type' => 'one-to-many',
                    'name' => 'b',
                )
            );

        $script->run();

        $bean = SugarTestReflection::callProtectedMethod(
            $script,
            'getBean',
            array(strtolower((self::MODULE)))
        );
        $this->assertEquals('PreScriptBean', get_class($bean));
    }
}

/**
 * Mock for SugarBean
 */
class PreScriptBean extends SugarBean
{
    public $object_name = ClearVarDefsTest::MODULE;

    public $module_name = ClearVarDefsTest::MODULE;
}


class ClearVardefsMockUser extends User
{
    public function getFieldDefinitions($property = '', $filter = array()) {
        return array();
    }
}
