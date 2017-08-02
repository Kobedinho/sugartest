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

namespace Sugarcrm\SugarcrmTestsUnit\SearchEngine\MetaDataHelper;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper
 *
 */
class MetaDataHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getFtsFields
     * @dataProvider providerGetFtsFields
     *
     *
     * @param string $module
     * @param array $vardef
     * @param boolean $override
     * @param array $result
     */
    public function testGetFtsFields($module, array $vardef, $override, array $result)
    {
        $helper = $this->getMetaDataHelperMock(array('getModuleVardefs'));
        $helper->disableCache(true);

        $helper->expects($this->any())
            ->method('getModuleVardefs')
            ->will($this->returnValue($vardef));

        $fields = $helper->getFtsFields($module, $override);
        $this->assertEquals($result, $fields);
    }

    public function providerGetFtsFields()
    {
        return array(
            array(
                'Tasks',
                array(
                    'fields' => array(
                        'name' => array(
                            'name' => 'name',
                            'type' => 'name',
                            'full_text_search' => array('enabled' => true, 'searchable' => true),
                        ),
                        'description' => array(
                            'name' => 'description',
                            'type' => 'text',
                        ),
                        'work_log' => array(
                            'name' => 'work_log',
                            'type' => 'text',
                            'full_text_search' => array('enabled' => false),
                        ),
                        'date_modified' => array(
                            'name' => 'date_modified',
                            'type' => 'datetime',
                            'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                        ),
                    ),
                    'indices' => array(),
                    'relationship' => array(),
                ),
                true,
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'varchar',
                        'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                    ),
                ),
            ),
            // No type override
            array(
                'Tasks',
                array(
                    'fields' => array(
                        'name' => array(
                            'name' => 'name',
                            'type' => 'name',
                            'full_text_search' => array('enabled' => true, 'searchable' => true),
                        ),
                        'description' => array(
                            'name' => 'description',
                            'type' => 'text',
                        ),
                        'work_log' => array(
                            'name' => 'work_log',
                            'type' => 'text',
                            'full_text_search' => array('enabled' => false),
                        ),
                        'date_modified' => array(
                            'name' => 'date_modified',
                            'type' => 'datetime',
                            'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                        ),
                    ),
                    'indices' => array(),
                    'relationship' => array(),
                ),
                false,
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                    ),
                ),
            ),
        );
    }


    /**
     * @covers ::getAllAggDefsModule
     * @dataProvider providerGetModuleAggregations
     *
     *
     * @param string $module
     * @param array $vardef
     * @param array $result
     */
    public function testGetAllAggDefsModule($module, array $vardef, array $result)
    {
        $helper = $this->getMetaDataHelperMock(
            array('getFtsFields')
        );
        $helper->disableCache(true);

        $helper->expects($this->any())
            ->method('getFtsFields')
            ->will($this->returnValue($vardef));

        $fields = TestReflection::callProtectedMethod($helper, 'getAllAggDefsModule', array($module));
        $this->assertEquals($result, $fields);
    }

    public function providerGetModuleAggregations()
    {
        return array(
            array(
                'Tasks',
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    // module specific aggregation, no options
                    'description' => array(
                        'name' => 'description',
                        'type' => 'text',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => array(
                                'agg1' => array(
                                    'type' => 'term'
                                ),
                            )
                        ),
                    ),
                    // module specific aggregation, with options
                    'work_log' => array(
                        'name' => 'work_log',
                        'type' => 'text',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => array(
                                'agg2' => array(
                                    'type' => 'term',
                                    'options' => array('size' => 21, 'order' => 'desc'),
                                ),
                            ),
                        ),
                    ),
                    // cross module aggregation, no options
                    'date_entered' => array(
                        'name' => 'date_entered',
                        'type' => 'datetime',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => array(
                                'date_entered' => array(
                                    'type' => 'date_range',
                                ),
                            ),
                        ),
                    ),
                    // cross module aggregation, with options
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => array(
                                'date_modified' => array(
                                    'type' => 'date_range',
                                    'options' => array('from' => 'foo', 'to' => 'bar'),
                                ),
                            ),
                        ),
                    ),
                    // mix of cross and module specific aggregations
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'full_text_search' => array(
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => array(
                                'status_types' => array(
                                    'type' => 'term',
                                    'options' => array('foo' => 'bar1'),
                                ),
                                'status' => array(
                                    'type' => 'dropdown',
                                    'options' => array('foo' => 'bar2'),
                                ),
                                'status_something' => array(
                                    'type' => 'myStatus',
                                    'options' => array('foo' => 'bar3'),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'cross' => array(
                        'date_entered' => array(
                            'type' => 'date_range',
                            'options' => array(),
                        ),
                        'date_modified' => array(
                            'type' => 'date_range',
                            'options' => array('from' => 'foo', 'to' => 'bar'),
                        ),
                        'status' => array(
                            'type' => 'dropdown',
                            'options' => array('foo' => 'bar2'),
                        ),
                    ),
                    'module' => array(
                        'description.agg1' => array(
                            'type' => 'term',
                            'options' => array()
                        ),
                        'work_log.agg2' => array(
                            'type' => 'term',
                            'options' => array('size' => 21, 'order' => 'desc'),
                        ),
                        'status.status_types' => array(
                            'type' => 'term',
                            'options' => array('foo' => 'bar1'),
                        ),
                        'status.status_something' => array(
                            'type' => 'myStatus',
                            'options' => array('foo' => 'bar3'),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @covers ::isFieldSearchable
     * @dataProvider dataProviderIsFieldSearchable
     *
     * @param array $defs
     * @param boolean $isSearchable
     */
    public function testIsFieldSearchable(array $defs, $isSearchable)
    {
        $sut = $this->getMetaDataHelperMock();
        $this->assertSame($isSearchable, $sut->isFieldSearchable($defs));
    }

    public function dataProviderIsFieldSearchable()
    {
        return array(
            array(
                array(
                    'name' => 'foo1',
                    'full_text_search' => array('enabled' => true, 'searchable' => false),
                ),
                false,
            ),
            array(
                array(
                    'name' => 'foo2',
                    'full_text_search' => array('enabled' => true, 'searchable' => true),
                ),
                true,
            ),
            array(
                array(
                    'name' => 'foo3',
                    'full_text_search' => array('enabled' => true, 'boost' => 1),
                ),
                true,
            ),
            array(
                array(
                    'name' => 'foo4',
                    'full_text_search' => array('enabled' => true, 'boost' => 3, 'searchable' => true),
                ),
                true,
            ),
            array(
                array(
                    'name' => 'foo5',
                    'full_text_search' => array('enabled' => true),
                ),
                false,
            ),
        );
    }


    /**
     * @covers ::getCache
     * @covers ::setCache
     * @dataProvider providerTestInMemoryCache
     */
    public function testInMemoryCache($key, $value, $isCacheDisabled, $isRealCacheDisabled, $expected)
    {

        $helper = $this->getMetaDataHelperMock(
            array('getRealCacheKey', 'isRealCacheDisabled', 'getRealCache', 'setRealCache')
        );
        $helper->expects($this->any())
            ->method('getRealCacheKey')
            ->will($this->returnValue("mdmhelper_" . $key));
        $helper->expects($this->any())
            ->method('isRealCacheDisabled')
            ->will($this->returnValue($isRealCacheDisabled));
        $helper->expects($this->any())
            ->method('getRealCache')
            ->will($this->returnValue($value));
        $helper->expects($this->any())
            ->method('setRealCache')
            ->will($this->returnValue($value));


        TestReflection::setProtectedValue($helper, 'disableCache', $isCacheDisabled);
        TestReflection::setProtectedValue($helper, 'inMemoryCache', array());

        TestReflection::callProtectedMethod($helper, 'setCache', array($key, $value));
        $result = TestReflection::callProtectedMethod($helper, 'getCache', array($key));

        $this->assertSame($result, $expected);

    }

    public function providerTestInMemoryCache()
    {
        return array(
            array(
                "enabled_modules",
                array("Accounts", "Contacts"),
                false,
                false,
                array("Accounts", "Contacts"),
            ),
            array(
                "enabled_modules",
                array("Accounts", "Contacts"),
                true,
                true,
                array("Accounts", "Contacts"),
            ),
            array(
                "enabled_modules",
                array("Accounts", "Contacts"),
                true,
                false,
                null,
            ),
            array(
                "enabled_modules",
                array("Accounts", "Contacts"),
                false,
                true,
                array("Accounts", "Contacts"),
            ),
        );
    }

    /**
     * Get MetaDataHelper mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper
     */
    protected function getMetaDataHelperMock(array $methods = null)
    {
        $mock = $this->getMockBuilder('Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        // stub out our logger
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        TestReflection::setProtectedValue($mock, 'logger', $logger);

        return $mock;
    }
}
