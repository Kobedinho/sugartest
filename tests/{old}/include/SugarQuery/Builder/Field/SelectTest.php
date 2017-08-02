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
 * @coversDefaultClass SugarQuery_Builder_Field_Select
 */
class SugarQuery_Builder_Field_SelectTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function expandFieldRelateOwnerDataProvider()
    {
        return array(
            array(
                array(
                    'name' => 'account_name',
                    'type' => 'relate',
                    'source' => 'non-db',
                    'rname' => 'name',
                    'module' => 'Accounts',
                ),
            ),
            array(
                array(
                    'name' => 'account_id',
                    'type' => 'relate',
                    'source' => 'non-db',
                    'rname' => 'id',
                    'module' => 'Accounts',
                ),
            ),
        );
    }

    /**
     * @dataProvider expandFieldRelateOwnerDataProvider
     * @covers ::expandField()
     */
    public function testExpandFieldRelateOwner($def)
    {
        $select = $this->getMockBuilder('SugarQuery_Builder_Field_Select')
            ->disableOriginalConstructor()
            ->setMethods(array('checkCustomField', 'addToSelect'))
            ->getMock();
        $select->def = $def;
        $select->jta = 'join_table_alias';
        $select->query = $this->createMock('SugarQuery');
        $select->query->select = $this->getMockBuilder('SugarQuery_Builder_Select')
            ->disableOriginalConstructor()
            ->setMethods(array('addField'))
            ->getMock();
        $select->query->select->expects($this->once())
            ->method('addField')
            ->with($this->equalTo('join_table_alias.assigned_user_id'));
        $select->expandField();
    }
}
