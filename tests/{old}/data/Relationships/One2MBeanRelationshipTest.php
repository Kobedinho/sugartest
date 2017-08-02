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

class One2MBeanRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        SugarTestKBContentUtilities::removeAllCreatedBeans();

        parent::tearDown();
    }

    public function testProperRhsFieldIsSet()
    {
        $primaryBean = SugarTestKBContentUtilities::createBean(array(
            'kbdocument_id' => create_guid(),
        ));
        $relatedBean = SugarTestKBContentUtilities::createBean();

        $primaryBean->load_relationship('localizations');
        $primaryBean->localizations->add($relatedBean);

        $this->assertEquals($primaryBean->kbdocument_id, $relatedBean->kbdocument_id);
    }

    /**
     * @dataProvider addProvider
     * @param $relationshipExists
     * @param $rowCompared
     * @param $isRHSMany_return
     * @param $removeAll_return
     * @param $result
     */
    public function testAdd(
        $relationshipExists,
        $rowCompared,
        $isRHSMany_return,
        $removeAll_return,
        $result
    ) {
        $mock = $this->createPartialMock(
            'One2MRelationship',
            array('add', 'relationship_exists', 'compareRow', 'isRHSMany', 'removeAll')
        );

        $mock->method('add')
            ->willReturn($result);

        $mock->method('relationship_exists')
            ->willReturn($relationshipExists);

        $mock->method('compareRow')
            ->willReturn($rowCompared);

        $mock->method('isRHSMany')
            ->willReturn($isRHSMany_return);

        $mock->method('removeAll')
            ->willReturn($removeAll_return);

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $value = $mock->add($beanMock, $beanMock);

        $this->assertEquals($result, $value);
    }

    public static function addProvider()
    {
        return array(
            array(false, false, true, true, true),
            array(true, false, true, true, true),
            array(false, true, true, true, true),
            array(false, false, false, true, true),
            array(true, false, false, true, true),
            array(false, true, false, true, true),
            array(false, false, true, false, false),
            array(true, false, true, false, false),
            array(false, true, true, false, false),
            array(false, false, false, false, false),
            array(true, false, false, false, false),
            array(false, true, false, false, false),
            array(true, true, true, true, false),
        );
    }
}
