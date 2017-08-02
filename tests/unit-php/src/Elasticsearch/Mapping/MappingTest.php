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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Mapping;

use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\RawProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\ObjectProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldProperty;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping
 *
 */
class MappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::excludeFromSource
     * @covers ::getSourceExcludes
     */
    public function testExcludeFromSource()
    {
        $mapping = new Mapping('FooBar');
        $this->assertSame(array(), $mapping->getSourceExcludes());
        $mapping->excludeFromSource('field1');
        $mapping->excludeFromSource('field2');
        $mapping->excludeFromSource('field1');
        $this->assertSame(array('field1', 'field2'), $mapping->getSourceExcludes());
    }

    /**
     * @covers ::getModule
     */
    public function testGetModule()
    {
        $mapping = new Mapping('ModuleName');
        $this->assertSame('ModuleName', $mapping->getModule());
    }

    /**
     * @covers ::hasProperty
     * @covers ::getProperty
     */
    public function testHasProperty()
    {
        $mapping = new Mapping('FooBar');
        $this->assertFalse($mapping->hasProperty('foobar'));

        $property = new RawProperty();
        $mapping->addRawProperty('foobar', $property);
        $this->assertTrue($mapping->hasProperty('foobar'));
        $this->assertSame($property, $mapping->getProperty('foobar'));
    }

    /**
     * @covers ::getProperty
     */
    public function testGetPropertyNotExist()
    {
        $this->setExpectedException(
            "Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException",
            "Trying to get non-existing property 'fieldx' for 'FooBar'"
        );

        $mapping = new Mapping('FooBar');
        $mapping->getProperty('fieldx');
    }

    /**
     * @covers ::addRawProperty
     * @covers ::addProperty
     */
    public function testAddRawProperty()
    {
        $mapping = new Mapping('FooBar');
        $property = new RawProperty();
        $mapping->addRawProperty('fieldx', $property);
        $this->assertSame($property, $mapping->getProperty('fieldx'));
    }

    /**
     * @covers ::addRawProperty
     * @covers ::addProperty
     */
    public function testAddRawPropertyExistingFailure()
    {
        $this->setExpectedException(
            "Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException",
            "Cannot redeclare field 'fieldx' for module 'FooBar'"
        );

        $mapping = new Mapping('FooBar');
        $mapping->addRawProperty('fieldx', new RawProperty());
        $mapping->addRawProperty('fieldx', new RawProperty());
    }

    /**
     * @covers ::addObjectProperty
     * @covers ::addProperty
     */
    public function testAddObjectProperty()
    {
        $mapping = new Mapping('FooBar');
        $property = new ObjectProperty();
        $mapping->addObjectProperty('fieldy', $property);
        $this->assertSame($property, $mapping->getProperty('fieldy'));
    }

    /**
     * @covers ::addObjectProperty
     * @covers ::addProperty
     */
    public function testAddObjectPropertyExistingFailure()
    {
        $this->setExpectedException(
            "Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException",
            "Cannot redeclare field 'fieldy' for module 'FooBar'"
        );

        $mapping = new Mapping('FooBar');
        $mapping->addRawProperty('fieldy', new ObjectProperty());
        $mapping->addRawProperty('fieldy', new ObjectProperty());
    }

    /**
     * Testing calling only addNotAnalyzedField
     * @covers ::addNotAnalyzedField
     * @covers ::createMultiFieldBase
     * @covers ::compile
     */
    public function testAddNotAnalyzedField()
    {
        $mapping = new Mapping('FooBar');
        $this->assertFalse($mapping->hasProperty('field1'));

        // add field1, no copyTo
        $mapping->addNotAnalyzedField('field1');
        $this->assertTrue($mapping->hasProperty('field1'));
        $this->assertSame(array(
            'field1' => array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'include_in_all' => false,
            ),
        ), $mapping->compile());

        // add same field again, with one copyTo
        $mapping->addNotAnalyzedField('field1', array('field1_copy1'));
        $this->assertSame(array(
            'field1' => array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'include_in_all' => false,
                'copy_to' => array(
                    'field1_copy1',
                )
            ),
        ), $mapping->compile());

        // add new field, with one copyTo
        $mapping->addNotAnalyzedField('field2', array('field2_copy1'));
        $this->assertTrue($mapping->hasProperty('field2'));
        $this->assertSame(array(
            'field1' => array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'include_in_all' => false,
                'copy_to' => array(
                    'field1_copy1',
                )
            ),
            'field2' => array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'include_in_all' => false,
                'copy_to' => array(
                    'field2_copy1',
                )
            ),
        ), $mapping->compile());

        // add field1 again, with one more copyTo
        $mapping->addNotAnalyzedField('field1', array('field1_copy2'));
        $this->assertSame(array(
            'field1' => array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'include_in_all' => false,
                'copy_to' => array(
                    'field1_copy1',
                    'field1_copy2',
                )
            ),
            'field2' => array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'include_in_all' => false,
                'copy_to' => array(
                    'field2_copy1',
                )
            ),
        ), $mapping->compile());
    }

    /**
     * Testing calling only addNotIndexedField
     * @covers ::addNotIndexedField
     * @covers ::createMultiFieldBase
     * @covers ::compile
     */
    public function testAddNotIndexedField()
    {
        $mapping = new Mapping('FooBar');
        $this->assertFalse($mapping->hasProperty('field1'));

        // add field1, no copyTo
        $mapping->addNotIndexedField('field1');
        $this->assertTrue($mapping->hasProperty('field1'));
        $this->assertSame(array(
            'field1' => array(
                'type' => 'string',
                'index' => 'no',
                'include_in_all' => false,
            ),
        ), $mapping->compile());

        // add same field again, with one copyTo
        $mapping->addNotIndexedField('field1', array('field1_copy1'));
        $this->assertSame(array(
            'field1' => array(
                'type' => 'string',
                'index' => 'no',
                'include_in_all' => false,
                'copy_to' => array(
                    'field1_copy1',
                )
            ),
        ), $mapping->compile());

        // add new field, with one copyTo
        $mapping->addNotIndexedField('field2', array('field2_copy1'));
        $this->assertTrue($mapping->hasProperty('field2'));
        $this->assertSame(array(
            'field1' => array(
                'type' => 'string',
                'index' => 'no',
                'include_in_all' => false,
                'copy_to' => array(
                    'field1_copy1',
                )
            ),
            'field2' => array(
                'type' => 'string',
                'index' => 'no',
                'include_in_all' => false,
                'copy_to' => array(
                    'field2_copy1',
                )
            ),
        ), $mapping->compile());

        // add field1 again, with one more copyTo
        $mapping->addNotIndexedField('field1', array('field1_copy2'));
        $this->assertSame(array(
            'field1' => array(
                'type' => 'string',
                'index' => 'no',
                'include_in_all' => false,
                'copy_to' => array(
                    'field1_copy1',
                    'field1_copy2',
                )
            ),
            'field2' => array(
                'type' => 'string',
                'index' => 'no',
                'include_in_all' => false,
                'copy_to' => array(
                    'field2_copy1',
                )
            ),
        ), $mapping->compile());
    }

    /**
     * Testing calling only addMultiField
     * @covers ::addMultiField
     * @covers ::createMultiFieldBase
     * @covers ::compile
     */
    public function testAddMultiField()
    {
        $mapping = new Mapping('FooBar');
        $this->assertFalse($mapping->hasProperty('base1'));

        // add base1.field1
        $mapping->addMultiField('base1', 'field1', new MultiFieldProperty());
        $this->assertTrue($mapping->hasProperty('base1'));
        $this->assertSame(array(
            'base1' => array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'include_in_all' => false,
                'fields' => array(
                    'field1' => array('type' => 'string'),
                ),
            ),
        ), $mapping->compile());

        // add base1.field2
        $mapping->addMultiField('base1', 'field2', new MultiFieldProperty());
        $this->assertSame(array(
            'base1' => array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'include_in_all' => false,
                'fields' => array(
                    'field1' => array('type' => 'string'),
                    'field2' => array('type' => 'string'),
                ),
            ),
        ), $mapping->compile());
    }

    /**
     * Test addMultiField on a non-multifield base
     * @covers ::addMultiField
     * @covers ::createMultiFieldBase
     */
    public function testAddMultiFieldInvalidBase()
    {
        $this->setExpectedException(
            "Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException",
            "Field 'field1' is not a multi field"
        );

        $mapping = new Mapping('FooBar');
        $mapping->addRawProperty('field1', new RawProperty());
        $mapping->addMultiField('field1', 'multi1', new MultiFieldProperty());
    }

    /**
     * Test addMultiField on a non-multifield base
     * @covers ::addMultiField
     * @covers ::createMultiFieldBase
     */
    public function testAddMultiFieldDuplicateField()
    {
        $this->setExpectedException(
            "Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException",
            "Field 'multi1' already exists as multi field"
        );

        $mapping = new Mapping('FooBar');
        $mapping->addMultiField('field1', 'multi1', new MultiFieldProperty());
        $mapping->addMultiField('field1', 'multi1', new MultiFieldProperty());
    }

    /**
     * Combination testing: Create multi fields on top of not indexed base field
     * @coversNothing
     * @dataProvider providerTestAddMultiFieldCombination
     */
    public function testAddMultiFieldCombination($baseMethod, $expected)
    {
        $mapping = new Mapping('FooBar');

        // create base field
        call_user_func(array($mapping, $baseMethod), 'base');

        // add base.field1
        $mapping->addMultiField('base', 'field1', new MultiFieldProperty());
        $this->assertTrue($mapping->hasProperty('base'));
        $this->assertSame($expected, $mapping->compile());
    }

    public function providerTestAddMultiFieldCombination()
    {
        return array(
            array(
                'addNotIndexedField',
                array(
                    'base' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'fields' => array(
                            'field1' => array('type' => 'string'),
                        ),
                    ),
                ),
            ),
            array(
                'addNotAnalyzedField',
                array(
                    'base' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'field1' => array('type' => 'string'),
                        ),
                    ),
                ),
            ),
        );
    }
}
