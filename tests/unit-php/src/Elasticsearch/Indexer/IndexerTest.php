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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Indexer;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\ProviderCollection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\Indexer
 */
class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::decodeBeanField
     * @dataProvider providerDecodeBeanField
     *
     * @param mixed $fieldValue
     * @param boolean $fromApi
     * @param mixed $expected
     */
    public function testDecodeBeanField($fieldValue, $fromApi, $expected)
    {
        $container = $this->getContainerMock();

        $dbManager = $this->getMockBuilder('\DBManager')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $dbManager->setEncode(true);

        $indexer = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Indexer\Indexer')
            ->setConstructorArgs(array(array(), $container, $dbManager))
            ->setMethods(array('isFromApi'))
            ->getMock();

        $indexer->expects($this->once())
            ->method('isFromApi')
            ->will($this->returnValue($fromApi));

        $result = TestReflection::callProtectedMethod($indexer, 'decodeBeanField', array($fieldValue));
        $this->assertEquals($expected, $result);
    }

    public function providerDecodeBeanField()
    {
        $date = new \DateTime('2015-03-14');
        return array(

            // htmlspecialchars
            array(
                "Hello &amp; world",
                false,
                "Hello & world",
            ),
            array(
                "Hello &quot; world",
                false,
                "Hello \" world",
            ),
            array(
                "Here&#039;s what we have",
                false,
                "Here's what we have",
            ),
            array(
                "Hello &lt; world",
                false,
                "Hello < world",
            ),
            array(
                "Hello &gt; world",
                false,
                "Hello > world",
            ),

            // no decoding when coming from API
            array(
                "Hello &amp; world",
                true,
                "Hello &amp; world",
            ),

            // non-string non-decode situations
            array(
                "Here&apos;s what we have",
                false,
                "Here&apos;s what we have",
            ),
            array(
                "Here are what we have",
                false,
                "Here are what we have",
            ),
            array(
                array('Foo is here', 'bar is there'),
                false,
                array('Foo is here', 'bar is there'),
            ),
            array(
                array('Foo&#039;s is here', 'bar&#039;s is there'),
                false,
                array('Foo&#039;s is here', 'bar&#039;s is there'),
            ),
            array(
                $date,
                false,
                $date,
            ),
        );
    }


    /**
     * @covers ::getBeanIndexFields
     * @dataProvider providerTestGetBeanIndexFields
     */
    public function testGetBeanIndexFields($module, $fields1, $fields2, $output)
    {
        $provider1 = $this->getProviderMock(array('getBeanIndexFields'));
        $provider1->expects($this->once())
            ->method('getBeanIndexFields')
            ->will($this->returnValue($fields1));

        $provider2 = $this->getProviderMock(array('getBeanIndexFields'));
        $provider2->expects($this->once())
            ->method('getBeanIndexFields')
            ->will($this->returnValue($fields2));

        $collection = new ProviderCollection($this->getContainerMock(), array($provider1, $provider2));

        $indexer = $this->getIndexerMock(array('getRegisteredProviders'));
        $indexer->expects($this->once())
            ->method('getRegisteredProviders')
            ->will($this->returnValue($collection));

        $fields = $indexer->getBeanIndexFields($module);
        $this->assertEquals($fields, $output);
    }

    public function providerTestGetBeanIndexFields()
    {
        return array(
            array(
                'Contacts',
                array('first_name' => 'John', 'last_name' => 'Smith'),
                array('title' => 'sales rep'),
                array('first_name' => 'John', 'last_name' => 'Smith', 'title' => 'sales rep'),
            ),
            array(
                'Contacts',
                array('first_name' => 'John', 'last_name' => 'Smith'),
                array('last_name' => 'Joe', 'title' => 'sales rep'),
                array('first_name' => 'John', 'last_name' => 'Joe', 'title' => 'sales rep'),
            ),
            array(
                'Contacts',
                array('first_name' => 'John', 'last_name' => 'Smith', 'description' => 'new member'),
                array('last_name' => 'Joe', 'title' => 'sales rep'),
                array(
                    'first_name' => 'John',
                    'last_name' => 'Joe',
                    'title' => 'sales rep',
                    'description' => 'new member'),
            ),
        );
    }

    /**
     * Get IndexerTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\Indexer
     */
    protected function getIndexerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Indexer\Indexer')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get Provider Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    protected function getProviderMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get Container Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected function getContainerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Container')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
