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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/9_AddTagFieldToViews.php';

/**
 * Test for adding the tag field to those record views that need it
 */
class SugarUpgradeAddTagFieldToViewsTest extends UpgradeTestCase
{
    /**
     * Tests adding the tag field to metadata
     * @param array $data The data to test
     * @param array $expect Expectations
     * @dataProvider addTagToMetadataProvider
     */
    public function testAddTagToMetadata($data, $expect)
    {
        $ug = new SugarUpgradeAddTagFieldToViewsMock($this->upgrader);
        $actual = $ug->addTagToMetadata($data);
        $this->assertEquals($actual, $expect['return']);
        $this->assertEquals($data, $expect['data']);
    }

    /**
     * Tests if metadata has the tag field
     * @param array $panels The panels to test
     * @param boolean $expect Expectation
     * @dataProvider hasTagFieldProvider
     */
    public function testHasTagField($panels, $expect)
    {
        $ug = new SugarUpgradeAddTagFieldToViews($this->upgrader);
        $actual = $ug->hasTagField($panels);
        $this->assertEquals($actual, $expect);
    }

    public function addTagToMetadataProvider()
    {
        return array(
            // Test is taggable and does not have tag field
            array(
                'data' => array(
                    'module' => 'Testa',
                    'client' => 'test', // Needed for the logger
                    'view' => 'test', // Needed for the logger
                    'defs' => array(
                        'panels' => array(
                            array(
                                'fields' => array('name', 'age', 'color',),
                            ),
                            array(
                                'fields' => array('range', 'token',),
                            ),
                            array(
                                'fields' => array('foo', 'bar',),
                            ),
                        ),
                    ),
                ),
                'expect' => array(
                    'return' => true,
                    'data' => array(
                        'module' => 'Testa',
                        'client' => 'test', // Needed for the logger
                        'view' => 'test', // Needed for the logger
                        'defs' => array(
                            'panels' => array(
                                array(
                                    'fields' => array('name', 'age', 'color',),
                                ),
                                array(
                                    'fields' => array('range', 'token', array('name' => 'tag', 'span' => 12),),
                                ),
                                array(
                                    'fields' => array('foo', 'bar',),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            // Test is taggable and does have tag
            array(
                'data' => array(
                    'module' => 'Testb',
                    'client' => 'test', // Needed for the logger
                    'view' => 'test', // Needed for the logger
                    'defs' => array(
                        'panels' => array(
                            array(
                                'fields' => array('name', 'age', 'color',),
                            ),
                            array(
                                'fields' => array('range', 'token', array('name' => 'tag', 'span' => 12),),
                            ),
                            array(
                                'fields' => array('foo', 'bar',),
                            ),
                        ),
                    ),
                ),
                'expect' => array(
                    'return' => false,
                    'data' => array(
                        'module' => 'Testb',
                        'client' => 'test', // Needed for the logger
                        'view' => 'test', // Needed for the logger
                        'defs' => array(
                            'panels' => array(
                                array(
                                    'fields' => array('name', 'age', 'color',),
                                ),
                                array(
                                    'fields' => array('range', 'token', array('name' => 'tag', 'span' => 12),),
                                ),
                                array(
                                    'fields' => array('foo', 'bar',),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            // Test is not taggable
            array(
                'data' => array(
                    'module' => 'Testc',
                    'client' => 'test', // Needed for the logger
                    'view' => 'test', // Needed for the logger
                    'defs' => array(
                        'panels' => array(
                            array(
                                'fields' => array('name', 'age', 'color',),
                            ),
                            array(
                                'fields' => array('range', 'token',),
                            ),
                            array(
                                'fields' => array('foo', 'bar',),
                            ),
                        ),
                    ),
                ),
                'expect' => array(
                    'return' => false,
                    'data' => array(
                        'module' => 'Testc',
                        'client' => 'test', // Needed for the logger
                        'view' => 'test', // Needed for the logger
                        'defs' => array(
                            'panels' => array(
                                array(
                                    'fields' => array('name', 'age', 'color',),
                                ),
                                array(
                                    'fields' => array('range', 'token',),
                                ),
                                array(
                                    'fields' => array('foo', 'bar',),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    public function hasTagFieldProvider()
    {
        return array(
            array(
                'panels' => array(
                    array(
                        'fields' => array('name', 'age', 'tag', 'color',),
                    ),
                ),
                'expect' => true,
            ),
            array(
                'panels' => array(
                    array(
                        'fields' => array('name', 'age', 'color',),
                    ),
                    array(
                        'fields' => array('range', 'tag',),
                    ),
                ),
                'expect' => true,
            ),
            array(
                'panels' => array(
                    array(
                        'fields' => array('name', 'age', 'color',),
                    ),
                    array(
                        'fields' => array('range', 'date',),
                    ),
                ),
                'expect' => false,
            ),
            array(
                'panels' => array(
                    array(
                        'fields' => array('name', 'age', 'color',),
                    ),
                    array(
                        'fields' => array('range', 'token',),
                    ),
                    array(
                        'fields' => array('test', 'foo', array('name' => 'tag', 'span' => 12),),
                    ),
                ),
                'expect' => true,
            ),
        );
    }
}

class SugarUpgradeAddTagFieldToViewsMock extends SugarUpgradeAddTagFieldToViews
{
    /**
     * Checks if a module is taggable or not
     * @param string $module The module to check
     * @return boolean
     */
    protected function isTaggable($module)
    {
        if ($module === 'Testa' || $module === 'Testb') {
            return true;
        }

        return false;
    }
}
