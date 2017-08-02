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

use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\RawProperty;

/**
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\RawProperty
 *
 */
class RawPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::setMapping
     * @covers ::getMapping
     */
    public function testSetGetMapping()
    {
        $field = new RawProperty();

        // initial mapping
        $field->setMapping(array('foo' => 'bar'));
        $this->assertSame(array('foo' => 'bar'), $field->getMapping());

        // second mapping (overwrites previous)
        $field->setMapping(array('more' => 'beer'));
        $this->assertSame(array(
            'more' => 'beer',
        ), $field->getMapping());
    }

    /**
     * @covers ::addCopyTo
     */
    public function testAddCopyTo()
    {
        $field = new RawProperty();

        // add first copy to field
        $field->addCopyTo('foo');
        $this->assertSame(array(
            'copy_to' => array('foo'),
        ), $field->getMapping());

        // add second copy to field
        $field->addCopyTo('bar');
        $this->assertSame(array(
            'copy_to' => array(
                'foo',
                'bar',
            ),
        ), $field->getMapping());

        // add existing field again, should not change
        $field->addCopyTo('foo');
        $this->assertSame(array(
            'copy_to' => array(
                'foo',
                'bar',
            ),
        ), $field->getMapping());
    }
}
