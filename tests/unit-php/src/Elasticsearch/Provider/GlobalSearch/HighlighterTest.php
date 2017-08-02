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

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Highlighter
 */
class HighlighterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::wrapValueWithTags
     * @dataProvider providerTestAddTagsInValue
     */
    public function testAddTagsInValue($value, $expected)
    {
        $highlighter = $this->getHighlighterMock();

        $res = TestReflection::callProtectedMethod($highlighter, 'wrapValueWithTags', array($value));
        $this->assertEquals($expected, $res);
    }

    public function providerTestAddTagsInValue()
    {
        return array(
            array(
                array('foo'),
                array('<strong>foo</strong>'),
            ),
            array(
                array("dev.support.beans@example.it"),
                array("<strong>dev.support.beans@example.it</strong>"),
            ),
            array(
                array("<strong>dev.support.beans@example.it</strong>"),
                array("<strong>dev.support.beans@example.it</strong>"),
            ),
            array(
                array("dev.support.beans@example.it", "<strong>im.support.qa</strong>@example.edu"),
                array("<strong>dev.support.beans@example.it</strong>", "<strong>im.support.qa@example.edu</strong>"),
            ),
            array(
                array(),
                array(),
            ),
        );
    }

    /**
     * @covers ::getSubFieldName
     * @dataProvider providerTestGetSubFieldName
     */
    public function testGetSubFieldName($field, $expected)
    {
        $highlighter = $this->getHighlighterMock();

        $res = $highlighter->getSubFieldName($field);
        $this->assertEquals($expected, $res);
    }

    public function providerTestGetSubFieldName()
    {
        return array(
            array(
                'Accounts__email_search.primary.gs_email_wildcard',
                'primary',
            ),
            array(
                'Accounts__email_search.secondary.gs_email_wildcard',
                'secondary',
            ),
            array(
                'Contacts__first_name.gs_string_wildcard',
                '',
            ),
            array(
                'Contacts__phone_home',
                '',
            ),
            array(
                '',
                '',
            ),
            array(
                null,
                '',
            ),
        );
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Highlighter
     */
    protected function getHighlighterMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Highlighter')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
