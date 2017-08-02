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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;


class ListViewDisplayTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $save_query;

    /**
     * @var ListViewDisplayMock
     */
    private $_lvd;

    public function setUp()
    {
        $this->_lvd = new ListViewDisplay();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        global $sugar_config;
        if(isset($sugar_config['save_query']))
        {
            $this->save_query = $sugar_config['save_query'];
        }
    }

    public function tearDown()
    {
        global $sugar_config;
        if(!empty($this->save_query))
        {
            $sugar_config['save_query'] = $this->save_query;
        }
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    	unset($GLOBALS['current_user']);
    	unset($GLOBALS['app_strings']);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('ListViewData',$this->_lvd->lvd);
        $this->assertInternalType('array',$this->_lvd->searchColumns);
    }

    public function testShouldProcessWhenConfigSaveQueryIsNotSet()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = null;

        $this->assertTrue($this->_lvd->shouldProcess('foo'));
        $this->assertTrue($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenConfigSaveQueryIsNotPopulateOnly()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_always';

        $this->assertTrue($this->_lvd->shouldProcess('foo'));
        $this->assertTrue($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsTrue()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = true;

        $this->assertTrue($this->_lvd->shouldProcess('foo'));
        $this->assertTrue($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsTrue()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = true;
        $_REQUEST['module'] = 'foo';

        $this->assertFalse($this->_lvd->shouldProcess('foo'));
        $this->assertFalse($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoNotEqual()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'bar';

        $this->assertTrue($this->_lvd->shouldProcess('foo'));
        $this->assertTrue($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoEqualAndQueryIsEmpty()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = '';
        $_SESSION['last_search_mod'] = '';

        $this->assertFalse($this->_lvd->shouldProcess('foo'));
        $this->assertFalse($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoEqualAndQueryEqualsMsi()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = 'MSI';
        $_SESSION['last_search_mod'] = '';

        $this->assertFalse($this->_lvd->shouldProcess('foo'));
        $this->assertFalse($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoNotEqualAndQueryDoesNotEqualsMsi()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = 'xMSI';
        $_SESSION['last_search_mod'] = '';

        $this->assertTrue($this->_lvd->shouldProcess('foo'));
        $this->assertTrue($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoEqualAndLastSearchModEqualsModule()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = '';
        $_SESSION['last_search_mod'] = 'foo';

        //C.L. Because of fix to 40186, the following two tests are now set to assertFalse
        $this->assertFalse($this->_lvd->shouldProcess('foo'), 'Assert that ListViewDisplay->shouldProcess is false even if module is the same because no query was specified');
        $this->assertFalse($this->_lvd->should_process, 'Assert that ListViewDisplay->shouldProcess class variable is false');

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoEqualAndLastSearchModDoesNotEqualsModule()
    {
        if ( isset($GLOBALS['sugar_config']['save_query']) ) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = '';
        $_SESSION['last_search_mod'] = 'bar';

        $this->assertFalse($this->_lvd->shouldProcess('foo'));
        $this->assertFalse($this->_lvd->should_process);

        if ( isset($oldsavequery) ) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testProcess()
    {
        $data = array(
            'data' => array(1,2,3),
            'pageData' => array('bean' => array('moduleDir'=>'testmoduledir'))
            );
        $this->_lvd->process('foo',$data,'testmetestme');

        $this->assertEquals(3,$this->_lvd->rowCount);
        $this->assertEquals('testmoduledir2_TESTMETESTME_offset',$this->_lvd->moduleString);
    }

    public function testDisplayIfShouldNotProcess()
    {
        $this->_lvd->should_process = false;

        $this->assertEmpty($this->_lvd->display());
    }

    public function testDisplayIfMultiSelectFalse()
    {
        $this->_lvd->should_process = true;
        $this->_lvd->multiSelect = false;

        $this->assertEmpty($this->_lvd->display());
    }

    public function testDisplayIfShowMassUpdateFormFalse()
    {
        $this->_lvd->should_process = true;
        $this->_lvd->show_mass_update_form = false;

        $this->assertEmpty($this->_lvd->display());
    }

    public function testDisplayIfShowMassUpdateFormTrueAndMultiSelectTrue()
    {
        $this->_lvd->should_process = true;
        $this->_lvd->show_mass_update_form = true;
        $this->_lvd->multiSelect = true;
        $this->_lvd->multi_select_popup = true;
        $this->_lvd->mass = $this->createMock('MassUpdate');
        $this->_lvd->mass->expects($this->any())
                         ->method('getDisplayMassUpdateForm')
                         ->will($this->returnValue('foo'));
        $this->_lvd->mass->expects($this->any())
                         ->method('getMassUpdateFormHeader')
                         ->will($this->returnValue('bar'));

        $this->assertEquals('foobar',$this->_lvd->display());
    }

    public function testBuildSelectLink()
    {
        $output = $this->_lvd->buildSelectLink();
        $output = implode($output['buttons'],"");
        $this->assertContains("<a id='select_link'",$output);
        $this->assertContains("sListView.check_all(document.MassUpdate, \"mass[]\", true, 0)",$output);
        $this->assertContains("sListView.check_entire_list(document.MassUpdate, \"mass[]\",true,0);",$output);
    }

    public function testBuildSelectLinkWithParameters()
    {
        $output = $this->_lvd->buildSelectLink('testtest',1,2);
        $output = implode($output['buttons'],"");
        $this->assertContains("<a id='testtest'",$output);
        $this->assertContains("sListView.check_all(document.MassUpdate, \"mass[]\", true, 2)",$output);
        $this->assertContains("sListView.check_entire_list(document.MassUpdate, \"mass[]\",true,1);",$output);
    }

    public function testBuildSelectLinkWithPageTotalLessThanZero()
    {
        $output = $this->_lvd->buildSelectLink('testtest',1,-1);
        $output = implode($output['buttons'],"");
        $this->assertContains("<a id='testtest'",$output);
        $this->assertContains("sListView.check_all(document.MassUpdate, \"mass[]\", true, 1)",$output);
        $this->assertContains("sListView.check_entire_list(document.MassUpdate, \"mass[]\",true,1);",$output);
    }

    public function testBuildExportLink()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->module_dir = 'testtest';
        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildExportLink');

        $this->assertContains("return sListView.send_form(true, 'testtest', 'index.php?entryPoint=export',",$output);
    }

    public function testBuildMassUpdateLink()
    {
        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildMassUpdateLink');
        
        $this->assertRegExp("/.*document\.getElementById\(['\"]massupdate_form['\"]\)\.style\.display\s*=\s*['\"]['\"].*/", $output);
    }

    public function composeEmailEmptyDataProvider()
    {
        return array(
            array(false),
            array(array()),
            array(
                array(
                    'field1' => array(
                        'type' => 'text',
                    ),
                )
            )
        );
    }

    /**
     * @dataProvider composeEmailEmptyDataProvider
     */
    public function testComposeEmailEmpty($fieldDefs)
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->field_defs = $fieldDefs;
        
        $this->assertEmpty(SugarTestReflection::callProtectedMethod($this->_lvd, 'buildComposeEmailLink', array(0)));
    }

    public function testComposeEmailIfFieldDefsIfUsingSugarEmailClient()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->object_name = 'foobar';
        $this->_lvd->seed->module_dir = 'foobarfoobar';
        $this->_lvd->seed->field_defs = array(
            'field1' => array(
                'type' => 'link',
                'relationship' => 'foobar_emailaddresses',
                ),
            );
        $GLOBALS['dictionary']['foobar']['relationships']['foobar_emailaddresses']['rhs_module'] = 'EmailAddresses';
        $GLOBALS['current_user']->setPreference('email_link_type','sugar');

        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildComposeEmailLink', array(5));

        $this->assertContains(', \'foobarfoobar\', \'5\', ',$output);

        unset($GLOBALS['dictionary']['foobar']);
    }

    public function testComposeEmailIfFieldDefsIfUsingExternalEmailClient()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->object_name = 'foobar';
        $this->_lvd->seed->module_dir = 'foobarfoobar';
        $_REQUEST['module'] = 'foobarfoobar';
        $this->_lvd->seed->field_defs = array(
            'field1' => array(
                'type' => 'link',
                'relationship' => 'foobar_emailaddresses',
                ),
            );
        $_REQUEST['module'] = 'foo';
        
        $GLOBALS['dictionary']['foobar']['relationships']['foobar_emailaddresses']['rhs_module'] = 'EmailAddresses';
        $GLOBALS['current_user']->setPreference('email_link_type','mailto');

        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildComposeEmailLink', array(5));

        $this->assertContains('sListView.use_external_mail_client',$output);

        unset($GLOBALS['dictionary']['foobar']);
        unset($_REQUEST['module']);
    }

    public function testBuildDeleteLink()
    {
        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildDeleteLink');

        $this->assertContains("return sListView.send_mass_update",$output);
    }

    public function testBuildSelectedObjectsSpan()
    {
        $output = $this->_lvd->buildSelectedObjectsSpan(1,1);

        $this->assertContains("<input  style='border: 0px; background: transparent; font-size: inherit; color: inherit' type='text' id='selectCountTop' readonly name='selectCount[]' value='1' />",$output);
    }

    public function testBuildMergeDuplicatesLinkWithNoAccess()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testBuildMergeDuplicatesLinkWhenModuleDoesNotHaveItEnabled()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->object_name = 'foobar';
        $this->_lvd->seed->module_dir = 'foobarfoobar';
        $GLOBALS['dictionary']['foobar']['duplicate_merge'] = false;
        $GLOBALS['current_user']->is_admin = 1;

        $this->assertEmpty(SugarTestReflection::callProtectedMethod($this->_lvd, 'buildMergeDuplicatesLink'));
    }

    public function testBuildMergeDuplicatesLink()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->object_name = 'foobar';
        $this->_lvd->seed->module_dir = 'foobarfoobar';
        $GLOBALS['dictionary']['foobar']['duplicate_merge'] = true;
        $GLOBALS['current_user']->is_admin = 1;

        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildMergeDuplicatesLink');

        $this->assertContains('"foobarfoobar", "");}', htmlspecialchars_decode($output));
    }

    public function testBuildMergeDuplicatesLinkBuildsReturnString()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->object_name = 'foobar';
        $this->_lvd->seed->module_dir = 'foobarfoobar';
        $GLOBALS['dictionary']['foobar']['duplicate_merge'] = true;
        $GLOBALS['current_user']->is_admin = 1;

        $request = InputValidation::create(array(
            'module' => 'Accounts',
            'action' => 'bar',
            'record' => '1',
        ), array());
        SugarTestReflection::setProtectedValue($this->_lvd, 'request', $request);

        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildMergeDuplicatesLink');

        $this->assertContains(
            '"foobarfoobar", "&return_module=Accounts&return_action=bar&return_id=1");}',
            htmlspecialchars_decode($output)
        );
    }

    public function testBuildMergeLinkWhenUserDisabledMailMerge()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->module_dir = 'foobarfoobar';
        $GLOBALS['current_user']->setPreference('mailmerge_on','off');

        $this->assertEmpty(SugarTestReflection::callProtectedMethod($this->_lvd, 'buildMergeLink'));
    }

    public function testBuildMergeLinkWhenSystemDisabledMailMerge()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->module_dir = 'foobarfoobar';

        $GLOBALS['current_user']->setPreference('mailmerge_on','on');

        $settings_cache = sugar_cache_retrieve('admin_settings_cache');
        if ( empty($settings_cache) ) {
            $settings_cache = array();
        }
        $settings_cache['system_mailmerge_on'] = false;
        sugar_cache_put('admin_settings_cache',$settings_cache);

        $this->assertEmpty(SugarTestReflection::callProtectedMethod($this->_lvd, 'buildMergeLink'));

        sugar_cache_clear('admin_settings_cache');
    }

    public function testBuildMergeLinkWhenModuleNotInModulesArray()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->module_dir = 'foobarfoobar';

        $GLOBALS['current_user']->setPreference('mailmerge_on','on');

        $settings_cache = sugar_cache_retrieve('admin_settings_cache');
        if ( empty($settings_cache) ) {
            $settings_cache = array();
        }
        $settings_cache['system_mailmerge_on'] = true;
        sugar_cache_put('admin_settings_cache',$settings_cache);

        $this->assertEmpty(
            SugarTestReflection::callProtectedMethod($this->_lvd, 'buildMergeLink', array(array('foobar' => 'foobar')))
        );

        sugar_cache_clear('admin_settings_cache');
    }

    public function testBuildMergeLink()
    {
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->module_dir = 'foobarfoobar';

        $GLOBALS['current_user']->setPreference('mailmerge_on','on');

        $settings_cache = sugar_cache_retrieve('admin_settings_cache');
        if ( empty($settings_cache) ) {
            $settings_cache = array();
        }
        $settings_cache['system_mailmerge_on'] = true;
        sugar_cache_put('admin_settings_cache',$settings_cache);

        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildMergeLink', array(array('foobarfoobar' => 'foobarfoobar')));
        $this->assertContains("index.php?action=index&module=MailMerge&entire=true",$output);

        sugar_cache_clear('admin_settings_cache');
    }

    public function testBuildTargetLink()
    {
        $_POST['module'] = 'foobar';
        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->module_dir = 'foobarfoobar';

        $output = SugarTestReflection::callProtectedMethod($this->_lvd, 'buildTargetList');

        $this->assertContains("input.setAttribute ( 'name' , 'module' );			    input.setAttribute ( 'value' , 'foobarfoobar' );",$output);
        $this->assertContains("input.setAttribute ( 'name' , 'current_query_by_page' );			    input.setAttribute ( 'value', '".base64_encode(serialize($_REQUEST))."' );",$output);
    }

    public function testDisplayEndWhenNotShowingMassUpdateForm()
    {
        $this->_lvd->show_mass_update_form = false;

        $this->assertEmpty($this->_lvd->displayEnd());
    }

    public function testDisplayEndWhenShowingMassUpdateForm()
    {
        $this->_lvd->show_mass_update_form = true;
        $this->_lvd->mass = $this->createMock('MassUpdate');
        $this->_lvd->mass->expects($this->any())
                         ->method('getMassUpdateForm')
                         ->will($this->returnValue('foo'));
        $this->_lvd->mass->expects($this->any())
                         ->method('endMassUpdateForm')
                         ->will($this->returnValue('bar'));

        $this->assertEquals('foobar',$this->_lvd->displayEnd());
    }

    public function testGetMultiSelectData()
    {
        $this->_lvd->moduleString = 'foobar';

        $output = $this->_lvd->getMultiSelectData();

        $this->assertEquals($output, "<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n".
				"<textarea style='display: none' name='uid'></textarea>\n" .
				"<input type='hidden' name='select_entire_list' value='0'>\n".
				"<input type='hidden' name='foobar' value='0'>\n".
                "<input type='hidden' name='show_plus' value=''>\n",$output);
    }

    public function testGetMultiSelectDataWithRequestParameterUidSet()
    {
        $this->_lvd->moduleString = 'foobar';
        $_REQUEST['uid'] = '1234';

        $output = $this->_lvd->getMultiSelectData();

        $this->assertEquals("<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n".
				"<textarea style='display: none' name='uid'>1234</textarea>\n" .
				"<input type='hidden' name='select_entire_list' value='0'>\n".
				"<input type='hidden' name='foobar' value='0'>\n" .
                "<input type='hidden' name='show_plus' value=''>\n",$output);        
    }

    public function testGetMultiSelectDataWithRequestParameterSelectEntireListSet()
    {
        $this->_lvd->moduleString = 'foobar';
        $_REQUEST['select_entire_list'] = '1234';

        $output = $this->_lvd->getMultiSelectData();

        $this->assertEquals("<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n".
				"<textarea style='display: none' name='uid'></textarea>\n" .
				"<input type='hidden' name='select_entire_list' value='1234'>\n".
				"<input type='hidden' name='foobar' value='0'>\n" .
                "<input type='hidden' name='show_plus' value=''>\n",$output);        
    }

    public function testGetMultiSelectDataWithRequestParameterMassupdateSet()
    {
        $this->_lvd->moduleString = 'foobar';
        $_REQUEST['uid'] = '1234';
        $_REQUEST['select_entire_list'] = '5678';
        $_REQUEST['massupdate'] = 'true';

        $output = $this->_lvd->getMultiSelectData();

        $this->assertEquals("<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n".
				"<textarea style='display: none' name='uid'></textarea>\n" .
				"<input type='hidden' name='select_entire_list' value='0'>\n".
				"<input type='hidden' name='foobar' value='0'>\n".
                "<input type='hidden' name='show_plus' value=''>\n",$output);        
    }

    /**
     * Check setupHTMLFields
     *
     * @dataProvider setupHTMLFieldsDataProvider
     * @param $expected - Expected HTML field value
     * @param $field - Field name
     * @param $displayColumns - Display columns def containing the definition for HTML $field
     */
    public function testSetupHTMLFields($expected, $field, $displayColumns)
    {
        $this->_lvd->displayColumns = $displayColumns;

        $this->_lvd->seed = new stdClass;
        $this->_lvd->seed->custom_fields = new stdClass;
        $this->_lvd->seed->custom_fields->bean = new stdClass;
        $this->_lvd->seed->custom_fields->bean->test_c = $displayColumns[$field]['default'];

        $data = array(
            'data' => array(
                0 => array(),
            ),
        );

        $data = SugarTestReflection::callProtectedMethod($this->_lvd, 'setupHTMLFields', array($data));

        $this->assertEquals($expected, $data['data'][0][$field], 'HTML Field value not set');
    }

    public static function setupHTMLFieldsDataProvider()
    {
        return array(
            array(
                '<p>test</p>',
                'test_c',
                array(
                    'test_c' => array(
                        'type' => 'html',
                        'default' => '<p>test</p>',
                    )
                ),
            ),
        );
    }

    /**
     * bug 50645 Blank value for URL custom field in DetailView and subpanel
     * @dataProvider defaultSeedDefValuesProvider
     */
    public function testDefaultSeedDefValues($expected, $displayColumns, $fieldDefs)
    {
        $this->_lvd->displayColumns = $displayColumns;
        $this->_lvd->lvd = new stdClass;
        $this->_lvd->lvd->seed = new stdClass;
        $this->_lvd->lvd->seed->field_defs = $fieldDefs;
        SugarTestReflection::callProtectedMethod($this->_lvd, 'fillDisplayColumnsWithVardefs');
        foreach ($this->_lvd->displayColumns as $columnName => $def) {
            $seedName = strtolower($columnName);
            $seedDef = $this->_lvd->lvd->seed->field_defs[$seedName];
            $this->assertEquals($expected, $seedDef['default'] === $def['default']);
        }
    }

    public static function defaultSeedDefValuesProvider()
    {
        return array(
            array(
                true,
                array(array(
                    'default' => true,
                    'label'   => 'LBL_TEST_TEST_KEY'
                )),
                array(array(
                    'default' => 'test/url/pattern/{id}',
                    'label'   => 'LBL_TEST_TEST_KEY'
                ))
            ),
            array(
                false,
                array(array(
                    'default' => false,
                    'label'   => 'LBL_TEST_TEST_KEY'
                )),
                array(array(
                    'default' => 'test/url/pattern/{id}',
                    'label'   => 'LBL_TEST_TEST_KEY'
                ))
            ),
            array(
                false,
                array(array(
                    'default' => false,
                    'label'   => 'LBL_TEST_TEST_KEY'
                )),
                array(array(
                    'default' => null,
                    'label'   => 'LBL_TEST_TEST_KEY'
                ))
            ),
        );
    }
}
