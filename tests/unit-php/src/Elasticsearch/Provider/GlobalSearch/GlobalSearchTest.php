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

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
 */
class GlobalSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getHandlers
     * @covers ::registerHandlers
     * @covers ::__construct
     */
    public function testGetHandlers()
    {
        $sut = new GlobalSearch();
        $this->assertInstanceOf('Iterator', $sut->getHandlers());
        $this->assertCount(8, $sut->getHandlers());
    }

    /**
     * @covers ::getStudioSupportedTypes
     */
    public function testGetStudioSupportedTypes()
    {
        $supported = array(
            'varchar',
            'name',
            'text',
            'int',
            'phone',
            'url',
            'longtext',
            'htmleditable_tinymce',
            'email',
        );
        $sut = new GlobalSearch();
        $this->assertEquals($supported, $sut->getStudioSupportedTypes());
    }

    /**
     * @covers ::isValidTypeField
     * @dataProvider providerTestIsValidTypeField
     */
    public function testIsValidTypeField($type, $fromQueue, $isSupported, $isSkipped, $expected)
    {
        $globalSearch = $this->getGlobalSearchMock(
            array(
                'isSupportedType',
                'isSkippedType'
            )
        );

        $globalSearch->expects($this->any())
            ->method('isSupportedType')
            ->will($this->returnValue($isSupported));

        $globalSearch->expects($this->any())
            ->method('isSkippedType')
            ->will($this->returnValue($isSkipped));

        $res = $globalSearch->isValidTypeField($type, $fromQueue);
        $this->assertEquals($expected, $res);
    }

    public function providerTestIsValidTypeField()
    {
        return array(
            array(
                'string',
                false,
                true,
                false,
                true
            ),
            array(
                'datetimecombo',
                false,
                false,
                false,
                false
            ),
            array(
                'string',
                true,
                false,
                false,
                false
            ),
            array(
                'email',
                true,
                true,
                true,
                false
            ),
        );
    }


    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    protected function getGlobalSearchMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
