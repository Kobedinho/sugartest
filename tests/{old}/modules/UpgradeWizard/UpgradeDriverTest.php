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
 * UpgradeDriverTest
 *
 * This class tests functions inside the file UpgradeDriver.php.
 *
 */

require_once('modules/UpgradeWizard/CliUpgrader.php');

class UpgradeDriverTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $driver;

    public function setUp()
    {
        $this->driver = new CliUpgrader();
    }

    public function tearDown()
    {
        unset($this->driver);
    }

    /**
     * This function tests cases for different combinations of parameters for configs.
     *
     * @param array  $old  : the old configs from "config.php" before upgrade.
     * @param array  $over : the override configs from "config_override.php".
     * @param array  $new  : the new configs generated during the upgrade.
     * @param array  $expected : the expected result of the test case.
     *
     * @dataProvider providers_test_genConfigs
     */
    public function test_genConfigs($old, $over, $new, $expected)
    {
        $this->assertEquals($expected, $this->driver->genConfigs($old, $over, $new));
    }

    /**
     * This function provides inputs for test_genConfigs().
     *
     * @return array the expected values of the test.
     */
    public function providers_test_genConfigs()
    {
        $returnArray = array(
            ///////////////////
            //Cases for array
            //////////////////
            array( // Case: Same in $over and $new, but not in $old
                array(),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array(),
            ),
            array( // Case: Same in $over and $new, but different in $old
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Feb 15, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Feb 15, 2014')),
            ),
            array( // Case: Same in all three
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
            ),
            array( // Case: Same in $over and $new, but extra elements than $old
                array('WRALholidays' => array('0' => 'Jan 1, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014')),
            ),
            array( // Case: Only in new, but not in either $over or $old
                array(),
                array(),
                array('WRALholidays' => array('0' => 'Jan 1, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014')),
            ),
            array( // Case: Different in $old and $over, but not in $new
                array('WRALholidays' => array('0' => 'Jan 1, 2014')),
                array('WRALholidays' => array('1' => 'Jan 13, 2014')),
                array(),
                array('WRALholidays' => array('0' => 'Jan 1, 2014')),
            ),
            array( // Case: Incremental in $old, $over, and $new
                array('WRALholidays' => array('0' => 'Jan 1, 2014')),
                array('WRALholidays' => array('1' => 'Jan 13, 2014')),
                array('WRALholidays' => array('2' => 'Feb 15, 2014')),
                array('WRALholidays' => array('0' => 'Jan 1, 2014', '2' => 'Feb 15, 2014')),
            ),
            array( // Case: Different values for the same key in $old, $over, and $new
                array('WRALholidays' => array('0' => 'Jan 1, 2014')),
                array('WRALholidays' => array('0' => 'Jan 13, 2014')),
                array('WRALholidays' => array('0' => 'Feb 15, 2014')),
                array('WRALholidays' => array('0' => 'Feb 15, 2014')),
            ),
            ///////////////////
            //Cases for boolean
            //////////////////
            array( // Case: boolean value, same $over and $new but not in $old
                array(),
                array('fts_disable_notification' => true),
                array('fts_disable_notification' => true),
                array(),
            ),
            array( // Case: boolean value, same in $over and new, but different in $old
                array('fts_disable_notification' => false),
                array('fts_disable_notification' => true),
                array('fts_disable_notification' => true),
                array('fts_disable_notification' => false),
            ),
            array( // Case: boolean value, only in $new but not in $over or $old
                array(),
                array(),
                array('fts_disable_notification' => true),
                array('fts_disable_notification' => true),
            ),
            ///////////////////
            //Cases for string
            //////////////////
            array( // Case: string value, same $over and $new but not in $old
                array(),
                array('chartEngine' => 'nvd3'),
                array('chartEngine' => 'nvd3'),
                array(),
            ),
            array( // Case: string value, same in $over and new, but different in $old
                array('chartEngine' => 'Jit'),
                array('chartEngine' => 'nvd3'),
                array('chartEngine' => 'nvd3'),
                array('chartEngine' => 'Jit'),
            ),
            array( // Case: string value, only in $new but not in $over or $old
                array(),
                array(),
                array('chartEngine' => 'nvd3'),
                array('chartEngine' => 'nvd3'),
            ),
            array( // Case: string value, Different in everything
                array('chartEngine' => 'Jit'),
                array('chartEngine' => 'nvd3'),
                array('chartEngine' => 'foo'),
                array('chartEngine' => 'foo'),
            ),
            ///////////////////
            //Cases for number
            //////////////////
            array( // Case: number value, same $over and $new but not in $old
                array(),
                array('js_lang_version' => 2),
                array('js_lang_version' => 2),
                array(),
            ),
            array( // Case: number value, same in $over and new, but different in $old
                array('js_lang_version' => 1),
                array('js_lang_version' => 2),
                array('js_lang_version' => 2),
                array('js_lang_version' => 1),
            ),
            array( // Case: number value, only in $new but not in $over or $old
                array(),
                array(),
                array('js_lang_version' => 2),
                array('js_lang_version' => 2),
            ),
            array( // Case: number value, different int everything
                array('js_lang_version' => 1),
                array('js_lang_version' => 2),
                array('js_lang_version' => 3),
                array('js_lang_version' => 3),
            ),
            ///////////////////
            //Cases for deep array
            //////////////////
            array( // Case: same $over and $new but not in $old
                array(),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
                array(),
            ),
            array( // Case: same in $over and new, but different in $old
                array('foo' => array('bar1' => array('1' => '100'), 'bar2'=> array('2' => '200'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
                array('foo' => array('bar1' => array('1' => '100'), 'bar2'=> array('2' => '200'))),
            ),
            array( // Case: only in $new but not in $over or $old
                array(),
                array(),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
            ),
            array( // Case: $new and $over have different values in deep level
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('3' => '30'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('3' => '30'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
            ),
            array( // Case: $new and $over have incremental values in deep level
                array('foo' => array('bar1' => array('1' => '10'))),
                array('foo' => array('bar1' => array('1' => '10', '3' => '30'))),
                array('foo' => array('bar1' => array('1' => '10', '3' => '30'))),
                array('foo' => array('bar1' => array('1' => '10'))),
            ),
            array( // Case: $new has values in deep level than $old, and $over is empty
                array('foo' => array('bar1' => array('1' => '10'))),
                array(),
                array('foo' => array('bar1' => array('1' => '10', '3' => '30'))),
                array('foo' => array('bar1' => array('1' => '10', '3' => '30'))),
            ),
            array( // Case: $old, $over and $new have different values in deep level
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '20'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '30'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '50'))),
                array('foo' => array('bar1' => array('1' => '10'), 'bar2'=> array('2' => '50'))),
            ),
        );
        return $returnArray;
    }

}

