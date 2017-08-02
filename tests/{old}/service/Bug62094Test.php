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


require_once 'tests/{old}/service/SOAPTestCase.php';

class Bug62094Test extends SOAPTestCase
{
    protected $definition;

    public function setUp()
    {
        $this->definition = Array(
            'id' => 'a3468352-8fd0-ec13-708a-517087f79ada',
            'relationship_name' => 'accounts_meetings_1',
            'lhs_module' => 'Accounts',
            'lhs_table' => 'accounts',
            'lhs_key' => 'id',
            'rhs_module' => 'Meetings',
            'rhs_table' => 'meetings',
            'rhs_key' => 'id',
            'join_table' => 'accounts_meetings_1_c',
            'join_key_lhs' => 'accounts_meetings_1accounts_ida',
            'join_key_rhs' => 'accounts_meetings_1meetings_idb',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => '',
            'relationship_role_column_value' =>'',
            'reverse' => 0,
            'deleted' => 0,
            'readonly' => 1,
            'rhs_subpanel' => '',
            'lhs_subpanel' => '',
            'from_studio' => 1,
            'is_custom' => 1
        );

        parent::setUp();

    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetModuleFields()
    {
        $relationship = new AbstractRelationship62094($this->definition);
        $vardef = $relationship->getLinkFieldDefinition('Meetings', 'accounts_meetings_1');

        $this->assertNotEmpty($vardef['module'], 'get_module_fields failed: empty module returned');
        $this->assertNotEmpty($vardef['bean_name'], 'get_module_fields failed: empty bean_name returned');

        $relationship = new ActivitiesRelationship($this->definition);
        $vardef = SugarTestReflection::callProtectedMethod(
            $relationship,
            'getLinkFieldDefinition',
            array('Meetings', 'accounts_meetings_1')
        );

        $this->assertNotEmpty($vardef['module'], 'get_module_fields failed: empty module returned');
        $this->assertNotEmpty($vardef['bean_name'], 'get_module_fields failed: empty bean_name returned');
    }
}

class AbstractRelationship62094 extends AbstractRelationship {

    public function getLinkFieldDefinition(
        $sourceModule,
        $relationshipName,
        $right_side = false,
        $vname = '',
        $id_name = false
    ) {
        return parent::getLinkFieldDefinition($sourceModule , $relationshipName);
    }
}
