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

namespace Sugarcrm\SugarcrmTestUnit\inc\database;

/**
 * Class OracleManagerTest
 *
 * @coversDefaultClass \OracleManager
 */
class OracleManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::massageIndexDefs
     * @covers ::generateCaseInsensitiveIndices
     */
    public function testMassageIndexDefs()
    {
        /** @var \DBManager|\PHPUnit_Framework_MockObject_MockObject $db */
        $db = $this->getMockBuilder('OracleManager')
            ->disableOriginalConstructor()
            ->setMethods(array('query'))
            ->getMock();

        $ciIndexConstraint = $this->logicalAnd(
            $this->matchesRegularExpression('/idx1_ci/'),
            $this->matchesRegularExpression('/UPPER\(field3\)/'),
            $this->logicalNot($this->matchesRegularExpression('/UPPER\(field[12]\)/'))
        );

        $db->expects($this->exactly(3))
            ->method('query')
            ->withConsecutive(
                array(),
                array($this->matchesRegularExpression('/idx1/')),
                array($ciIndexConstraint)
            )
            ->willReturn(true);

        $indices = array(
            array('name' => 'idx1', 'type' => 'index', 'fields' => array('field1', 'field2', 'field3')),
        );

        $fieldDefs = array(
            'field1' => array('name' => 'field1', 'type' => 'id'),
            'field2' => array('name' => 'field2', 'type' => 'enum'),
            'field3' => array('name' => 'field3', 'type' => 'varchar'),
        );

        $db->createTableParams('table1', $fieldDefs, $indices);
    }
}
