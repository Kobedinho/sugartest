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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Api;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\ApiEndpointTrait
 *
 */
class ApiEndpointTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::initApi
     * @covers ::callApi
     * @requires PHP 5.4
     */
    public function testTrait()
    {
        $service = $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->getMock();

        $trait = $this->getMockBuilder('Sugarcrm\Sugarcrm\Console\Command\Api\ApiEndpointTrait')
            ->setMethods(array('getService'))
            ->getMockForTrait();

        $trait->expects($this->once())
            ->method('getService')
            ->will($this->returnValue($service));

        $api = $this->createMock('Sugarcrm\SugarcrmTestsUnit\Console\Fixtures\UnitTestApi');

        $apiCallArgs = array('foo', 'bar', array('more' => 'beer'));

        $api->expects($this->once())
            ->method('test1')
            ->with($this->equalTo($service), $this->equalTo($apiCallArgs));

        TestReflection::callProtectedMethod($trait, 'initApi', array($api));
        TestReflection::callProtectedMethod(
            $trait,
            'callApi',
            array('test1', $apiCallArgs)
        );
    }
}
