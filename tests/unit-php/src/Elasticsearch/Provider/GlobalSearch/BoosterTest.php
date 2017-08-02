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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Booster;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Booster
 *
 */
class BoosterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::setWeighted
     * @covers ::getBoostedField
     * @covers ::getBoostValue
     * @covers ::normalizeBoost
     * @covers ::weight
     * @dataProvider dataProviderTestGetBoostedField
     *
     * @param array $weighted
     * @param string $field
     * @param array $defs
     * @param string $type
     * @param string $expected
     */
    public function testGetBoostedField(array $weighted, $field, array $defs, $type, $expected)
    {
        $bh = new Booster();
        $bh->setWeighted($weighted);
        $this->assertEquals($expected, $bh->getBoostedField($field, $defs, $type));
    }

    public function dataProviderTestGetBoostedField()
    {
        return array(
            array(
                array(),
                'field.sub',
                array(),
                'foo',
                'field.sub^1',
            ),
            array(
                array(),
                'field.sub',
                array('full_text_search' => array()),
                'foo',
                'field.sub^1',
            ),
            array(
                array(),
                'field.sub',
                array('full_text_search' => array('boost' => 2.138)),
                'foo',
                'field.sub^2.14',
            ),
            array(
                array('foo' => 0.5),
                'field.sub',
                array('full_text_search' => array('boost' => 2.138)),
                'foo',
                'field.sub^1.07',
            ),
        );
    }
}
