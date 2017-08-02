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
  * Class KBUsefulnessRelationshipTest
 */
class KBUsefulnessRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Definition for relationship.
     * @var array
     */
    protected $def = array(
        'name' => 'rel',
        'join_table' => 'jt',
        'true_relationship_type' => 'many-to-many',
        'primary_flag_column' => 'flag',
        'lhs_module' => 'Users',
        'rhs_module' => 'KBContents',
    );

    /**
     * Check required where condition for relationship.
     * @param array $params params to pass into testing method.
     * @dataProvider getWhereParams
     */
    public function testGetWhere($params)
    {

        $rel = new KBUsefulnessRelationship($this->def);
        $rel->primaryOnly = true;
        $res = SugarTestReflection::callProtectedMethod($rel, 'getRoleWhere', $params);
        $this->assertEquals(' AND jt.flag = 1', $res);
        $rel->primaryOnly = false;
        $res = SugarTestReflection::callProtectedMethod($rel, 'getRoleWhere', $params);
        $this->assertNotEquals(' AND jt.flag = 1', $res);
    }

    /**
     * Data provider for test
     * @return array
     */
    public function getWhereParams()
    {
        return array(
            array(
                array(
                    '',
                    true,
                    true,
                ),
            ),
            array(
                array(
                    'jt',
                    false,
                    true,
                ),
            ),
            array(
                array(
                    'jt',
                    true,
                    false,
                ),
            ),
            array(
                array(
                    'jt',
                    false,
                    false,
                ),
            ),
        );
    }
}
