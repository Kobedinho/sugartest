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




class SidecarGridLayoutMetaDataParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SidecarGridLayoutMetaDataParserTestDerivative
     */
    protected $_parser;

    public function setUp()
    {
        //echo "Setup";
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = true;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = array();

        $implementation = $this->getMockForAbstractClass('AbstractMetaDataImplementation');
        $this->_parser = new SidecarGridLayoutMetaDataParserTestDerivative(
            MB_PORTALRECORDVIEW,
            'Leads',
            $implementation
        );
    }

    public function tearDown()
    {
        //echo "TearDown";
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function getMockRequestArray() {
        return array(
            'PORTAL' => '1',
            'action' => 'saveLayout',
            'module' => 'ModuleBuilder',
            'panel-1-label' => '0',
            'panel-1-name' => 'Default',
            'panels_as_tabs',
            'slot-1-0-label' => 'Salutation',
            'slot-1-0-name' => 'salutation',
            'slot-1-1-name' => '(empty)',
            'slot-1-10-label' => 'Email Opt Out:',
            'slot-1-10-name' => 'email_opt_out',
            'slot-1-11-name' => '(filler)',
            'slot-1-12-label' => 'Title:',
            'slot-1-12-name' => 'title',
            'slot-1-13-label' => 'Department:',
            'slot-1-13-name' => 'department',
            'slot-1-14-label' => 'Account Name:',
            'slot-1-14-name' => 'account_name',
            'slot-1-15-name' => '(empty)',
            'slot-1-16-label' => 'Primary Address Street',
            'slot-1-16-name' => 'primary_address_street',
            'slot-1-17-name' => '(empty)',
            'slot-1-18-label' => 'Primary Address City',
            'slot-1-18-name' => 'primary_address_city',
            'slot-1-19-label' => 'Primary Address State',
            'slot-1-19-name' => 'primary_address_state',
            'slot-1-2-label' => 'First Name:',
            'slot-1-2-name' => 'first_name',
            'slot-1-20-label' => 'Primary Address Postalcode',
            'slot-1-20-name' => 'primary_address_postalcode',
            'slot-1-21-label' => 'Primary Address Country',
            'slot-1-21-name' => 'primary_address_country',
            'slot-1-22-label' => 'LBL_DATE_ENTERED',
            'slot-1-22-name' => 'date_entered',
            'slot-1-23-label' => 'LBL_DATE_MODIFIED',
            'slot-1-23-name' => 'date_modified',
            'slot-1-3-label' => 'Last Name:',
            'slot-1-3-name' => 'last_name',
            'slot-1-4-label' => 'Office Phone:',
            'slot-1-4-name' => 'phone_work',
            'slot-1-5-label' => 'Mobile:',
            'slot-1-5-name' => 'phone_mobile',
            'slot-1-6-label' => 'Home Phone:',
            'slot-1-6-name' => 'phone_home',
            'slot-1-7-label' => 'Do Not Call:',
            'slot-1-7-name' => 'do_not_call',
            'slot-1-8-label' => 'Email Address:',
            'slot-1-8-name' => 'email1',
            'slot-1-9-label' => 'Other Email:',
            'slot-1-9-name' => 'email2',
            'sync_detail_and_edit    ',
            'to_pdf' => '1',
            'view' => 'EditView',
            'view_module' => 'Leads',
        );
    }

    /*
    * data provider for testing converting to and from canonical form
    */
    public function canonicalAndInternalForms() {
        // pull in our arrays
        require 'canonical_panel_test.php';
        require 'internal_panel_test.php';

        // this is php shorthand for returning an array( array($a[0],$b[0]), ...)
        return array_map(null,$canonicals,$internals);
    }

    /**
     * data provider for testing converting to canonical form
     */
    public function convertToCanonicalForms()
    {
        $tests = $this->canonicalAndInternalForms();
        // PAT-1934: restore defaults
        $tests[] = array(
            array(
                array(
                    'name' => 'PANEL_BODY',
                    'label' => 'PANEL_BODY',
                    'columns' => 2,
                    'labelsOnTop' => 1,
                    'placeholders' => 1,
                    'fields' => array(
                        array(
                            'name' => 'duration',
                            'span' => 9,
                        ),
                        array(
                            'name' => 'repeat_type',
                            'span' => 3,
                        ),
                    ),
                ),
            ),
            array(
                'panel_body' => array(
                    array(
                        'duration',
                        'repeat_type',
                    ),
                ),
            ),
            array(
                'duration' => array(
                    'name' => 'duration',
                    'span' => 9,
                ),
                'repeat_type' => array(
                    'name' => 'repeat_type',
                    'span' => 3,
                ),
            ),
            array(
                'panels' => array(
                    array(
                        'name' => 'panel_body',
                        'fields' => array(
                            array(
                                'name' => 'repeat_type',
                                'span' => 12,
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'duration' => array(
                    'name' => 'duration',
                    'span' => 9,
                ),
                'repeat_type' => array(
                    'name' => 'repeat_type',
                    'span' => 3,
                ),
            ),
        );

        // PAT-1837, 1611: re-calculate spans from previous view defs
        $tests[] = array(
            array(
                array(
                    'name' => 'PANEL_BODY',
                    'label' => 'PANEL_BODY',
                    'columns' => 2,
                    'labelsOnTop' => 1,
                    'placeholders' => 1,
                    'fields' => array(
                        array(
                            'name' => 'account_name',
                        ),
                        array(
                            'name' => 'email',
                        ),
                    ),
                ),
            ),
            array(
                'panel_body' => array(
                    array(
                        'account_name',
                        'email',
                    ),
                ),
            ),
            array(
                'account_name',
                'email',
            ),
            // previous view defs
            array(
                'panels' => array(
                    array(
                        'name' => 'panel_body',
                        'fields' => array(
                            array(
                                'name' => 'account_name',
                                'span' => 12,
                            ),
                            array(
                                'name' => 'email',
                                'span' => 12,
                            ),
                        ),
                    ),
                ),
            ),
        );

        // PAT-2410: field with base def is single unit on end column
        $tests[] = array(
            array(
                array(
                    'name' => 'PANEL_BODY',
                    'label' => 'PANEL_BODY',
                    'columns' => 2,
                    'labelsOnTop' => 1,
                    'placeholders' => 1,
                    'fields' => array(
                        '',
                        array(
                            'name' => 'description',
                        ),
                    ),
                ),
            ),
            array(
                'panel_body' => array(
                    array(
                        '',
                        'description',
                    ),
                ),
            ),
            array(
                'description',
            ),
            array(
                'panels' => array(
                    array(
                        'name' => 'panel_body',
                        'fields' => array(
                            array(
                                'name' => 'description',
                                'span' => 12,
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'description' => array(
                    'name' => 'description',
                    'span' => 12,
                ),
            ),
        );

        return $tests;
    }

    /**
     * @dataProvider canonicalAndInternalForms
     * @param $input
     * @param $expected
     */
    public function testConvertFromCanonicalForm($input, $expected)
    {
        static $it = 0;

        $output = $this->_parser->convertFromCanonicalForm($input);

        $this->assertEquals($expected, $output, "Iteration $it expectation did not match result");

        $it++;
    }

    public function canonicalAndInternalFieldList()
    {
        return array(
            array(
                // canonical panels
                array(array(
                    'name' => 'Default',
                    'columns' => 2,
                    'fields' => array(
                        array(
                            'name' => 'name',
                            'label' => 'Name',
                        ),
                        array(
                            'name' => 'status',
                            'label' => 'Status',
                        ),
                        array(
                            'name' => 'description',
                            'label' => 'Description',
                        ),
//                        ""
                ))),
                // internal fieldlist
                array(
                    'name' => array(
                        'name' => 'name',
                        'label' => 'Name',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'label' => 'Status',
                    ),
                    'description' => array(
                        'name' => 'description',
                        'label' => 'Description',
                    ),
//                    "" => null,

                )
            ),
            array(
                // internal panels
                array('Default' => array(
                    array( //row 1
                        array(
                            'name' => 'name',
                            'label' => 'LBL_NAME',
                            'span' => 12
                        ),
                        MBConstants::$EMPTY
                    ),
                    array( //row 2
                        array(
                            'name' => 'status',
                            'label' => 'LBL_STATUS',
                        ),
                        array(
                            'name' => 'description',
                            'label' => 'LBL_DESCRIPTION',
                        ),
                    ),
                )),
                // field list
                array(
                    'name' =>  array(
                        'name' => 'name',
                        'label' => 'LBL_NAME',
                        'span' => 12
                    ),
                    '(empty)' => MBConstants::$EMPTY,
                    'status' =>  array(
                        'name' => 'status',
                        'label' => 'LBL_STATUS',
                    ),
                    'description' => array(
                        'name' => 'description',
                        'label' => 'LBL_DESCRIPTION',
                    ),

                )
            ),

        );
    }

    /**
     * @dataProvider canonicalAndInternalFieldList
     * @param input
     * @param expected
     */
    public function testGetFieldsFromLayout($input, $expected)
    {
        $output = $this->_parser->testGetFieldsFromLayout(array('panels' => $input));
        $this->assertEquals($expected, $output);
    }

    /**
     * @dataProvider canonicalAndInternalFieldList
     * @param input
     * @param expected
     */
    public function testGetFieldsFromLayoutUsingFullViewdef($input, $expected)
    {
        // put on the additional array path on the input
        $canonical_input['portal']['view']['record']['panels'] = $input;
        $output = $this->_parser->testGetFieldsFromLayout($canonical_input);
        $this->assertEquals($expected, $output);
    }

    /**
     * @dataProvider convertToCanonicalForms
     * @param $expected
     * @param $panels
     * @param $fieldDef
     * @param $previousViewDef
     * @param $baseViewDef
     */
    public function testConvertToCanonicalForm($expected, $panels, $fieldDef = null, $previousViewDef = null, $baseViewDef = null)
    {
        // need this to prime our viewdefs
        $this->_parser->testInstallOriginalViewdefs(array(
            'panels' => $expected
        ));

        if ($previousViewDef) {
            $implementation = $this->_parser->getImplementation();
            $ref = new ReflectionClass($implementation);
            $prop = $ref->getProperty('_viewdefs');
            $prop->setAccessible(true);
            $prop->setValue($implementation, $previousViewDef);
        }

        if ($baseViewDef) {
            $this->_parser->testInstallBaseViewFields($baseViewDef);
        }

        $output = $this->_parser->testConvertToCanonicalForm($panels, $fieldDef);

        $this->assertEquals($expected, $output);

    }

    /**
     * Tests panel label setting
     *
     * @dataProvider panelDefsLabelsProvider
     * @param array $panel A mock panels array
     * @param string $expectation Expected converted value
     */
    public function testPanelLabelsAreSetByPanelDefs($panel, $expectation)
    {
        // Convert the panel def
        $converted =  $this->_parser->convertFromCanonicalForm($panel);

        // Get the key from the conversion as this is the label
        $label = key($converted);

        // Assert
        $this->assertEquals($expectation, $label, "Expected $expectation but label was returned as $label");
    }

    /**
     * Data provider for panel label tester
     *
     * @return array
     */
    public function panelDefsLabelsProvider()
    {
        return array(
            // Tests a set label in the defs
            array('panel' => array(array('label' => 'Super Awesome Label', 'fields' => array())), 'expectation' => 'Super Awesome Label'),

            // Tests no label set but a panel name set
            array('panel' => array(array('name' => 'panel_hidden', 'fields' => array())), 'expectation' => 'LBL_RECORD_SHOWMORE'),
            array('panel' => array(array('name' => 'panel_header', 'fields' => array())), 'expectation' => 'LBL_RECORD_HEADER'),
            array('panel' => array(array('name' => 'panel_body', 'fields' => array())), 'expectation' => 'LBL_RECORD_BODY'),

            // Tests no label or name so uses the array key as the label
            array('panel' => array(array('foo' => 'bar', 'fields' => array())), 'expectation' => 0),
        );
    }

    /**
     * Tests parsing of readonly properties of field defs
     *
     * @dataProvider readonlyPropTestProvider
     * @param array $defs Mock array of vardefs to trim
     * @param boolean $expectation Assertion to test
     */
    public function testReadonlyPropertyIsParsed($defs, $expectation)
    {
        $result = $this->_parser->_trimFieldDefs($defs);
        $actual = !empty($result['readonly']);
        $this->assertEquals($expectation, $actual, "Assertion of readonly property existence failed");
    }

    public function readonlyPropTestProvider()
    {
        return array(
            array('defs' => array('name' => 'test1', 'vname' => 'LBL_TEST1', 'readonly' => true), 'expectation' => true),
            array('defs' => array('name' => 'test2', 'vname' => 'LBL_TEST2'), 'expectation' => false),
        );
    }

    /**
     * Test handling of span adjustments and mutation of the baseSpans array
     *
     * @param int $fieldCount The count of fields in a row
     * @param array $lastField The last field that was touched
     * @param array $baseSpans Array of fields that had spans orignally applied
     * @param int $singleSpanUnit The size of a single span
     * @param array $expectResult Expected return value
     * @param array $expectBaseSpans Expected baseSpans array
     * @dataProvider spanAdjustmentsProvider
     */
    public function testSpanAdjustments($fieldCount, $lastField, $baseSpans, $singleSpanUnit, $expectResult, $expectBaseSpans)
    {
        $this->_parser->setBaseSpans($baseSpans);
        $result = $this->_parser->testGetLastFieldSpan($lastField, $singleSpanUnit, $fieldCount);

        // Test the result
        $this->assertEquals($result, $expectResult);

        // Test the adjusted spans
        $adjSpans = $this->_parser->getBaseSpans();
        $this->assertEquals($adjSpans, $expectBaseSpans);
    }

    public function spanAdjustmentsProvider()
    {
        // maxSpan on the parser is 12 by default
        // maxCols on the parser is 2 by default
        return array(
            // Test no handling for single field rows
            array(
                'fieldCount' => 1,
                'lastField' => null,
                'baseSpans' => array(),
                'singleSpanUnit' => 6,
                'expectResult' => array('span' => 12),
                'expectBaseSpans' => array(),
            ),
            // Test OOTB behavior
            array(
                'fieldCount' => 2,
                'lastField' => array('name' => 'test'),
                'baseSpans' => array('test' => 12),
                'singleSpanUnit' => 6,
                'expectResult' => array('span' => 6),
                'expectBaseSpans' => array(
                    'test' => array(
                        'span' => 6,
                        'adjustment' => 6,
                    ),
                ),
            ),
            // Test oddball single span behavior
            array(
                'fieldCount' => 2,
                'lastField' => array('name' => 'test'),
                'baseSpans' => array('test' => 12),
                'singleSpanUnit' => 4,
                'expectResult' => array('span' => 4),
                'expectBaseSpans' => array(
                    'test' => array(
                        'span' => 8,
                        'adjustment' => 4,
                    ),
                ),
            ),
            // Test no changing of the lastField from baseSpans
            array(
                'fieldCount' => 2,
                'lastField' => array('name' => 'test'),
                'baseSpans' => array('test' => 9),
                'singleSpanUnit' => 6,
                'expectResult' => array('span' => 3),
                'expectBaseSpans' => array(
                    'test' => array(
                        'span' => 9,
                        'adjustment' => 0,
                    ),
                ),
            ),
            // Test no handling if no lastField name
            array(
                'fieldCount' => 2,
                'lastField' => array(),
                'baseSpans' => array('test' => 6, 'test1' => 12),
                'singleSpanUnit' => 6,
                'expectResult' => array(),
                'expectBaseSpans' => array(
                    'test' => array(
                        'span' => 6,
                        'adjustment' => 0,
                    ),
                    'test1' => array(
                        'span' => 12,
                        'adjustment' => 0,
                    ),
                ),
            ),
            // Test no handling if no baseSpans of the field name
            array(
                'fieldCount' => 2,
                'lastField' => array('name' => 'test'),
                'baseSpans' => array('test1' => 12),
                'singleSpanUnit' => 6,
                'expectResult' => array(),
                'expectBaseSpans' => array(
                    'test1' => array(
                        'span' => 12,
                        'adjustment' => 0,
                    ),
                ),
            ),
        );
    }

    /**
     * @covers SidecarGridLayoutMetaDataParser::removeField
     */
    public function testRemoveFieldRemovesField()
    {
        $panel = array(
            'LBL_RECORD_BODY' => array(
                array(
                    0 => 'account_name',
                    1 => 'date_closed',
                ),
                array(
                    0 => 'amount',
                    1 => '(empty)',
                ),
                array(
                    0 => 'best_case',
                    1 => 'worst_case',
                ),
                array(
                    0 => 'sales_status',
                    1 => '(filler)',
                ),
            )
        );

        $this->_parser->_viewdefs['panels'] = $panel;

        $this->_parser->removeField('sales_status');

        foreach ( $this->_parser->_viewdefs [ 'panels' ] as $panelID => $panel ) {
            foreach ($panel as $rowIndex => $row) {
                if (is_array($row)) {
                    foreach ($row as $fieldIndex => $field) {
                        $this->assertNotEquals('sales_stage', $field);
                    }
                }
            }
        }
    }

    /**
     * @covers SidecarGridLayoutMetaDataParser::removeField
     */
    public function testRemoveFieldRemovesRowWithEmptyAndFiller()
    {
        $panel = array(
            'LBL_RECORD_BODY' => array(
                array(
                    0 => 'account_name',
                    1 => 'date_closed',
                ),
                array(
                    0 => 'amount',
                    1 => '(empty)',
                ),
                array(
                    0 => 'best_case',
                    1 => 'worst_case',
                ),
                array(
                    0 => 'sales_status',
                    1 => '(filler)',
                ),
            )
        );

        $this->_parser->_viewdefs['panels'] = $panel;

        $this->_parser->removeField('sales_status');

        $this->assertCount(3, $this->_parser->_viewdefs['panels']['LBL_RECORD_BODY']);
    }

    /**
     * @covers SidecarGridLayoutMetaDataParser::removeField
     */
    public function testRemoveFieldRemovesEmptyPanel()
    {
        $panels = array(
            'LBL_PANEL_1' => array(
                array(
                    'field_1',
                ),
            ),
            'LBL_PANEL_2' => array(
                array(
                    'field_2',
                ),
            ),
        );

        $this->_parser->_viewdefs['panels'] = $panels;

        $this->_parser->removeField('field_1');

        $this->assertSame(array(
            'LBL_PANEL_2' => array(
                array(
                    'field_2',
                ),
            ),
        ), $this->_parser->_viewdefs['panels']);
    }
}



/**
 * Using derived helper class from SidecarGridLayoutMetaDataParser to test canonical/internal
 * format conversions without saving the file.
 *
 * lifted from SearchViewMDPTest
 */
class SidecarGridLayoutMetaDataParserTestDerivative extends SidecarGridLayoutMetaDataParser
{
    // dummy constructor for now
    public function __construct($view, $moduleName, $implementation)
    {
        $view = strtolower ( $view ) ;

        $this->FILLER = array ( 'name' => MBConstants::$FILLER['name'] , 'label' => translate ( MBConstants::$FILLER['label'] ) ) ;

        $this->_moduleName = $moduleName ;
        $this->_view = $view ;

        $module = StudioModuleFactory::getStudioModule( $moduleName ) ;
        $this->module_dir = $module->seed->module_dir;
        $this->_fielddefs = $module->getFields();
        $this->_standardizeFieldLabels( $this->_fielddefs );
        $this->implementation = $implementation;
    }

    public function getImplementation()
    {
        return $this->implementation;
    }

    public function testInstallOriginalViewdefs($viewdefs)
    {
        $this->_originalViewDef = $this->getFieldsFromLayout($viewdefs);
    }

    public function testInstallBaseViewFields($fields = array())
    {
        $this->baseViewFields = $fields;
    }

    public function testConvertToCanonicalForm($panels, $fielddefs=null) {
        if ($fielddefs==null)
        {
            $fielddefs = $this->_fielddefs;
        }

        // spoof our internal viewdefs


        return $this->_convertToCanonicalForm($panels, $fielddefs);
    }

    public function testPopulateFromRequest(&$fielddefs) {
        // ??
    }

    public function testGetFieldsFromLayout($viewdef) {
        return $this->getFieldsFromLayout($viewdef);
    }

    public function testGetLastFieldSpan($lastField, $singleSpanUnit, $fieldCount)
    {
        return $this->getLastFieldSpan($lastField, $singleSpanUnit, $fieldCount);
    }

    public function setBaseSpans($spans)
    {
        foreach ($spans as $name => $value) {
            $this->setBaseSpan($name, $value);
        }
    }

    public function getBaseSpans()
    {
        return $this->baseSpans;
    }
}
