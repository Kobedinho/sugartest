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
 * @covers RelateCollectionDefinition
 */
class RelateCollectionDefinitionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var RelateCollectionDefinition
     */
    private $definition;

    public function setUp()
    {
        $this->definition = $this->getMockBuilder('RelateCollectionDefinition')
            ->disableOriginalConstructor()
            ->setMethods(array('dummy'))
            ->getMockForAbstractClass();
    }

    public function testLoadDefinitionSuccess()
    {
        $fieldDef = array(
            'type' => 'collection',
            'links' => array(),
        );

        $actual = $this->loadDefinition('test', $fieldDef);
        $this->assertEquals($fieldDef, $actual);
    }

    /**
     * @dataProvider loadDefinitionFailureProvider
     */
    public function testLoadDefinitionFailure($fieldDef)
    {
        $this->setExpectedException('SugarApiExceptionNotFound');
        $this->loadDefinition('test', $fieldDef);
    }

    public static function loadDefinitionFailureProvider()
    {
        return array(
            'non-array' => array(
                null,
                'SugarApiExceptionNotFound'
            ),
            'non-collection' => array(
                array('type' => 'varchar'),
                'SugarApiExceptionNotFound'
            ),
        );
    }

    /**
     * @return SugarBean|PHPUnit_Framework_MockObject_MockObject
     */
    private function getCollectionDefinitionBeanMock($fieldName, $fieldDef)
    {
        /** @var SugarBean|PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldDefinition'))
            ->getMock();
        $bean->expects($this->once())
            ->method('getFieldDefinition')
            ->with($fieldName)
            ->will($this->returnValue($fieldDef));

        return $bean;
    }

    /**
     * @return SugarBean|PHPUnit_Framework_MockObject_MockObject
     */
    private function loadDefinition($fieldName, $fieldDef)
    {
        $bean = $this->getCollectionDefinitionBeanMock($fieldName, $fieldDef);

        SugarTestReflection::setProtectedValue($this->definition, 'name', $fieldName);
        SugarTestReflection::setProtectedValue($this->definition, 'bean', $bean);
        return SugarTestReflection::callProtectedMethod($this->definition, 'loadDefinition');
    }
}
