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
 * @covers SugarFieldFullname
 */
class SugarFieldFullnameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SugarFieldFullname
     */
    private $sf;

    public function setUp()
    {
        $this->sf = new SugarFieldFullname('fullname');
    }

    public function testNameFormatFieldsAreConsidered()
    {
        global $locale;

        $locale = $this->getMockBuilder('Localization')
            ->setMethods(array('getNameFormatFields'))
            ->disableOriginalConstructor()
            ->getMock();
        $locale->expects($this->once())
            ->method('getNameFormatFields')
            ->with('TheModule')
            ->willReturn(array('foo', 'bar'));

        /** @var ViewIterator|PHPUnit_Framework_MockObject_MockObject $it */
        $it = $this->getMockBuilder('ViewIterator')
            ->disableOriginalConstructor()
            ->setMethods(array('dummy'))
            ->getMock();

        $fields = array();
        $this->sf->setModule('TheModule');
        $this->sf->iterateViewField($it, array(
            'name' => 'full_name',
        ), function ($field) use (&$fields) {
            $fields[] = $field;
        });

        $this->assertEquals(array(
            array(
                'name' => 'foo',
            ),
            array(
                'name' => 'bar',
            ),
        ), $fields);
    }
}
