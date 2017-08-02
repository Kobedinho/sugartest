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

namespace Sugarcrm\Sugarcrm\TestsUnit\Elasticsearch\Analysis;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder
 *
 */
class AnalysisBuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::addCustomAnalyzer
     * @covers ::addAnalysis
     * @dataProvider providerTestAddCustomAnalyzer
     */
    public function testAddCustomAnalyzer($name, $tokenizer, array $filters, array $charFilters, array $output)
    {
        $builder = $this->getAnalysisBuilderMock();
        $builder2 = $builder->addCustomAnalyzer($name, $tokenizer, $filters, $charFilters);

        $value = TestReflection::getProtectedValue($builder2, 'analysis');
        $this->assertEquals($value[AnalysisBuilder::ANALYZER][$name], $output);
    }

    public function providerTestAddCustomAnalyzer()
    {
        return array(
            array(
                'gs_analyzer_string',
                'standard',
                array('lowercase'),
                array(),
                array(
                    'type' => AnalysisBuilder::CUSTOM_ANALYZER,
                    AnalysisBuilder::TOKENIZER => 'standard',
                    AnalysisBuilder::TOKENFILTER => array('lowercase'),
                )
            ),
            array(
                'gs_analyzer_phone',
                'whitespace',
                array(),
                array('gs_char_num_pattern'),
                array(
                    'type' => AnalysisBuilder::CUSTOM_ANALYZER,
                    AnalysisBuilder::TOKENIZER => 'whitespace',
                    AnalysisBuilder::CHARFILTER => array('gs_char_num_pattern'),
                )
            ),
            array(
                'gs_analyzer_phone_ngram',
                'whitespace',
                array('gs_filter_ngram_3_15'),
                array('gs_char_num_pattern'),
                array(
                    'type' => AnalysisBuilder::CUSTOM_ANALYZER,
                    AnalysisBuilder::TOKENIZER => 'whitespace',
                    AnalysisBuilder::TOKENFILTER => array('gs_filter_ngram_3_15'),
                    AnalysisBuilder::CHARFILTER => array('gs_char_num_pattern'),
                )
            ),
        );
    }


    /**
     * @covers ::addAnalyzer
     * @covers ::addTokenizer
     * @covers ::addFilter
     * @covers ::addCharFilter
     * @covers ::addAnalysis
     * @dataProvider providerTestAddAnalyzer
     */
    public function testAddAnalyzer($name, $base, $type, array $options, array $output)
    {
        $builder = $this->getAnalysisBuilderMock();
        switch($base) {
            case AnalysisBuilder::ANALYZER:
                $builder2 = $builder->addAnalyzer($name, $type, $options);
                break;
            case AnalysisBuilder::TOKENIZER:
                $builder2 = $builder->addTokenizer($name, $type, $options);
                break;
            case AnalysisBuilder::TOKENFILTER:
                $builder2 = $builder->addFilter($name, $type, $options);
                break;
            case AnalysisBuilder::CHARFILTER:
                $builder2 = $builder->addCharFilter($name, $type, $options);
                break;
            default:
                break;
        }

        $value = TestReflection::getProtectedValue($builder2, 'analysis');
        $this->assertEquals($value[$base][$name], $output);
    }

    public function providerTestAddAnalyzer()
    {
        return array(
            array(
                'gs_analyzer_standard',
                AnalysisBuilder::ANALYZER,
                'standard',
                array(),
                array(
                    'type' => 'standard',
                )
            ),
            array(
                'gs_analyzer_whitespace',
                AnalysisBuilder::TOKENIZER,
                'whitespace',
                array(),
                array(
                    'type' => 'whitespace',
                )
            ),
            array(
                'gs_filter_ngram_1_15',
                AnalysisBuilder::TOKENFILTER,
                'nGram',
                array('min_gram' => 1, 'max_gram' => 15),
                array(
                    'type' => 'nGram',
                    'min_gram' => 1,
                    'max_gram' => 15,
                )
            ),
            array(
                'gs_char_num_pattern',
                AnalysisBuilder::CHARFILTER,
                'pattern_replace',
                array('pattern' => '[^\\d\\s]+', 'replacement' => ''),
                array(
                    'type' => 'pattern_replace',
                    'pattern' => '[^\\d\\s]+',
                    'replacement' => '',
                )
            ),
        );
    }


    /**
     * Get AnalysisBuilderTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder
     */
    protected function getAnalysisBuilderMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
