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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Booster;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields
 *
 */
class SearchFieldsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::addSearchField
     * @covers ::getSearchFields
     * @covers ::__construct
     * @dataProvider providerTestAddSearchField
     */
    public function testAddSearchField($module, array $path, array $defs, $weightId, $expected, Booster $booster = null)
    {
        $sut = new SearchFields($booster);
        $sut->addSearchField($module, $path, $defs, $weightId);
        $this->assertEquals(array($expected), $sut->getSearchFields());
    }

    public function providerTestAddSearchField()
    {
        return array(
            // one level
            array(
                'Contacts',
                array('first_name'),
                array(),
                'test_ngram',
                'first_name',
                null,
            ),
            // two levels
            array(
                'Contacts',
                array('first_name', 'test_ngram'),
                array(),
                'test_ngram',
                'first_name.test_ngram',
                null,
            ),
            // three levels
            array(
                'Contacts',
                array('email_search', 'primary', 'test_default'),
                array(),
                'test_ngram',
                'email_search.primary.test_default',
                null,
            ),
            // three levels with boost
            array(
                'Contacts',
                array('email_search', 'primary', 'test_default'),
                array(),
                'test_ngram',
                'email_search.primary.test_default^1',
                $this->getBoosterMock('email_search.primary.test_default'),
            ),
        );
    }

    /**
     * Get SearchFields mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields
     */
    protected function getSearchFieldsMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get Booster mock
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Booster
     */
    protected function getBoosterMock($expected)
    {
        $booster = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Booster')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $booster->expects($this->once())
            ->method('getBoostedField')
            ->with($this->equalTo($expected))
            ->will($this->returnValue($expected . '^1'));

        return $booster;
    }
}
