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
 * @covers AbstractCollectionDefinition
 */
class AbstractCollectionDefinitionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractCollectionDefinition
     */
    private $definition;

    public function setUp()
    {
        $this->definition = $this->getMockBuilder('AbstractCollectionDefinition')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @dataProvider normalizeSourcesSuccessProvider
     */
    public function testNormalizeSourcesSuccess(array $sources, $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod(
            $this->definition,
            'normalizeSources',
            array($sources, null, null)
        );

        $this->assertEquals($expected, $actual);
    }

    public static function normalizeSourcesSuccessProvider()
    {
        return array(
            array(
                array(
                    'a',
                    array('name' => 'b'),
                    array(
                        'name' => 'c',
                        'field_map' => array(),
                    ),
                ),
                array(
                    'a' => array(),
                    'b' => array(),
                    'c' => array(
                        'field_map' => array(),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider normalizeSourcesFailureProvider
     * @expectedException SugarApiExceptionError
     */
    public function testNormalizeSourcesFailure($sources)
    {
        SugarTestReflection::callProtectedMethod(
            $this->definition,
            'normalizeSources',
            array($sources, null, null)
        );
    }

    public static function normalizeSourcesFailureProvider()
    {
        return array(
            'non-array-sources' => array(null),
            'non-string-or-array-source' => array(
                array(null),
            ),
            'no-name' => array(
                array(
                    array(),
                ),
            ),
        );
    }
}
