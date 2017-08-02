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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder
 *
 */
class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::buildPostFilters
     * @dataProvider providerBuildPostFilters
     *
     * @param array $filterParams : a list of post filters' parameters
     * @param array $outputArray : the expected value of the output filter in array format
     */
    public function testBuildPostFilters($filterParams, $outputArray)
    {
        $builder = $this->getQueryBuilderMock();

        $postFilters = array();
        foreach ($filterParams as $key => $value) {
            $termFilter = new \Elastica\Filter\Term();
            $termFilter->setTerm($key, $value);
            $postFilters[] = $termFilter;
        }

        $result = TestReflection::callProtectedMethod($builder, 'buildPostFilters', array($postFilters));

        $this->assertEquals($result->toArray(), $outputArray);
    }

    public function providerBuildPostFilters()
    {
        return array(
            array(
                array("_type" => "Accounts", "assigned_user_id" => "seed_max_id"),
                array("bool" => array("must" => array("0" => array("term" => array("_type" => "Accounts")),
                    "1" => array("term" => array("assigned_user_id" => "seed_max_id")))))
            ),
        );
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder
     */
    protected function getQueryBuilderMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
