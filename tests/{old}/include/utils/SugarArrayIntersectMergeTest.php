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

require_once 'include/utils.php';

class SugarArrayIntersectMergeTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testSubArrayOrderIsPreserved()
    {
        $array1 = array(
            'dog' => array(
                'dog1' => 'dog1',
                'dog2' => 'dog2',
                'dog3' => 'dog3',
                'dog4' => 'dog4',
            )
        );

        $array2 = array(
            'dog' => array(
                'dog2' => 'dog2',
                'dog1' => 'dog1',
                'dog3' => 'dog3',
                'dog4' => 'dog4',
            )
        );

        $results = sugarArrayIntersectMerge($array1, $array2);

        $keys1 = array_keys($results['dog']);
        $keys2 = array_keys($array1['dog']);

        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals($keys1[$i], $keys2[$i]);
        }
    }

    public function testIntersectMerge()
    {
        $foo = array(
            'one' => 123,
            'two' => 123,
            'foo' => array(
                'int' => 123,
                'foo' => 'bar',
            ),
            'bar' => array(
                'int' => 123,
                'foo' => 'bar',
            ),
        );
        $bar = array(
            'one' => 123,
            'two' => 321,
            'three' => 321,
            'foo' => array(
                'int' => 321,
                'bar' => 'foo',
            ),
        );

        $expected = array(
            'one' => 123,
            'two' => 321,
            'foo' => array(
                'int' => 321,
                'foo' => 'bar',
            ),
            'bar' => array(
                'int' => 123,
                'foo' => 'bar',
            ),
        );
        $this->assertEquals(sugarArrayIntersectMerge($foo, $bar), $expected);
        // insure that internal functions can't duplicate behavior
        $this->assertNotEquals(array_merge($foo, $bar), $expected);
        $this->assertNotEquals(array_merge_recursive($foo, $bar), $expected);
    }

    public function testDaysOfTheWeek()
    {
        $gimp = array(
            'days_of_the_week' => array('mon', 'tues', 'weds', 'thurs', 'fri', 'sat', 'sun'),
        );
        $dom = array(
            'days_of_the_week' => array('1', '2', '3', '4'),
        );

        $expected = array(
            'days_of_the_week' => array('1', '2', '3', '4', 'fri', 'sat', 'sun'),
        );
        $this->assertEquals(sugarArrayIntersectMerge($gimp, $dom), $expected);
        // insure that internal functions can't duplicate behavior
        $this->assertNotEquals(array_merge($gimp, $dom), $expected);
        $this->assertNotEquals(array_merge_recursive($gimp, $dom), $expected);
    }

    public function testDuration()
    {
        $gimp = array(
            'duration' => array(
                '86400' => '1 day',
                '172800' => '2 days',
                '259200' => '3 days',
                '867-5309' => 'jenny',
            ),
        );
        $dom = array(
            'duration' => array(
                '86400' => '1 day translated',
                '259200' => '3 days translated',
                '123456' => '25 years translated',
                '867-5309' => '',  // Should not replace gimp since this is empty
            ),
        );
        $expected = array(
            'duration' => array(
                '86400' => '1 day translated',
                '172800' => '2 days',
                '259200' => '3 days translated',
                '867-5309' => 'jenny',
            ),
        );
        $this->assertEquals(sugarArrayIntersectMerge($gimp, $dom), $expected);
        // insure that internal functions can't duplicate behavior
        $this->assertNotEquals(array_merge($gimp, $dom), $expected);
        $this->assertNotEquals(array_merge_recursive($gimp, $dom), $expected);
    }
}
