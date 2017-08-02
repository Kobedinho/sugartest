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

namespace Sugarcrm\SugarcrmTests\ProcessManager\Field\Evaluator;

use Sugarcrm\Sugarcrm\ProcessManager\Field\Evaluator;

class MultienumTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * EvaluatorInterface object
     * @var EvaluatorInterface
     */
    protected $eval;

    public function setup()
    {
        parent::setup();
        $this->eval = new Evaluator\Multienum;
    }

    /**
     * Tests whether a value on a bean has changed
     * @dataProvider hasChangedProvider
     * @param SugarBean $bean SugarBean to test with
     * @param string $name Name of the field to test
     * @param array $data Data array to test
     * @param boolean $expect Expectation
     */
    public function testHasChanged($bean, $name, $data, $expect)
    {
        $this->eval->init($bean, $name, $data);
        $actual = $this->eval->hasChanged();
        $this->assertEquals($expect, $actual);
    }

    public function hasChangedProvider()
    {
        // Simple bean setup to cover all test cases
        $bean = \BeanFactory::newBean('Bugs');
        $bean->test1 = '^Test1^,^Test2^,^Test4^';
        $bean->test3 = '^Test1^,^Test2^,^Test4^';
        $bean->test4 = '^Test9^,^Test7^,^Test3^';
        $bean->test5 = '^Test5^,^Test3^,^Test1^';
        $bean->test6 = '^Test2^,^Test6^,^Test4^';
        $bean->test7 = '^Test1^,^Test7^,^Test5^';

        return array(
            // Tests no data value given
            array(
                'bean' => $bean,
                'name' => 'test1',
                'data' => array(),
                'expect' => false,
            ),
            // Tests no bean property set
            array(
                'bean' => $bean,
                'name' => 'test2',
                'data' => array('test2' => array('Test1')),
                'expect' => false,
            ),
            // Tests no change of data values or order
            array(
                'bean' => $bean,
                'name' => 'test3',
                'data' => array('test3' => array('Test1', 'Test2', 'Test4')),
                'expect' => false,
            ),
            // Tests no change of data values with different order
            array(
                'bean' => $bean,
                'name' => 'test4',
                'data' => array('test4' => array('Test7', 'Test3', 'Test9')),
                'expect' => false,
            ),
            // Tests value change by adding more
            array(
                'bean' => $bean,
                'name' => 'test5',
                'data' => array('test5' => array('Test5', 'Test3', 'Test1', 'Test7')),
                'expect' => true,
            ),
            // Tests value change by removing
            array(
                'bean' => $bean,
                'name' => 'test6',
                'data' => array('test6' => array('Test2', 'Test6')),
                'expect' => true,
            ),
            // Tests value change
            array(
                'bean' => $bean,
                'name' => 'test7',
                'data' => array('test7' => array('Test8', 'Test6', 'Test4')),
                'expect' => true,
            ),
        );
    }
}
