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


class Bug51311Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function providerBug51311()
    {
        return array(
            array(
                array (
                  'name' => 'contents',
                  'dbType' => 'longtext',
                  'type' => 'nvarchar',
                  'vname' => 'LBL_DESCRIPTION',
                  'isnull' => true,
                ),
                'user_preferences',
                'max'
            ),

            array(
                array (
                  'name' => 'contents',
                  'dbType'  => 'text',
                  'type' => 'nvarchar',
                  'vname' => 'LBL_DESCRIPTION',
                  'isnull' => true,
                ),
                'user_preferences',
                'max'
            ),

            array(
                array (
                  'name' => 'contents',
                  'dbType'  => 'image',
                  'type' => 'image',
                  'vname' => 'LBL_DESCRIPTION',
                  'isnull' => true,
                ),
                'user_preferences',
                '2147483647'
            ),

            array(
                array (
                  'name' => 'contents',
                  'dbType'  => 'ntext',
                  'type' => 'image',
                  'vname' => 'LBL_DESCRIPTION',
                  'isnull' => true,
                ),
                'user_preferences',
                '2147483646'
            ),

            array(
                array (
                  'name' => 'contents',
                  'dbType' => 'nvarchar',
                  'type' => 'nvarchar',
                  'vname' => 'LBL_DESCRIPTION',
                  'isnull' => true,
                ),
                'user_preferences',
                '255'
            ),
        );
    }

    /**
     * @dataProvider providerBug51311
     */
    public function testSqlSrvMassageFieldDef($fieldDef, $tablename, $len)
    {
        $manager = new SqlsrvManager();
        $manager->massageFieldDef($fieldDef, $tablename);
        $this->assertEquals($len, $fieldDef['len']);
    }
}
