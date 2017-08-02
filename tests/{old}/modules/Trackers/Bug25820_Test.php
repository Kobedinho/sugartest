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

require_once('tests/{old}/modules/Trackers/TrackerTestUtility.php');
require_once('tests/{old}/SugarTestLangPackCreator.php');

class Bug25820_Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_trackerReporter;

    public function setUp()
    {
        $this->_trackerReporter = new TrackerReporterBug25820Mock();
        SugarCache::$isCacheReset = false;
    }

    public function testGetTranslatedModuleNameInModuleList()
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('moduleList',array('Contacts'=>'cat'));
        $langpack->save();
        $this->assertEquals('cat',
            $this->_trackerReporter->getGetTranslatedModuleName('Contacts')
            );
    }

    public function testGetTranslatedModuleNameInModStrings()
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setModString('LBL_MODULE_NAME','stringname','Administration');
        $langpack->save();

        $this->assertEquals('stringname',
            $this->_trackerReporter->getGetTranslatedModuleName('Administration')
            );
    }

    public function testGetTranslatedModuleNameModuleBuilder()
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setModString('LBL_MODULEBUILDER','stringname','ModuleBuilder');
        $langpack->save();

        $this->assertEquals('stringname',
            $this->_trackerReporter->getGetTranslatedModuleName('ModuleBuilder')
            );
    }
}


class TrackerReporterBug25820Mock extends TrackerReporter
{
    public function getGetTranslatedModuleName(
        $moduleName
        )
    {
        return $this->_getTranslatedModuleName($moduleName);
    }
}
?>
