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
 
require_once('vendor/nusoap//nusoap.php');

class AdvancedSearchWidgetTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_sugarField;
    private $_smarty;
    private $_params;
    private $_customSugarFieldTeamsetContents;

	public function setUp()
	{
        if(file_exists('custom/include/SugarFields/Fields/Teamset/SugarFieldTeamset.php'))
        {
           $this->_customSugarFieldTeamsetContents = file_get_contents('custom/include/SugarFields/Fields/Teamset/SugarFieldTeamset.php');
           unlink('custom/include/SugarFields/Fields/Teamset/SugarFieldTeamset.php');
        }

		$sfh = new SugarFieldHandler();
		$this->_sugarField = $sfh->getSugarField('Teamset', true);

		$this->_params = array();
		$this->_params['parentFieldArray'] = 'fields';
		$this->_params['tabindex'] = true;
		$this->_params['displayType'] = 'renderSearchView';
    	$this->_params['display'] = '';
    	$this->_params['labelSpan'] = '';
    	$this->_params['fieldSpan'] = '';
    	$this->_params['formName'] = 'search_form';
    	$this->_params['displayParams'] = array('formName'=>'');
		$team = BeanFactory::newBean('Accounts');
		$fieldDefs = $team->field_defs;
		$fieldDefs['team_name_advanced'] = $fieldDefs['team_name'];
		$fieldDefs['team_name_advanced']['name'] = 'team_name_advanced';
		$this->_smarty = new Sugar_Smarty();
		$this->_smarty->assign('fields', $fieldDefs);
		$this->_smarty->assign('displayParams', array());
		$_REQUEST['module'] = 'Accounts';
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    	if(!empty($this->_customSugarFieldTeamsetContents))
        {
            file_put_contents('custom/include/SugarFields/Fields/Teamset/SugarFieldTeamset.php', $this->_customSugarFieldTeamsetContents);
        }
    }

    protected function checkSearchValues($html)
    {
		$matches = array();
        preg_match_all("'(<script[^>]*?>)(.*?)(</script[^>]*?>)'si", $html, $matches, PREG_PATTERN_ORDER);
	    $this->assertTrue(isset($matches[0][5]), "Check that the script tags are rendered for advanced teams widget");
		if(isset($matches[0][5])) {
	       $js = $matches[0][5];
	       $valueMatches = array();
	       if(preg_match_all('/\.value = \"([^\"]+)\"/', $js, $valueMatches, PREG_PATTERN_ORDER)) {
	       	  $this->assertEquals($valueMatches[1][0], 'West', "Check that team 'West' is the first team in widget as specified by arguments");
	       	  $this->assertEquals($valueMatches[1][1], 'West', "Check that team 'West' is the first team in widget as specified by arguments");
	       }
	    }
    }

    public function testSearchValuesFromRequest()
    {
    	$_REQUEST['form_name'] = '';
	    $_REQUEST['update_fields_team_name_advanced_collection'] = '';
	    $_REQUEST['team_name_advanced_new_on_update'] = false;
	    $_REQUEST['team_name_advanced_allow_update'] = '';
	    $_REQUEST['team_name_advanced_allowed_to_check'] = false;
	    $_REQUEST['team_name_advanced_field'] = 'team_name_advanced_table';
	    $_REQUEST['team_name_advanced_collection_0'] = 'West';
	    $_REQUEST['id_team_name_advanced_collection_0'] = 'West';
	    $_REQUEST['primary_team_name_advanced_collection'] = 0;
	    $_REQUEST['team_name_advanced_type'] = 'all';
		$this->_sugarField->render($this->_params, $this->_smarty);
		$this->setOutputCallback(array($this, "checkSearchValues"));
    }
}
