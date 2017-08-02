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

class TargetModuleDeveloperVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{   
    public function setUp() 
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(); 
    }

    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset( $GLOBALS['current_user']);
    }

    /**
     * @dataProvider targetVisiblityProvider
     */
    public function testDevVisibilityNoModules($isAdmin, $adminModules, $queryFrag)
    {
        global $current_user;
        global $db;

        $this->markTestIncomplete('[BR-3362] Testing SQL doesn\'t work with prepared statements');

        $bean = new Call();
        $bean->parent_type = "Accounts";
        $current_user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->getMock();
        $current_user->method('isAdmin')->willReturn($isAdmin);
        $current_user->method('getDeveloperModules')->willReturn($adminModules);
        $vis = new TargetModuleDeveloperVisibility($bean, array(
            'targetModuleField' => 'parent_type'
        ));
        $query = "";
        $query = $vis->addVisibilityWhere($query);

        if (empty($queryFrag)) {
            $this->assertEmpty($query);
        } else {
            $this->assertContains($queryFrag, $query);
        }

        $sq = new SugarQuery();
        $sq->from($bean);
        $sq->select('id');
        $vis->addVisibilityWhereQuery($sq);
        if (empty($queryFrag)) {
            $inWhere = array_filter($sq->where->conditions, function ($clause) {
                return !empty($clause->operator) && $clause->operator == 'IN';
            });
            $this->assertEmpty($inWhere);
        } else {
            $this->assertContains($queryFrag, $sq->compile()->getSQL());
        }

    }

    public function targetVisiblityProvider() {
        return array(
            //Global admin
            array(
                true,
                array(),
                false
            ),
            //Developer for Accounts and leads
            array(
                false,
                array('Accounts', 'Leads'),
                "Accounts"
            ),
            //Slightly non-sensical case. Should never occur when the SugarACLDeveloperForTarget is used in conjunction
            array(
                false,
                array(),
                "NULL"
            ),
        );



    }

}
