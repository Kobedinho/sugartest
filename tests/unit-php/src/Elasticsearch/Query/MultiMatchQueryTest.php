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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery
 *
 */
class MultiMatchQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::processFieldName
     * @dataProvider providerProcessFieldName
     */
    public function testProcessFieldName($inputField, $module, $field)
    {
        $mQuery = $this->getMultiMatchQueryMock(array('normalizeFieldName'));


        $mQuery->expects($this->any())
            ->method('normalizeFieldName')
            ->will($this->returnValue($field));

        list($moduleName, $fieldName) = TestReflection::callProtectedMethod(
            $mQuery,
            'processFieldName',
            array($inputField)
        );

        $this->assertEquals($moduleName, $module);
        $this->assertEquals($fieldName, $field);
    }

    public function providerProcessFieldName()
    {
        return array(
            array(
                "Contacts__first_name.gs_string_wildcard^0.9",
                "Contacts",
                "first_name"
            ),
            array(
                "Contacts__email_search.primary.gs_email^1.95",
                "Contacts",
                "email"
            ),
            array(
                "Contacts__email_search.secondary.gs_email_wildcard^0.49",
                "Contacts",
                "email"
            ),
            //missing boost value
            array(
                "Contacts__last_name.gs_string_wildcard",
                "Contacts",
                "last_name"
            ),
            //missing field def
            array(
                "Contacts__first_name",
                "Contacts",
                "first_name"
            ),
            //missing contact name
            array(
                "first_name",
                "",
                "first_name"
            )
        );
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery
     */
    protected function getMultiMatchQueryMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
