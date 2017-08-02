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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Result;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet;

/**
 * @coversDefaultClass \GlobalSearchApi
 */
class GlobalSearchApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::parseArguments
     * @dataProvider providerTestParseArguments
     */
    public function testParseArguments(array $args, $moduleList, $q, $limit, $offset)
    {
        $sut = $this->getGlobalSearchApiMock();
        TestReflection::callProtectedMethod($sut, 'parseArguments', array($args));

        $this->assertSame($moduleList, TestReflection::getProtectedValue($sut, 'moduleList'));
        $this->assertSame($q, TestReflection::getProtectedValue($sut, 'term'));
        $this->assertSame($limit, TestReflection::getProtectedValue($sut, 'limit'));
        $this->assertSame($offset, TestReflection::getProtectedValue($sut, 'offset'));
    }

    public function providerTestParseArguments()
    {
        return array(

            // defaults
            array(
                array(),
                array(),
                '',
                20,
                0,
            ),

            // valid settings
            array(
                array(
                    'module_list' => 'Accounts,Contacts',
                    'q' => 'swaffelen',
                    'max_num' => 50,
                    'offset' => 100,
                ),
                array('Accounts', 'Contacts'),
                'swaffelen',
                50,
                100,
            ),

            // cast integers
            array(
                array(
                    'module_list' => 'Leads',
                    'q' => 'more stuff',
                    'max_num' => "invalid",
                    'offset' => 5.30,
                ),
                array('Leads'),
                'more stuff',
                0,
                5,
            ),
        );
    }

    /**
     * @covers ::executeGlobalSearch
     */
    public function testExecuteGlobalSearch()
    {
        $engine = $this->createMock('Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable');

        $expectedCalls = array(
            'from',
            'getTags',
            'setTagLimit',
            'setFilters',
            'term',
            'limit',
            'offset',
            'fieldBoost',
            'highlighter',
        );

        foreach ($expectedCalls as $callMe) {
            $engine->expects($this->once())
                ->method($callMe)
                ->will($this->returnValue($engine));
        }

        $sut = $this->getGlobalSearchApiMock();
        TestReflection::callProtectedMethod($sut, 'executeGlobalSearch', array($engine));
    }

    /**
     * @covers ::formatResults
     * @dataProvider providerTestFormatResults
     */
    public function testFormatResults(array $hits, array $expected)
    {
        $api = $this->getRestServiceMock(array());
        $resultSet = $this->getFormatResultsFixture($hits);

        $sut = $this->getGlobalSearchApiMock(array('formatBeanFromResult'));
        $sut->expects($this->exactly(count($hits)))
            ->method('formatBeanFromResult')
            ->will($this->returnCallback(array($this, 'formatBeanFromResult')));

        $actual = TestReflection::callProtectedMethod($sut, 'formatResults', array($api, $resultSet));
        $this->assertEquals($expected, $actual);
    }

    public function providerTestFormatResults()
    {
        return array(

            // no score or highlights available
            array(
                array(
                    array(
                        '_id' => '123',
                        '_type' => 'Accounts',
                        '_source' => array(
                            'id' => '123',
                            'name' => 'SugarCRM',
                        ),
                    ),
                    array(
                        '_id' => '456',
                        '_type' => 'Contacts',
                        '_source' => array(
                            'id' => '456',
                            'first_name' => 'skymeyer',
                        ),
                    ),
                ),
                array(
                    array(
                        'id' => '123',
                        'name' => 'SugarCRM',
                        '_module' => 'Accounts',
                    ),
                    array(
                        'id' => '456',
                        'first_name' => 'skymeyer',
                        '_module' => 'Contacts',
                    ),
                ),
            ),

            // score and highlights on one entry
            array(
                array(
                    array(
                        '_id' => '123',
                        '_type' => 'Accounts',
                        '_source' => array(
                            'id' => '123',
                            'name' => 'SugarCRM',
                        ),
                        '_score' => 1.80,
                        'highlight' => array(
                            'list' => array('of', 'stuff'),
                        ),
                    ),
                    array(
                        '_id' => '456',
                        '_type' => 'Contacts',
                        '_source' => array(
                            'id' => '456',
                            'first_name' => 'skymeyer',
                        ),
                    ),
                ),
                array(
                    array(
                        'id' => '123',
                        'name' => 'SugarCRM',
                        '_module' => 'Accounts',
                        '_score' => 1.80,
                        '_highlights' => array(
                            'list' => array('of', 'stuff'),
                        ),
                    ),
                    array(
                        'id' => '456',
                        'first_name' => 'skymeyer',
                        '_module' => 'Contacts',
                    ),
                ),
            ),

            // score and highlights mixed
            array(
                array(
                    array(
                        '_id' => '123',
                        '_type' => 'Accounts',
                        '_source' => array(
                            'id' => '123',
                            'name' => 'SugarCRM',
                        ),
                        'highlight' => array(
                            'list' => array('of', 'stuff'),
                        ),
                    ),
                    array(
                        '_id' => '456',
                        '_type' => 'Contacts',
                        '_source' => array(
                            'id' => '456',
                            'first_name' => 'skymeyer',
                        ),
                        '_score' => 1.50,
                    ),
                ),
                array(
                    array(
                        'id' => '123',
                        'name' => 'SugarCRM',
                        '_module' => 'Accounts',
                        '_highlights' => array(
                            'list' => array('of', 'stuff'),
                        ),
                    ),
                    array(
                        'id' => '456',
                        'first_name' => 'skymeyer',
                        '_module' => 'Contacts',
                        '_score' => 1.50,
                    ),
                ),
            ),
        );
    }

    /**
     * Callback for testFormatResults
     */
    public function formatBeanFromResult()
    {
        $args = func_get_args();
        $result = $args[1];
        $beanData = $result->getData();
        $beanData['_module'] = $result->getType();
        return $beanData;
    }

    /**
     * @param null|array $methods
     * @return \GlobalSearchApi
     */
    protected function getGlobalSearchApiMock($methods = null)
    {
        return $this->getMockBuilder('GlobalSearchApi')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param null|array $methods
     * @return \RestService
     */
    protected function getRestServiceMock($methods = null)
    {
        return $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Fixture helper for testFormatResults
     * @param array $hits
     * @return ResultSet
     */
    protected function getFormatResultsFixture(array $hits)
    {
        $elasticaResults = array();

        foreach ($hits as $hit) {
            $elasticaResults[] = new \Elastica\Result($hit);
        }

        $elasticaResultSet = $this->getMockBuilder('Elastica\ResultSet')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        TestReflection::setProtectedValue($elasticaResultSet, '_results', $elasticaResults);

        $highlighter = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Query\Highlighter\AbstractHighlighter')
            ->getMockForAbstractClass();

        $resultSet = new ResultSet($elasticaResultSet);
        $resultSet->setHighlighter($highlighter);
        return $resultSet;
    }
}
