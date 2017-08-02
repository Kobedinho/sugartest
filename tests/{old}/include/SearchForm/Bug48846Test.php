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

require_once 'include/SearchForm/SearchForm2.php';

class Bug48846Test extends Sugar_PHPUnit_Framework_TestCase {

    var $module = 'Cases';
    var $action = 'wirelesslist';
    var $seed;
    var $form;
    var $array;

    public function setUp() {
        require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;

        require "modules/".$this->module."/metadata/searchdefs.php";
        require "modules/".$this->module."/metadata/SearchFields.php";
        require "modules/".$this->module."/metadata/listviewdefs.php";

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);

        $this->seed = new $beanList[$this->module];
        $this->form = new SearchForm($this->seed, $this->module, $this->action);
        $this->form->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', "advanced_search", $listViewDefs);

        $this->array = array(
            'module'=>$this->module,
            'action'=>$this->action,
            'searchFormTab'=>'advanced_search',
            'query'=>'true',
        );
    }

    public function tearDown() {
        unset($this->array);
        unset($this->form);
        unset($this->seed);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }


    /**
     * testSearchInt
     *
     * tests where generation in search form
     *
     * @dataProvider searchIntProvider
     */
    public function testSearchInt($exp, $val) {
        $this->array['case_number_advanced'] = $val;

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertSame($exp, $query[0]);
    }

    /**
     * searchIntProvider
     *
     * @return Array values for testing
     */
    public function searchIntProvider() {
        return array(
            array("cases.case_number in (123)", 123),
            array("cases.case_number in (-1)", 'test'),
            array("cases.case_number in (12,14,16)", '12,14,16'),
            array("cases.case_number in (12,-1,16)", '12,junk,16'),
            array("cases.case_number in (-1,12,-1,16,34,124,-1)", 'stuff,12,junk,16,34,124,morejunk'),
        );
    }

}
