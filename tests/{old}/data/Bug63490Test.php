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
 * @ticket 63490
 */
class Bug63490Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarBean
     */
    private static $bean;

    public static function setUpBeforeClass()
    {
        self::$bean = new SugarBean();
        self::$bean->table_name = 'bean';
        self::$bean->field_defs = array(
            'id' => array(),
            'name' => array(),
        );
    }

    /**
     * @param string $input
     * @param string $expected
     * @param bool $suppress_table_name
     * @param array $field_map
     *
     * @dataProvider correctProvider
     */
    public function testCorrectColumns(
        $input,
        $expected,
        $suppress_table_name = false,
        $field_map = array()
    ) {
        $actual = self::$bean->process_order_by(
            $input,
            null,
            $suppress_table_name,
            $field_map
        );
        $this->assertContains($expected, $actual);

        // Test order stability column
        $stability = $suppress_table_name ? 'id' : 'bean.id';
        if (!self::$bean->db->supports('order_stability')) {
            $msg = 'Missing ORDER BY stability column';
            $this->assertContains($stability, $actual, $msg);
        } else {
            $msg = 'Unexpected ORDER BY stability column';
            $this->assertNotContains($stability, $actual, $msg);
        }
    }

    /**
     * @param string $input
     *
     * @dataProvider incorrectProvider
     */
    public function testIncorrectColumns($input)
    {
        $actual = self::$bean->process_order_by($input);
        $this->assertNotContains($input, $actual);
    }

    /**
     * @param string $input
     *
     * @dataProvider duplicateProvider
     */
    public function testNoDuplicates($input)
    {
        $actual = self::$bean->process_order_by($input);
        $count = substr_count($actual, 'bean.id');
        $this->assertEquals(1, $count, 'There must be exactly one occurrence of bean.id in ORDER BY');
    }

    public static function correctProvider()
    {
        return array(
            // contains table anme
            array('bean.name DESC', 'bean.name DESC'),
            // existing field is accepted
            array('name', 'bean.name'),
            // valid order is accepted
            array('name asc', 'bean.name asc'),
            // order is case-insensitive
            array('name DeSc', 'bean.name DeSc'),
            // any white spaces are accepted
            array("\tname\t\nASC\n\r", 'bean.name ASC'),
            // invalid order is ignored
            array('name somehow', 'bean.name'),
            // everything after the first white space considered order
            array('name desc asc', 'bean.name'),
            // $suppress_table_name usage
            array('name', 'name', true),
            // $relate_field_map usage
            array('name desc', 'first_name desc, last_name desc', false, array(
                'name' => array('first_name', 'last_name'),
            )),
        );
    }

    public static function incorrectProvider()
    {
        return array(
            // non-existing field is removed
            array('title'),
            // non-existing field is removed together with order
            array('title asc'),
            // non-existing field with table name is removed
            array('bean.title')
        );
    }

    public static function duplicateProvider()
    {
        return array(
            array('id'),
            array('id asc'),
            array('id desc'),
        );
    }
}
