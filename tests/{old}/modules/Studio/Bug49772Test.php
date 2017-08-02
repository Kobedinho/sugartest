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


/**
 * Bug #49772
 *
 * [IBM RTC 3001] XSS - Administration, Rename Modules, Singular Label
 * @ticket 49772
 * @author arymarchik@sugarcrm.com
 */
class Bug49772Test extends Sugar_PHPUnit_Framework_TestCase
{


    private $_old_label = '';
    private $_test_label = 'LBL_ACCOUNT_NAME';
    private $_test_module = 'Contacts';
    private $_lang = 'en_us';


    /**
     * Generating new label with HTML tags
     * @group 43069
     */
    public function testLabelSaving()
    {
        $mod_strings = return_module_language($this->_lang, $this->_test_module);
        $this->_old_label = $mod_strings[$this->_test_label];
        $pref = '<img alt="<script>" src="www.test.com/img.png" ="alert(7001)" width="1" height="1"/>';
        $prepared_pref = to_html(strip_tags(from_html($pref)));
        $new_label = $prepared_pref . ' ' . $this->_old_label;

        // save the new label to the language file
        ParserLabel::addLabels($this->_lang, array($this->_test_label => $new_label), $this->_test_module);

        // read the language file to get the new value
        include "custom/modules/{$this->_test_module}/Ext/Language/{$this->_lang}.lang.ext.php";

        $this->assertEquals($new_label, $mod_strings[$this->_test_label]);
        $this->assertNotEquals($pref . ' ' . $this->_old_label, $mod_strings[$this->_test_label]);

    }

    public function tearDown()
    {
        ParserLabel::addLabels($this->_lang, array($this->_test_label=>$this->_old_label), $this->_test_module);
    }
}
