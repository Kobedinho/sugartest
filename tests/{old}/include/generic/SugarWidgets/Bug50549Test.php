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



class Bug50549Test extends Sugar_PHPUnit_Framework_TestCase {

    var $field;

    public function setUp() {
        global $beanList, $beanFiles;
        require('include/modules.php');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['sugar_config']['default_language']);

        $mock = new Bug50549MockReporter();
        $this->field = new SugarWidgetFieldMultiEnum($mock);
    }

    public function tearDown() {
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    /**
     * @dataProvider bug50549DataProvider
     */
    public function testQuery($layout_def, $expected) {
		switch ($layout_def['qualifier_name']) {
	    	case 'is_not':
	    		$function = "queryFilteris_not";
	    		break;
	    	case 'is': 
	    		$function = "queryFilteris";
	    		break;
	    	default:
	    		$function = "";
	    		break;
        }
        $this->assertContains($expected, $this->field->$function($layout_def), "Filter: '" . strtoupper($layout_def['qualifier_name']) . "' not adding carets.");
    }

    /**
     * Data provider for testColumnLabelsAreCorrectForMatrixReport()
     * @return array layout_def, expected
     */
    public function bug50549DataProvider() {
        return array(
            '0' => array( 
                array (
  					'name' => 'multi_c',
  					'table_key' => 'self',
			  		'qualifier_name' => 'is_not',
			  		'runtime' => 1,
			  		'input_name0' => 
			  		array (
			    		0 => 'B',
			  		),
			  		'column_name' => 'self:multi_c',
			  		'id' => 'rowid0',
			  		'table_alias' => 'cases_cstm',
			  		'column_key' => 'self:multi_c',
			  		'type' => 'multienum',
				),
                "<> '^B^'",
            ),
            '1' => array( 
                array (
  					'name' => 'multi_c',
  					'table_key' => 'self',
			  		'qualifier_name' => 'is',
			  		'runtime' => 1,
			  		'input_name0' => 
			  		array (
			    		0 => 'C',
			  		),
			  		'column_name' => 'self:multi_c',
			  		'id' => 'rowid0',
			  		'table_alias' => 'cases_cstm',
			  		'column_key' => 'self:multi_c',
			  		'type' => 'multienum',
				),
                "= '^C^'",
            ),
            '2' => array( 
                array (
  					'name' => 'multi_c2',
  					'table_key' => 'self',
			  		'qualifier_name' => 'is_not',
			  		'runtime' => 1,
			  		'input_name0' => 
			  		array (
			    		0 => 'B',
			  		),
			  		'column_name' => 'self:multi_c2',
			  		'id' => 'rowid0',
			  		'table_alias' => 'cases_cstm',
			  		'column_key' => 'self:multi_c2',
			  		'type' => 'multienum',
				),
                "<> 'B'",
            ),
            '3' => array( 
                array (
  					'name' => 'multi_c2',
  					'table_key' => 'self',
			  		'qualifier_name' => 'is',
			  		'runtime' => 1,
			  		'input_name0' => 
			  		array (
			    		0 => 'C',
			  		),
			  		'column_name' => 'self:multi_c2',
			  		'id' => 'rowid0',
			  		'table_alias' => 'cases_cstm',
			  		'column_key' => 'self:multi_c2',
			  		'type' => 'multienum',
				),
                "= 'C'",
            ),
        );
    }
}


class Bug50549MockReporter extends Report {

	var $all_fields = array (
		'self:multi_c' => array (
	  		'dependency' => '',
	  		'required' => false,
	  		'source' => 'custom_fields',
	  		'name' => 'multi_c',
	  		'vname' => 'LBL_MULTI',
	  		'type' => 'multienum',
	  		'massupdate' => '0',
	  		'default' => '^A^',
	  		'comments' => '',
	  		'help' => '',
	  		'importable' => 'true',
	  		'duplicate_merge' => 'disabled',
	  		'duplicate_merge_dom_value' => '0',
	  		'audited' => false,
	  		'reportable' => true,
	  		'unified_search' => false,
	  		'calculated' => false,
	  		'size' => '20',
	  		'options' => 'test_list',
	  		'studio' => 'visible',
	  		'isMultiSelect' => true,
			'id' => 'Casesmulti_c',
	  		'custom_module' => 'Cases',
	  		'module' => 'Cases',
	  		'real_table' => 'cases_cstm',
		),
		'self:multi_c2' => array (
	  		'dependency' => '',
	  		'required' => false,
	  		'name' => 'multi_c',
	  		'vname' => 'LBL_MULTI',
	  		'type' => 'multienum',
	  		'massupdate' => '0',
	  		'default' => '^A^',
	  		'comments' => '',
	  		'help' => '',
	  		'importable' => 'true',
	  		'duplicate_merge' => 'disabled',
	  		'duplicate_merge_dom_value' => '0',
	  		'audited' => false,
	  		'reportable' => true,
	  		'unified_search' => false,
	  		'calculated' => false,
	  		'size' => '20',
	  		'options' => 'test_list',
	  		'studio' => 'visible',
	  		'isMultiSelect' => true,
			'id' => 'Casesmulti_c',
	  		'custom_module' => 'Cases',
	  		'module' => 'Cases',
	  		'real_table' => 'cases_cstm',
		)
	);
	 
	public function getAttribute($name)
    {
        return $this;
    }
}


?>
