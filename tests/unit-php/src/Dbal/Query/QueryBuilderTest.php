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

namespace Sugarcrm\SugarcrmTestsUnit\Dbal\Query;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder
 */
class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::importSubQuery
     */
    public function testImportSubQuery()
    {
        /** @var \Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder|\PHPUnit_Framework_MockObject_MockObject $q1 */
        $q1 = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getSQL'))
            ->getMock();
        $q1->expects($this->once())
            ->method('getSQL')
            ->willReturn('SELECT 1 FROM DUAL');
        $q1->createPositionalParameter('x', \PDO::PARAM_INT);

        /** @var \Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder|\PHPUnit_Framework_MockObject_MockObject $q2 */
        $q2 = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $sql = $q2->importSubQuery($q1);
        $this->assertEquals('SELECT 1 FROM DUAL', $sql);

        $q2->createPositionalParameter('y', \PDO::PARAM_BOOL);

        $this->assertSame(array(
            1 => 'x',
            2 => 'y',
        ), $q2->getParameters());

        $this->assertSame(array(
            1 => \PDO::PARAM_INT,
            2 => \PDO::PARAM_BOOL,
        ), $q2->getParameterTypes());
    }
}
