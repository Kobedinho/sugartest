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

namespace Sugarcrm\SugarcrmTestsUnit\inc\api;

/**
 *
 * @coversDefaultClass \ServiceDictionaryRest
 *
 */
class ServiceDictionaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::registerEndpoints
     * @covers ::preRegisterEndpoints
     * @covers ::addToPathArray
     * @covers ::getRegisteredEndpoints
     *
     * @dataProvider providerTestRegisterEndpoints
     */
    public function testRegisterEndpoints($endpoints, $file, $class, $platform, $custom, $expected)
    {
        $sut = $this->getServiceDictionaryRestMock();

        // verify preregistering
        $sut->preRegisterEndpoints();
        $this->assertSame(array(), $sut->getRegisteredEndpoints());

        // set endpoints
        $sut->registerEndpoints($endpoints, $file, $class, $platform, $custom);
        $this->assertEquals($expected, $sut->getRegisteredEndpoints());
    }

    public function providerTestRegisterEndpoints()
    {
        return array(

            // GET and POST definition combined, with custom score
            array(
                array(
                    'entry1' => array(
                        'reqType' => 'GET',
                        'path' => array('path1', '<data>'),
                        'pathVars' => array('pathvar1', 'pathvar2'),
                        'method' => 'serviceMethod1',
                    ),
                    'entry2' => array(
                        'reqType' => 'POST',
                        'path' => array('?', 'path3'),
                        'pathVars' => array('pathvar3'),
                        'method' => 'serviceMethod2',
                    ),
                ),
                'fileName',
                'className',
                'base',
                true,
                array(
                    2 => array(
                        'base' => array(
                            'GET' => array(
                                'path1' => array(
                                    '<data>' => array(
                                        array(
                                            'reqType' => 'GET',
                                            'path' => array('path1', '<data>'),
                                            'pathVars' => array('pathvar1', 'pathvar2'),
                                            'method' => 'serviceMethod1',
                                            'file' => 'fileName',
                                            'className' => 'className',
                                            'score' => 8.50,
                                        ),
                                    ),
                                ),
                            ),
                            'POST' => array(
                                '?' => array(
                                    'path3' => array(
                                        array(
                                            'reqType' => 'POST',
                                            'path' => array('?', 'path3'),
                                            'pathVars' => array('pathvar3'),
                                            'method' => 'serviceMethod2',
                                            'file' => 'fileName',
                                            'className' => 'className',
                                            'score' => 8.25,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // two GET conditions - one as single reqType one as array reqType
            array(
                array(
                    'entry1' => array(
                        'reqType' => 'GET',
                        'path' => array('path1', 'path2'),
                        'pathVars' => array('pathvar1', 'pathvar2'),
                        'method' => 'serviceMethod1',
                    ),
                    'entry2' => array(
                        'reqType' => array('GET'),
                        'path' => array('?', 'path3'),
                        'pathVars' => array('pathvar3'),
                        'method' => 'serviceMethod2',
                    ),
                ),
                'fileName2',
                'className2',
                'base',
                false,
                array(
                    2 => array(
                        'base' => array(
                            'GET' => array(
                                'path1' => array(
                                    'path2' => array(
                                        array(
                                            'reqType' => 'GET',
                                            'path' => array('path1', 'path2'),
                                            'pathVars' => array('pathvar1', 'pathvar2'),
                                            'method' => 'serviceMethod1',
                                            'file' => 'fileName2',
                                            'className' => 'className2',
                                            'score' => 8.75,
                                        ),
                                    ),
                                ),
                                '?' => array(
                                    'path3' => array(
                                        array(
                                            'reqType' => 'GET',
                                            'path' => array('?', 'path3'),
                                            'pathVars' => array('pathvar3'),
                                            'method' => 'serviceMethod2',
                                            'file' => 'fileName2',
                                            'className' => 'className2',
                                            'score' => 7.75,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // extra score
            array(
                array(
                    'entry1' => array(
                        'reqType' => 'GET',
                        'path' => array('path1'),
                    ),
                    'entry2' => array(
                        'reqType' => 'GET',
                        'path' => array('path2'),
                        'extraScore' => 9.50,
                    ),
                ),
                'fileName3',
                'className3',
                'mobile',
                false,
                array(
                    1 => array(
                        'mobile' => array(
                            'GET' => array(
                                'path1' => array(
                                    array(
                                        'reqType' => 'GET',
                                        'path' => array('path1'),
                                        'file' => 'fileName3',
                                        'className' => 'className3',
                                        'score' => 7.00,
                                    ),
                                ),
                                'path2' => array(
                                    array(
                                        'reqType' => 'GET',
                                        'path' => array('path2'),
                                        'extraScore' => 9.50,
                                        'file' => 'fileName3',
                                        'className' => 'className3',
                                        'score' => 16.50,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // dual request type
            array(
                array(
                    'entry1' => array(
                        'reqType' => array('GET', 'POST'),
                        'path' => array('path1', 'path2'),
                        'pathVars' => array('pathvar1', 'pathvar2'),
                        'method' => 'serviceMethod1',
                    ),
                ),
                'fileName4',
                'className4',
                'base',
                false,
                array(
                    2 => array(
                        'base' => array(
                            'GET' => array(
                                'path1' => array(
                                    'path2' => array(
                                        array(
                                            'reqType' => 'GET',
                                            'path' => array('path1', 'path2'),
                                            'pathVars' => array('pathvar1', 'pathvar2'),
                                            'method' => 'serviceMethod1',
                                            'file' => 'fileName4',
                                            'className' => 'className4',
                                            'score' => 8.75,
                                        ),
                                    ),
                                ),
                            ),
                            'POST' => array(
                                'path1' => array(
                                    'path2' => array(
                                        array(
                                            'reqType' => 'POST',
                                            'path' => array('path1', 'path2'),
                                            'pathVars' => array('pathvar1', 'pathvar2'),
                                            'method' => 'serviceMethod1',
                                            'file' => 'fileName4',
                                            'className' => 'className4',
                                            'score' => 8.75,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // invalid entry points (not array)
            array(
                null,
                null,
                null,
                null,
                false,
                array(),
            ),

        );
    }

    /**
     * @param null|array $methods
     * @return \ServiceDictionaryRest
     */
    protected function getServiceDictionaryRestMock($methods = null)
    {
        return $this->getMockBuilder('ServiceDictionaryRest')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
