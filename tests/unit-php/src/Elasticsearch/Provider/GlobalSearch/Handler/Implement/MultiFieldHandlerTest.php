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

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler
 *
 */
class MultiFieldHandlerTest extends \PHPUnit_Framework_TestCase
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
        );
        $implements = class_implements($nsPrefix . '\Implement\MultiFieldHandler');
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

        $sut = $this->getMultiFieldHandlerMock();
        TestReflection::setProtectedValue($sut, $property, $value);
        $sut->setProvider($provider);
    }

    public function providerTestSetProvider()
    {
        return array(
            array(
                'typesMultiField',
                array(
                    'varchar' => array(
                        'gs_string_default',
                        'gs_string_ngram',
                    ),
                    'name' => array(
                        'gs_string_default',
                        'gs_string_ngram',
                    ),
                ),
                'addSupportedTypes',
                array(
                    'varchar',
                    'name',
                ),
            ),
            array(
                'weightedBoost',
                array(
                    'field' => 0.35,
                ),
                'addWeightedBoosts',
                array(
                    'field' => 0.35,
                ),
            ),
            array(
                'highlighterFields',
                array(
                    '*.field_default' => array(
                        'number_of_fragments' => 0,
                    ),
                ),
                'addHighlighterFields',
                array(
                    '*.field_default' => array(
                        'number_of_fragments' => 0,
                    ),
                ),
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
        $sut = $this->getMultiFieldHandlerMock();
        $sut->buildAnalysis($analysisBuilder);

        $expected = array(
            'analysis' => array(
                'analyzer' => array(
                    'gs_analyzer_string' => array(
                        'tokenizer' => 'standard',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_string_ngram' => array(
                        'tokenizer' => 'standard',
                        'filter' => array(
                            'lowercase',
                            'gs_filter_ngram_1_15',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_phone_ngram' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'gs_filter_ngram_3_15',
                        ),
                        'char_filter' => array(
                            'gs_char_num_pattern',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_phone' => array(
                        'tokenizer' => 'whitespace',
                        'char_filter' => array(
                            'gs_char_num_pattern',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_text_ngram' => array(
                        'tokenizer' => 'standard',
                        'filter' => array(
                            'lowercase',
                            'gs_filter_ngram_3_15',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_url' => array(
                        'tokenizer' => 'uax_url_email',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_url_ngram' => array(
                        'tokenizer' => 'uax_url_email',
                        'filter' => array(
                            'lowercase',
                            'gs_filter_ngram_3_15',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_string_exact' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_string_html' => array(
                        'tokenizer' => 'standard',
                        'filter' => array(
                            'lowercase',
                        ),
                        'char_filter' => array(
                            'html_strip',
                        ),
                        'type' => 'custom',
                    ),
                ),
                'tokenizer' => array(),
                'filter' => array(
                    'gs_filter_ngram_1_15' => array(
                        'min_gram' => 1,
                        'max_gram' => 15,
                        'type' => 'nGram',
                    ),
                    'gs_filter_ngram_2_15' => array(
                        'min_gram' => 2,
                        'max_gram' => 15,
                        'type' => 'nGram',
                    ),
                    'gs_filter_ngram_3_15' => array(
                        'min_gram' => 3,
                        'max_gram' => 15,
                        'type' => 'nGram',
                    ),
                ),
                'char_filter' => array(
                    'gs_char_num_pattern' => array(
                        'pattern' => '[^\\d]+',
                        'replacement' => '',
                        'type' => 'pattern_replace',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $analysisBuilder->compile());
    }

    /**
     * Validation test for implemented mapping
     * @coversNothing
     * @dataProvider providerTestBuildMappingValidation
     */
    public function testBuildMappingValidation($module, $field, array $defs, $searchable, array $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getMultiFieldHandlerMock(array('isFieldSearchable'));

        $sut->expects($this->any())
            ->method('isFieldSearchable')
            ->will($this->returnValue($searchable));

        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMappingValidation()
    {
        return array(
            // test 'varchar' type
            array(
                'Accounts',
                'billing_street',
                array(
                    'type' => 'varchar',
                ),
                true,
                array(
                    'Accounts__billing_street' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                        ),
                    ),
                    'billing_street' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Accounts__billing_street',
                        ),
                    ),
                ),
            ),
            // test 'name' type
            array(
                'Opporunities',
                'name',
                array(
                    'type' => 'name',
                ),
                true,
                array(
                    'Opporunities__name' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                        ),
                    ),
                    'name' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Opporunities__name',
                        ),
                    ),
                ),
            ),
            // test 'text' type
            array(
                'Accounts',
                'description',
                array(
                    'type' => 'text',
                ),
                true,
                array(
                    'Accounts__description' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_text_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_text_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                        ),
                    ),
                    'description' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Accounts__description',
                        ),
                    ),
                ),
            ),
            // test 'datetime' type
            array(
                'Accounts',
                'date_modified',
                array(
                    'type' => 'datetime',
                ),
                false,
                array(
                    'date_modified' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_datetime' =>  array(
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'datetimecombo' type
            array(
                'Meetings',
                'date_start',
                array(
                    'type' => 'datetimecombo',
                ),
                false,
                array(
                    'date_start' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_datetime' =>  array(
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'date' type
            array(
                'Opportunities',
                'date_closed',
                array(
                    'type' => 'date',
                ),
                false,
                array(
                    'date_closed' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_date' =>  array(
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'int' type
            array(
                'Cases',
                'case_number',
                array(
                    'type' => 'int',
                ),
                true,
                array(
                    'Cases__case_number' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_integer' => array(
                                'type' => 'integer',
                                'index' => 'no',
                                'store' => false,
                            ),
                        ),
                    ),
                    'case_number' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Cases__case_number',
                        ),
                    ),
                ),
            ),
            // test 'phone' type
            array(
                'Contacts',
                'mobile',
                array(
                    'type' => 'phone',
                ),
                true,
                array(
                    'Contacts__mobile' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_phone_wildcard' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_phone_ngram',
                                'search_analyzer' => 'gs_analyzer_phone',
                                'store' => true,
                            ),
                        ),
                    ),
                    'mobile' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Contacts__mobile',
                        ),
                    ),
                ),
            ),
            // test 'url' type
            array(
                'Accounts',
                'website',
                array(
                    'type' => 'url',
                ),
                false,
                array(
                    'website' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_url' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_url',
                                'search_analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ),
                            'gs_url_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_url_ngram',
                                'search_analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'id' type
            array(
                'Accounts',
                'id',
                array(
                    'type' => 'id',
                ),
                false,
                array(
                    'id' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                    ),
                ),
            ),
            // test 'exact' type
            array(
                'Accounts',
                'stuff',
                array(
                    'type' => 'exact',
                ),
                true,
                array(
                    'Accounts__stuff' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string_exact' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string_exact',
                                'search_analyzer' => 'gs_analyzer_string_exact',
                                'store' => true,
                            ),
                        ),
                    ),
                    'stuff' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Accounts__stuff',
                        ),
                    ),
                ),
            ),
            // test 'longtext' type
            array(
                'Accounts',
                'description',
                array(
                    'type' => 'longtext',
                ),
                true,
                array(
                    'Accounts__description' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_text_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_text_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                        ),
                    ),
                    'description' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Accounts__description',
                        ),
                    ),
                ),
            ),
            // test 'htmleditable_tinymce' type
            array(
                'KBContents',
                'body',
                array(
                    'type' => 'htmleditable_tinymce',
                ),
                true,
                array(
                    'KBContents__body' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_text_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_text_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                        ),
                    ),
                    'body' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'KBContents__body',
                        ),
                    ),
                ),
            ),
            // test 'enum' type
            array(
                'Bugs',
                'status',
                array(
                    'type' => 'enum',
                ),
                false,
                array(
                    'status' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                    ),
                ),
            ),
        );
    }

    /**
     * @covers ::buildMapping
     * @covers ::getMultiFieldProperty
     * @covers ::createMultiFieldBase
     * @covers ::isLongFieldType
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, $types, array $long, array $multi, $field, $defs, $searchable, $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getMultiFieldHandlerMock(array('isFieldSearchable'));

        $sut->expects($this->any())
            ->method('isFieldSearchable')
            ->will($this->returnValue($searchable));

        // set multi field types and definitions
        TestReflection::setProtectedValue($sut, 'typesMultiField', $types);
        TestReflection::setProtectedValue($sut, 'longFieldTypes', $long);
        TestReflection::setProtectedValue($sut, 'multiFieldDefs', $multi);

        $sut->buildMapping($mapping, $field, $defs);
        $this->assertSame($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return array(
            // missing field type
            array(
                'FooBar',
                array(),
                array(),
                array(),
                'first_name',
                array(
                    'name' => 'first_name',
                ),
                true,
                array(),
            ),
            // missing mapping definition
            array(
                'FooBar',
                array(),
                array(),
                array(),
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'does_not_exist',
                ),
                true,
                array(),
            ),
            // single definition
            array(
                'custom_Module',
                array(
                    'type1' => array('mapping1'),
                ),
                array(),
                array(
                    'mapping1' => array('type' => 'string'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                true,
                array(
                    'custom_Module__field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'string',
                            ),
                        ),
                    ),
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'custom_Module__field1',
                        ),
                    ),
                ),
            ),
            // multi definition
            array(
                'Accounts',
                array(
                    'type1' => array('mapping1', 'mapping2'),
                ),
                array(),
                array(
                    'mapping1' => array('type' => 'string'),
                    'mapping2' => array('type' => 'integer'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                true,
                array(
                    'Accounts__field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'string',
                            ),
                            'mapping2' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Accounts__field1',
                        ),
                    ),
                ),
            ),
            // not_analyzed type
            array(
                'test_New_Module',
                array(
                    'type1' => array('not_analyzed'),
                ),
                array(),
                array(
                    'not_analyzed' => array(),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                false,
                array(
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                    ),
                ),
            ),
            // multi definition with not_analyzed
            array(
                'Opportunities',
                array(
                    'type1' => array('mapping1', 'not_analyzed', 'mapping2'),
                ),
                array(),
                array(
                    'not_analyzed' => array(),
                    'mapping1' => array('type' => 'string'),
                    'mapping2' => array('type' => 'integer'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                true,
                array(
                    'Opportunities__field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'string',
                            ),
                            'mapping2' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Opportunities__field1',
                        ),
                    ),
                ),
            ),
            // multi definition with not indexed
            array(
                'Opportunities',
                array(
                    'type1' => array('mapping1', 'not_analyzed', 'mapping2'),
                ),
                array('type1'),
                array(
                    'not_analyzed' => array(),
                    'mapping1' => array('type' => 'string'),
                    'mapping2' => array('type' => 'integer'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                true,
                array(
                    'Opportunities__field1' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'string',
                            ),
                            'mapping2' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'no',
                        'include_in_all' => false,
                        'copy_to' => array(
                            'Opportunities__field1',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @covers ::buildSearchFields
     * @covers ::getStringFieldsForType
     * @covers ::isStringBased
     * @covers ::getMultiFieldProperty
     * @dataProvider providerTestBuildSearchFields
     */
    public function testBuildSearchFields(array $types, array $multi, $module, $field, array $defs, array $expected)
    {
        $sut = $this->getMultiFieldHandlerMock(array('addHighlighterField'));

        // set multi field types and definitions
        TestReflection::setProtectedValue($sut, 'typesMultiField', $types);
        TestReflection::setProtectedValue($sut, 'multiFieldDefs', $multi);

        // mock SearchFields
        $sf = new SearchFields();
        $GLOBALS['log'] = \LoggerManager::getLogger('SugarCRM');
        $sut->buildSearchFields($sf, $module, $field, $defs);
        $this->assertEquals($expected, $sf->getSearchFields());
    }

    public function providerTestBuildSearchFields()
    {
        return array(
            // missing field type
            array(
                array(),
                array(),
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                ),
                array(),
            ),
            // missing mapping definition
            array(
                array(),
                array(),
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'does_not_exist',
                ),
                array(),
            ),
            // test multi field string fields only
            array(
                array(
                    'varchar' => array(
                        'test_default',
                        'test_ngram',
                    ),
                ),
                array(
                    'test_default' => array('type' => 'string'),
                    'test_ngram' => array('type' => 'string'),
                ),
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'varchar',
                ),
                array(
                    'Contacts__first_name.test_default',
                    'Contacts__first_name.test_ngram',
                ),
            ),
            // test not_analyzed field
            array(
                array(
                    'custom_type' => array(
                        'not_analyzed',
                    ),
                ),
                array(
                    'not_analyzed' => array(),
                ),
                'test_CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'test_CustomModule__custom_field',
                ),
            ),
            // test not_analyzed field combined with other multifields
            array(
                array(
                    'custom_type' => array(
                        'not_analyzed',
                        'test_default',
                        'test_ngram',
                    ),
                ),
                array(
                    'not_analyzed' => array(),
                    'test_default' => array('type' => 'string'),
                    'test_ngram' => array('type' => 'string'),
                ),
                'CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'CustomModule__custom_field',
                    'CustomModule__custom_field.test_default',
                    'CustomModule__custom_field.test_ngram',
                ),
            ),
            // test mix string and non-string fields
            array(
                array(
                    'custom_type' => array(
                        'test_default',
                        'test_integer',
                    ),
                ),
                array(
                    'test_default' => array('type' => 'string'),
                    'test_integer' => array('type' => 'integer'),
                ),
                'CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'CustomModule__custom_field.test_default',
                ),
            ),
            // test mix string and non-string fields with not_analyzed
            array(
                array(
                    'custom_type' => array(
                        'test_default',
                        'test_integer',
                        'not_analyzed',
                    ),
                ),
                array(
                    'not_analyzed' => array(),
                    'test_default' => array('type' => 'string'),
                    'test_integer' => array('type' => 'integer'),
                ),
                'CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'CustomModule__custom_field.test_default',
                    'CustomModule__custom_field',
                ),
            ),
        );
    }

    /**
     * Get MultiFieldHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler
     */
    protected function getMultiFieldHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
