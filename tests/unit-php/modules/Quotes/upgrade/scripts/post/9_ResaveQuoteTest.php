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
namespace Sugarcrm\SugarcrmTestUnit\modules\upgrade\scripts\post;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \SugarUpgradeResaveQuote
 */
class ResaveQuoteTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        \SugarAutoLoader::load('../../modules/Quotes/upgrade/scripts/post/9_ResaveQuote.php');
        parent::setup();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $mock = $this->getMockBuilder('\SugarUpgradeResaveQuote')
            ->setMethods(['resaveQuotes'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->from_version = '7.8.0.0';
        $mock->expects($this->once())
            ->method('resaveQuotes');
        $mock->run();
    }

    /**
     * @covers ::resaveQuotes
     */
    public function testResaveQuotes()
    {
        $sqMock = $this->getMockBuilder('ResaveQuotesTest_SugarQueryMock')
            ->setMethods(['select', 'from', 'execute'])
            ->getMock();
        $beanMock = $this->getMockBuilder('ResaveQuotesTest_BeanMock')
            ->setMethods(['save'])
            ->getMock();
        $beanMock->taxrate_id = 'bar';
        $beanMock->value = 'val';
        $mock = $this->getMockBuilder('\SugarUpgradeResaveQuote')
            ->setMethods(['getSugarQuery', 'getBean'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->exactly(2))
            ->method('getSugarQuery')
            ->will($this->returnValue($sqMock));
        $mock->expects($this->exactly(5))
            ->method('getBean')
            ->withConsecutive(
                ['ProductBundles'],
                ['ProductBundles', 'foo'],
                ['Quotes'],
                ['Quotes', 'foo'],
                ['TaxRates', 'bar']
            )
            ->will($this->returnValue($beanMock));
        $sqMock->expects($this->exactly(2))
            ->method('select')
            ->with(array('id'));
        $sqMock->expects($this->exactly(2))
            ->method('from')
            ->with($beanMock);
        $sqMock->expects($this->exactly(2))
            ->method('execute')
            ->will($this->returnValue(array(array('id'=>'foo'))));
        $beanMock->expects($this->exactly(2))
            ->method('save');
        $mock->resaveQuotes();
    }
}

class ResaveQuotesTest_SugarQueryMock
{
    public function select()
    {
    }
    public function from()
    {
    }
    public function execute()
    {
    }
}

class ResaveQuotesTest_BeanMock
{
    public function save()
    {
    }
}
