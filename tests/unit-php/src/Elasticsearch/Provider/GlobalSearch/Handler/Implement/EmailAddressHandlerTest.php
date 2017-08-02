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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Implement;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\EmailAddressHandler
 *
 */
class EmailAddressHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coversNothing
     */
    public function testRequiredInterfaces()
    {
        $nsPrefix = 'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler';
        $interfaces = array(
            $nsPrefix . '\AnalysisHandlerInterface',
            $nsPrefix . '\MappingHandlerInterface',
            $nsPrefix . '\SearchFieldsHandlerInterface',
            $nsPrefix . '\ProcessDocumentHandlerInterface',
        );
        $implements = class_implements($nsPrefix . '\Implement\EmailAddressHandler');
        $this->assertEquals($interfaces, array_values(array_intersect($implements, $interfaces)));
    }

    /**
     * @covers ::setProvider
     * @dataProvider providerTestSetProvider
     */
    public function testSetProvider($property, array $value, $method, array $expected)
    {
        $provider = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->once())
            ->method($method)
            ->with($this->equalTo($expected));

        $sut = $this->getEmailAddressHandlerMock();

        if ($property !== null) {
            TestReflection::setProtectedValue($sut, $property, $value);
        }

        $sut->setProvider($provider);
    }

    public function providerTestSetProvider()
    {
        return array(
            array(
                null,
                array(),
                'addSupportedTypes',
                array('email'),
            ),
            array(
                'highlighterFields',
                array('stuff'),
                'addHighlighterFields',
                array('stuff'),
            ),
            array(
                'weightedBoost',
                array('morestuff'),
                'addWeightedBoosts',
                array('morestuff'),
            ),
            array(
                null,
                array(),
                'addFieldRemap',
                array('email_search' => 'email'),
            ),
            array(
                null,
                array(),
                'addSkipTypesFromQueue',
                array('email'),
            ),
        );
    }

    /**
     * Validation test for implemented analysis settings
     * @covers ::buildAnalysis
     */
    public function testBuildAnalysisValidation()
    {
        $analysisBuilder = new AnalysisBuilder();
        $sut = $this->getEmailAddressHandlerMock();
        $sut->buildAnalysis($analysisBuilder);

        $expected = array(
            'analysis' => array(
                'analyzer' => array(
                    'gs_analyzer_email' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_email_ngram' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'lowercase',
                            'gs_filter_ngram_1_15',
                        ),
                        'type' => 'custom',
                    ),
                ),
                'tokenizer' => array(),
                'filter' => array(),
                'char_filter' => array(),
            ),
        );

        $this->assertEquals($expected, $analysisBuilder->compile());
    }

    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, $field, array $defs, array $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getEmailAddressHandlerMock();
        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return array(
            // test 'email' type for 'email' field
            array(
                'testModule',
                'email',
                array(
                    'name' => 'email',
                    'type' => 'email',
                ),
                array(
                    'testModule__email_search' => array(
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => true,
                        'include_in_all' => false,
                        'properties' => array(
                            'primary' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed',
                                'fields' => array(
                                    'gs_email' => array(
                                        'type' => 'string',
                                        'index' => 'analyzed',
                                        'index_analyzer' => 'gs_analyzer_email',
                                        'search_analyzer' => 'gs_analyzer_email',
                                        'store' => true,
                                    ),
                                    'gs_email_wildcard' => array(
                                        'type' => 'string',
                                        'index' => 'analyzed',
                                        'index_analyzer' => 'gs_analyzer_email_ngram',
                                        'search_analyzer' => 'gs_analyzer_email',
                                        'store' => true,
                                    ),
                                ),
                            ),
                            'secondary' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed',
                                'fields' => array(
                                    'gs_email' => array(
                                        'type' => 'string',
                                        'index' => 'analyzed',
                                        'index_analyzer' => 'gs_analyzer_email',
                                        'search_analyzer' => 'gs_analyzer_email',
                                        'store' => true,
                                    ),
                                    'gs_email_wildcard' => array(
                                        'type' => 'string',
                                        'index' => 'analyzed',
                                        'index_analyzer' => 'gs_analyzer_email_ngram',
                                        'search_analyzer' => 'gs_analyzer_email',
                                        'store' => true,
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'email' => array(
                        'type' => 'object',
                        'include_in_all' => false,
                        'dynamic' => false,
                        'enabled' => false,
                    ),
                ),
            ),
            // test 'email' type for non 'email' field
            array(
                'Accounts',
                'other_email',
                array(
                    'name' => 'other_email',
                    'type' => 'email',
                ),
                array(),
            ),
            // test non 'email' type for 'email' field
            array(
                'Contacts',
                'email',
                array(
                    'name' => 'email',
                    'type' => 'non_email',
                ),
                array(),
            ),
            // test non 'email' type for non 'email' field
            array(
                'Leads',
                'other_email',
                array(
                    'name' => 'other_email',
                    'type' => 'non_email',
                ),
                array(),
            ),
        );
    }

    /**
     * @covers ::buildSearchFields
     * @dataProvider providerTestBuildSearchFields
     */
    public function testBuildSearchFields($module, $field, array $defs, array $expected)
    {
        $sf = new SearchFields();
        $sut = $this->getEmailAddressHandlerMock();
        $GLOBALS['log'] = \LoggerManager::getLogger('SugarCRM');
        $sut->buildSearchFields($sf, $module, $field, $defs);
        $this->assertEquals($expected, $sf->getSearchFields());
    }

    public function providerTestBuildSearchFields()
    {
        return array(
            // email field
            array(
                'Contacts',
                'email',
                array(
                    'name' => 'email',
                    'type' => 'email',
                ),
                array(
                    'Contacts__email_search.primary.gs_email',
                    'Contacts__email_search.primary.gs_email_wildcard',
                    'Contacts__email_search.secondary.gs_email',
                    'Contacts__email_search.secondary.gs_email_wildcard',
                ),
            ),
            // non email type/field
            array(
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'varchar',
                ),
                array(),
            ),
            // email field, non email type
            array(
                'Contacts',
                'email',
                array(
                    'name' => 'email',
                    'type' => 'varchar',
                ),
                array(),
            ),
            // non email field, email type
            array(
                'Contacts',
                'other_email',
                array(
                    'name' => 'other_email',
                    'type' => 'email',
                ),
                array(),
            ),
        );
    }

    /**
     * @covers ::processDocumentPreIndex
     * @covers ::getEmailAddressesForBean
     * @dataProvider providerTestProcessDocumentPreIndex
     */
    public function testProcessDocumentPreIndex($module, array $beanFields, $fetch, array $expected)
    {
        $bean = $this->getSugarBeanMock($beanFields);
        $sut = $this->getEmailAddressHandlerMock(array('fetchEmailAddressesFromDatabase'));

        // stub db fetch
        if ($fetch === null) {
            $sut->expects($this->never())
                ->method('fetchEmailAddressesFromDatabase');
        } else {
            $sut->expects($this->once())
                ->method('fetchEmailAddressesFromDatabase')
                ->will($this->returnValue($fetch));
        }

        $document = new Document();
        $document->setType($module);

        $sut->processDocumentPreIndex($document, $bean);
        $this->assertEquals($expected, $document->getData());
    }

    public function providerTestProcessDocumentPreIndex()
    {
        return array(
            // missing email field in bean field_defs
            array(
                'Contacts',
                array(
                    'first_name' => 'Jelle',
                    'last_name' => 'Vink',
                    'field_defs' => array(
                        'name',
                    ),
                ),
                null,
                array(),
            ),
            // no emailAddress object means no fetch and empty result
            array(
                'Accounts',
                array(
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'field_defs' => array(
                        'email' => array('type' => 'email'),
                    ),
                ),
                null,
                array(
                    'email' => array(),
                    'Accounts__email_search' => array(
                        'primary' => '',
                        'secondary' => array(),
                    ),
                ),
            ),
            // emailAddress present but not correct object
            array(
                'Accounts',
                array(
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => '',
                    'field_defs' => array(
                        'email' => array('type' => 'email'),
                    ),
                ),
                null,
                array(
                    'email' => array(),
                    'Accounts__email_search' => array(
                        'primary' => '',
                        'secondary' => array(),
                    ),
                ),
            ),
            // emailAddress present and fetched
            array(
                'Leads',
                array(
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => $this->getSugarEmailAddressFixture(
                        true,
                        false,
                        array('first@gmail.com', 'second@sugarcrm.com', 'ok@more.co.uk')
                    ),
                    'field_defs' => array(
                        'email' => array('type' => 'email'),
                    ),
                ),
                null,
                array(
                    'email' => array(
                        array(
                            'email_address' => 'first@gmail.com',
                            'primary_address' => true,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                        array(
                            'email_address' => 'second@sugarcrm.com',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                        array(
                            'email_address' => 'ok@more.co.uk',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                    ),
                    'Leads__email_search' => array(
                        'primary' => 'first@gmail.com',
                        'secondary' => array('second@sugarcrm.com', 'ok@more.co.uk'),
                    ),
                ),
            ),
            // emailAddress present with fetched and dontLegacySave
            array(
                'Leads',
                array(
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => $this->getSugarEmailAddressFixture(
                        true,
                        true,
                        array('first@gmail.com', 'second@sugarcrm.com')
                    ),
                    'field_defs' => array(
                        'email' => array('type' => 'email'),
                    ),
                ),
                null,
                array(
                    'email' => array(
                        array(
                            'email_address' => 'first@gmail.com',
                            'primary_address' => true,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                        array(
                            'email_address' => 'second@sugarcrm.com',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                    ),
                    'Leads__email_search' => array(
                        'primary' => 'first@gmail.com',
                        'secondary' => array('second@sugarcrm.com'),
                    ),
                ),
            ),
            // emailAddress present with fetched and dontLegacySave
            array(
                'Accounts',
                array(
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => $this->getSugarEmailAddressFixture(
                        true,
                        true,
                        array('first@gmail.com')
                    ),
                    'field_defs' => array(
                        'email' => array('type' => 'email'),
                    ),
                ),
                null,
                array(
                    'email' => array(
                        array(
                            'email_address' => 'first@gmail.com',
                            'primary_address' => true,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                    ),
                    'Accounts__email_search' => array(
                        'primary' => 'first@gmail.com',
                        'secondary' => array(),
                    ),
                ),
            ),
            // emailAddress present with fetch from database
            array(
                'Leads',
                array(
                    'name' => 'SugarCRM',
                    'email' => 'foobar',
                    'emailAddress' => $this->getSugarEmailAddressFixture(false, false),
                    'field_defs' => array(
                        'email' => array('type' => 'email'),
                    ),
                ),
                $this->getEmailsFixture(array('first@gmail.com', 'second@sugarcrm.com', 'ok@more.co.uk')),
                array(
                    'email' => array(
                        array(
                            'email_address' => 'first@gmail.com',
                            'primary_address' => true,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                        array(
                            'email_address' => 'second@sugarcrm.com',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                        array(
                            'email_address' => 'ok@more.co.uk',
                            'primary_address' => false,
                            'reply_to_address' => false,
                            'invalid_email' => false,
                            'opt_out' => false,
                        ),
                    ),
                    'Leads__email_search' => array(
                        'primary' => 'first@gmail.com',
                        'secondary' => array('second@sugarcrm.com', 'ok@more.co.uk'),
                    ),
                ),
            ),
        );
    }

    /**
     * Get EmailAddressHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\EmailAddressHandler
     */
    protected function getEmailAddressHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\EmailAddressHandler')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     *
     * @param boolean $hasFetched
     * @param boolean $dontLegacySave
     * @param array $values
     * @return \SugarEmailAddress
     */
    protected function getSugarEmailAddressFixture($hasFetched, $dontLegacySave, array $values = null)
    {
        $email = $this->getMockBuilder('SugarEmailAddress')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $email->hasFetched = $hasFetched;
        $email->dontLegacySave = $dontLegacySave;

        if ($values) {
            $email->addresses = $this->getEmailsFixture($values);
        }

        return $email;
    }

    /**
     * Get raw email address fixture
     * @param array $values
     * @return array
     */
    protected function getEmailsFixture(array $values)
    {
        $fixture = array();
        foreach ($values as $email) {
            $fixture[] = array(
                'email_address' => $email,
                'primary_address' => empty($fixture) ? true : false,
                'reply_to_address' => false,
                'invalid_email' => false,
                'opt_out' => false,
            );
        }
        return $fixture;
    }

    /**
     * Get SugarBean mock
     * @param array $beanFields
     * @return \SugarBean
     */
    protected function getSugarBeanMock(array $beanFields)
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        foreach ($beanFields as $property => $value) {
            $bean->$property = $value;
        }

        return $bean;
    }
}
