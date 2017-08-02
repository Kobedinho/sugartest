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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Mapping\Property;

use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldProperty;

/**
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldProperty
 *
 */
class MultiFieldPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::setType
     * @covers ::getMapping
     * @dataProvider providerTestSetType
     */
    public function testSetType($type)
    {
        $expected = array('type' => $type);
        $field = new MultiFieldProperty();
        $field->setType($type);
        $this->assertSame($expected, $field->getMapping());
    }

    public function providerTestSetType()
    {
        return array(
            array('string'),
            array('float'),
            array('double'),
            array('byte'),
            array('short'),
            array('integer'),
            array('long'),
            array('token_count'),
            array('date'),
            array('boolean'),
        );
    }

    /**
     * @covers ::setType
     */
    public function testSetTypeInvalid()
    {
        $this->setExpectedException(
            "Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException",
            "Invalid type 'foobar' for MultiFieldProperty"
        );

        $field = new MultiFieldProperty();
        $field->setType('foobar');
    }
}
