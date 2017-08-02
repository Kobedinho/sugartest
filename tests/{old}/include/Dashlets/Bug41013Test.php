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
 * @ticket 41013
 */
class Bug41013Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_moduleName;

    public function setup()
    {
        $this->_moduleName = 'TestModuleForDashletLoadLanguageTest'.mt_rand();

        sugar_mkdir("custom/modules/{$this->_moduleName}/metadata/",null,true);
        sugar_file_put_contents("custom/modules/{$this->_moduleName}/metadata/dashletviewdefs.php",
            '<?php $dashletData[\''.$this->_moduleName.'Dashlet\'][\'searchFields\'] = array(); $dashletData[\''.$this->_moduleName.'Dashlet\'][\'columns\'] = array(\'Foo\'); ?>');
        SugarAutoLoader::addToMap("custom/modules/{$this->_moduleName}/metadata/dashletviewdefs.php", false);
    }

    public function tearDown()
    {
        if ( is_dir("custom/modules/{$this->_moduleName}") ) {
            rmdir_recursive("custom/modules/{$this->_moduleName}");
            SugarAutoLoader::delFromMap("custom/modules/{$this->_moduleName}", false);
        }

        unset($GLOBALS['dashletStrings']);
    }

    public function testCanLoadCustomMetadataTwiceInARow()
    {
        $dashlet = new DashletGenericMock();
        $dashlet->seedBean->module_dir = $this->_moduleName;

        $dashlet->loadCustomMetadata();

        $this->assertEquals(array('Foo'),$dashlet->columns);

        $dashlet->columns = array();

        $dashlet->loadCustomMetadata();

        $this->assertEquals(array('Foo'),$dashlet->columns);
    }
}

class DashletGenericMock extends DashletGeneric
{
    public function __construct()
    {
        $this->seedBean = new stdClass();
    }

    public function loadCustomMetadata()
    {
        parent::loadCustomMetadata();
    }
}
