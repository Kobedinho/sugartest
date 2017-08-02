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
 * MetaDataFilesTest
 *
 * This test checks to see that the correct files are loaded from the clients/ directories
 *
 *
 */


class MetaDataFilesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->createdFiles = array();
        $this->createdDirs = array();
        SugarTestHelper::setUp('app_list_strings');
    }

    public function tearDown()
    {
        foreach ( $this->createdFiles as $file ) {
            SugarAutoLoader::unlink($file);
        }
        foreach ( $this->createdDirs as $dir ) {
            rmdir_recursive($dir);
            SugarAutoLoader::delFromMap($dir, false);
        }

        SugarAutoLoader::saveMap();
    }

    public $fileFullPaths = array(
        'Accountsmobilelistviewbase'   => 'modules/Accounts/clients/mobile/views/list/list.php',
        'Accountsmobilelistviewcustom' => 'custom/modules/Accounts/clients/mobile/views/list/list.php',
        'Bugsportalrecordviewworking'    => 'custom/working/modules/Bugs/clients/portal/views/record/record.php',
        'Casesportalrecordviewhistory' => 'custom/history/modules/Cases/clients/portal/views/record/record.php',
        'Bugsmobilesearchviewbase'     => 'modules/Bugs/clients/mobile/views/search/search.php',
        'Callsbasesearchviewbase'      => 'modules/Calls/clients/base/views/search/search.php',
    );

    public $deployedFileNames = array(
        'Accountslistviewbase' => 'modules/Accounts/metadata/listviewdefs.php',
        'Leadswirelesseditviewcustommobile' => 'custom/modules/Leads/clients/mobile/views/edit/edit.php',
        'Notesportalrecordviewworkingportal' => 'custom/working/modules/Notes/clients/portal/views/record/record.php',
        'Quotesadvanced_searchhistory' => 'custom/history/modules/Quotes/metadata/searchdefs.php',
        'Meetingsbasic_searchbase'  => 'modules/Meetings/metadata/searchdefs.php',
        'Bugswireless_advanced_searchbasemobile' => 'modules/Bugs/clients/mobile/views/search/search.php',
    );

    public $undeployedFileNames = array(
        'Accountslistviewbase' => 'custom/modulebuilder/packages/LZWYZ/modules/Accounts/metadata/listviewdefs.php',
        'Leadswirelesseditviewcustommobile' => 'custom/modulebuilder/packages/LZWYZ/modules/Leads/clients/mobile/views/edit/edit.php',
        'Notesportalrecordviewworkingportal' => 'custom/modulebuilder/packages/LZWYZ/modules/Notes/clients/portal/views/record/record.php',
        'Quotesadvanced_searchhistory' => 'custom/working/modulebuilder/packages/LZWYZ/modules/Quotes/metadata/searchdefs.php',
    );

    /**
     * @dataProvider MetaDataFileFullPathProvider
     * @param string $module
     * @param string $viewtype
     * @param string $location
     * @param string $client
     * @param string $component
     */
    public function testMetaDataFileFullPath($module, $viewtype, $location, $client, $component) {
        $filepath = MetaDataFiles::getModuleFileName($module, $viewtype, $location, $client, $component);
        $known = $this->fileFullPaths[$module.$client.$viewtype.$component.$location];

        $this->assertEquals($known, $filepath, 'Filepath mismatch: ' . $filepath . ' <-> ' . $known);
    }

    /**
     * @dataProvider DeployedFileNameProvider
     * @param string $view
     * @param string $module
     * @param string $location
     * @param string $client
     */
    public function testDeployedFileName($view, $module, $location, $client) {
        $name = MetaDataFiles::getDeployedFileName($view, $module, $location, $client);
        $known = $this->deployedFileNames[$module.$view.$location.$client];
        $this->assertEquals($known, $name, 'Filename mismatch: ' . $name . ' <-> ' . $known);
    }

    /**
     * @dataProvider UndeployedFileNameProvider
     * @param string $view
     * @param string $module
     * @param string $package
     * @param string $location
     * @param string $client
     */
    public function testUndeployedFileName($view, $module, $package, $location, $client) {
        $name = MetaDataFiles::getUndeployedFileName($view, $module, $package, $location, $client);
        $known = $this->undeployedFileNames[$module.$view.$location.$client];
        $this->assertEquals($known, $name, 'Filename mismatch: ' . $name . ' <-> ' . $known);
    }

    public function MetaDataFileFullPathProvider() {
        return array(
            array('Accounts', 'list', MB_BASEMETADATALOCATION, MB_WIRELESS, 'view'),
            array('Accounts', 'list', MB_CUSTOMMETADATALOCATION, MB_WIRELESS, 'view'),
            array('Bugs', 'search', MB_BASEMETADATALOCATION, MB_WIRELESS, 'view'),
            array('Bugs', 'record', MB_WORKINGMETADATALOCATION, MB_PORTAL, 'view'),
            array('Cases', 'record', MB_HISTORYMETADATALOCATION, MB_PORTAL, 'view'),
            array('Calls', 'search', MB_BASEMETADATALOCATION, 'base', 'view'),
        );
    }

    public function DeployedFileNameProvider() {
        return array(
            array(MB_LISTVIEW, 'Accounts', MB_BASEMETADATALOCATION, ''),
            array(MB_WIRELESSEDITVIEW, 'Leads', MB_CUSTOMMETADATALOCATION, MB_WIRELESS),
            array(MB_PORTALRECORDVIEW, 'Notes', MB_WORKINGMETADATALOCATION, MB_PORTAL),
            array(MB_ADVANCEDSEARCH, 'Quotes', MB_HISTORYMETADATALOCATION, ''),
            array(MB_BASICSEARCH, 'Meetings', MB_BASEMETADATALOCATION, ''),
            array(MB_WIRELESSADVANCEDSEARCH, 'Bugs', MB_BASEMETADATALOCATION, MB_WIRELESS),
        );
    }

    public function UndeployedFileNameProvider() {
        return array(
            array(MB_LISTVIEW, 'Accounts', 'LZWYZ', MB_BASEMETADATALOCATION, ''),
            array(MB_WIRELESSEDITVIEW, 'Leads', 'LZWYZ', MB_CUSTOMMETADATALOCATION, MB_WIRELESS),
            array(MB_PORTALRECORDVIEW, 'Notes', 'LZWYZ', MB_WORKINGMETADATALOCATION, MB_PORTAL),
            array(MB_ADVANCEDSEARCH, 'Quotes', 'LZWYZ', MB_HISTORYMETADATALOCATION, ''),
        );
    }

    public function testLoadingFieldTemplate()
    {
        $this->markTestIncomplete("This test does not properly ensure that the clients/base/fields/fo directory is created");
        $this->createdDirs[] = 'clients/base/fields/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[0]);

        $this->createdFiles[] = 'clients/base/fields/fo/rizzle.hbs';
        SugarAutoLoader::put($this->createdFiles[0],'FO RIZZLE (base)');

        $fileList = MetaDataFiles::getClientFiles(array('base'),'field');

        $this->assertArrayHasKey($this->createdFiles[0],$fileList,"The file list should contain fo rizzle.");

        $fileContents = MetaDataFiles::getClientFileContents(array('fo/rizzle.hbs'=>$fileList[$this->createdFiles[0]]),'field');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbs was a template");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section");
        $this->assertEquals('FO RIZZLE (base)',$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the contents of the rizzle template");
    }

    public function testLoadingCustomFieldTemplate()
    {
        $this->markTestIncomplete("This test does not properly ensure that the clients/base/fields/fo directory is created");
        $this->createdDirs[] = 'clients/base/fields/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[0]);
        $this->createdDirs[] = 'custom/clients/base/fields/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[1]);

        // These have to be in this order, to simulate how they return from getClientFiles
        $this->createdFiles[] = 'custom/clients/base/fields/fo/rizzle.hbs';
        SugarAutoLoader::put($this->createdFiles[0],'FO RIZZLE (custom)');
        $this->createdFiles[] = 'custom/clients/base/fields/fo/drizzle.hbs';
        SugarAutoLoader::put($this->createdFiles[1],'FO DRIZZLE (custom)');
        $this->createdFiles[] = 'clients/base/fields/fo/rizzle.hbs';
        SugarAutoLoader::put($this->createdFiles[2],'FO RIZZLE (base)');
        $this->createdFiles[] = 'clients/base/fields/fo/fizzle.hbs';
        SugarAutoLoader::put($this->createdFiles[3],'FO FIZZLE (base)');

        $fileList = MetaDataFiles::getClientFiles(array('base'),'field');

        $myFileList = array();
        foreach ( $this->createdFiles as $fileName ) {
            $this->assertArrayHasKey($fileName,$fileList,"The file list should contain: ". $fileName);
            $myFileList[$fileName] = $fileList[$fileName];
        }

        $fileContents = MetaDataFiles::getClientFileContents($myFileList,'field');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbs was a template");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section");
        $this->assertArrayHasKey('fizzle',$fileContents['fo']['templates'],"Didn't correctly put fizzle in the template section");
        $this->assertArrayHasKey('drizzle',$fileContents['fo']['templates'],"Didn't correctly put drizzle in the template section");
        $this->assertEquals('FO RIZZLE (custom)',$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the contents of the rizzle template");
        $this->assertEquals('FO FIZZLE (base)',$fileContents['fo']['templates']['fizzle'],"Did not correctly read in the contents of the fizzle template");
        $this->assertEquals('FO DRIZZLE (custom)',$fileContents['fo']['templates']['drizzle'],"Did not correctly read in the contents of the drizzle template");
    }

    public function testLoadingFieldController()
    {
        $this->markTestIncomplete("This test does not properly ensure that the clients/base/fields/fo directory is created");
        $this->createdDirs[] = 'clients/base/fields/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[0]);
        $this->createdDirs[] = 'clients/mobile/fields/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[1]);

        $this->createdFiles[] = 'clients/base/fields/fo/fo.js';
        $controllerContentsBase = 'console.log("fo"); // (base/controller)';
        SugarAutoLoader::put($this->createdFiles[0],$controllerContentsBase);

        $this->createdFiles[] = 'clients/base/fields/fo/rizzle.hbs';
        $templateContentsBase = 'FO RIZZLE (base/template)';
        SugarAutoLoader::put($this->createdFiles[1],$templateContentsBase);


        $this->createdFiles[] = 'clients/mobile/fields/fo/fo.js';
        $controllerContentsMobile = 'console.log("fo"); // (mobile/controller)';
        SugarAutoLoader::put($this->createdFiles[2],$controllerContentsMobile);

        $this->createdFiles[] = 'clients/mobile/fields/fo/rizzle.hbs';
        $templateContentsMobile = 'FO RIZZLE (mobile/template)';
        SugarAutoLoader::put($this->createdFiles[3],$templateContentsMobile);

        $fileList = MetaDataFiles::getClientFiles(array('mobile','base'),'field');

        foreach ( $this->createdFiles as $fileName) {
            $this->assertArrayHasKey($fileName,$fileList,"The file list should contain $fileName");
        }

        $fileContents = MetaDataFiles::getClientFileContents($fileList,'field');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbs was a template");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section");
        $this->assertEquals($templateContentsMobile,$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the mobile contents of the rizzle template");

        $this->assertArrayHasKey('controller',$fileContents['fo'],"Didn't figure out that fo.js was a controller");
        $this->assertArrayHasKey('mobile',$fileContents['fo']['controller'],"Didn't find the mobile controller");
        $this->assertArrayHasKey('base',$fileContents['fo']['controller'],"Didn't find the base controller");
        $this->assertEquals($controllerContentsBase,$fileContents['fo']['controller']['base'],"Didn't correctly place the fo (base) controller in the base section");
        $this->assertEquals($controllerContentsMobile,$fileContents['fo']['controller']['mobile'],"Didn't correctly place the fo (mobile) controller in the mobile section");


        $fileList = MetaDataFiles::getClientFiles(array('base'),'field');

        $this->assertArrayHasKey($this->createdFiles[0],$fileList,"The file list should contain ".$this->createdFiles[0]);
        $this->assertArrayHasKey($this->createdFiles[1],$fileList,"The file list should contain ".$this->createdFiles[1]);
        $this->assertArrayNotHasKey($this->createdFiles[2],$fileList,"The file list should NOT contain ".$this->createdFiles[2]);
        $this->assertArrayNotHasKey($this->createdFiles[3],$fileList,"The file list should NOT contain ".$this->createdFiles[3]);

        $fileContents = MetaDataFiles::getClientFileContents($fileList,'field');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section. 2");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbs was a template 2");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section 2");
        $this->assertEquals($templateContentsBase,$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the base contents of the rizzle template");

        $this->assertArrayHasKey('controller',$fileContents['fo'],"Didn't figure out that fo.js was a controller 2");
        $this->assertArrayNotHasKey('mobile',$fileContents['fo']['controller'],"Found the mobile controller when it shouldn't have");
        $this->assertArrayHasKey('base',$fileContents['fo']['controller'],"Didn't find the base controller");
        $this->assertEquals($controllerContentsBase,$fileContents['fo']['controller']['base'],"Didn't correctly place the fo (base) controller in the base section");

    }

    public function testLoadingViewEverything()
    {
        $this->markTestIncomplete("This test does not properly ensure that the clients/base/fields/fo directory is created");
        $this->createdDirs[] = 'modules/Accounts/clients/base/views/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[0]);
        $this->createdDirs[] = 'modules/Accounts/clients/mobile/views/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[1]);

        $this->createdFiles[] = 'modules/Accounts/clients/base/views/fo/fo.js';
        $baseController = 'console.log("fo"); // (base/controller)';
        SugarAutoLoader::put($this->createdFiles[0],$baseController);

        $this->createdFiles[] = 'modules/Accounts/clients/base/views/fo/rizzle.hbs';
        $baseTemplate = 'FO RIZZLE (base)';
        SugarAutoLoader::put($this->createdFiles[1],$baseTemplate);

        $this->createdFiles[] = 'modules/Accounts/clients/base/views/fo/fo.php';
        $baseMetaContents = '<?php'."\n".'$viewdefs["Accounts"]["base"]["view"]["fo"] = array("erma"=>"base");';
        SugarAutoLoader::put($this->createdFiles[2],$baseMetaContents);

        $this->createdFiles[] = 'modules/Accounts/clients/mobile/views/fo/fo.js';
        $mobileController = 'console.log("fo"); // (mobile/controller)';
        SugarAutoLoader::put($this->createdFiles[3],$mobileController);

        $this->createdFiles[] = 'modules/Accounts/clients/mobile/views/fo/rizzle.hbs';
        $mobileTemplate = 'FO RIZZLE (mobile)';
        SugarAutoLoader::put($this->createdFiles[4],$mobileTemplate);

        $this->createdFiles[] = 'modules/Accounts/clients/mobile/views/fo/fo.php';
        $mobileMetaContents = '<?php'."\n".'$viewdefs["Accounts"]["mobile"]["view"]["fo"] = array("erma"=>"mobile");';
        SugarAutoLoader::put($this->createdFiles[5],$mobileMetaContents);

        $this->createdFiles[] = 'modules/ActivityStream/Activity/clients/base/datas/model/model.js';
        $baseModelContents = '({})';
        SugarAutoLoader::put($this->createdFiles[6],$baseModelContents);

        $fileList = MetaDataFiles::getClientFiles(array('mobile','base'),'view','Accounts');

        foreach ( $this->createdFiles as $fileName) {
            $this->assertArrayHasKey($fileName,$fileList,"The file list should contain $fileName");
        }

        $fileContents = MetaDataFiles::getClientFileContents($fileList,'view','Accounts');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbs was a template");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section");
        $this->assertEquals($mobileTemplate,$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the mobile contents of the rizzle template");

        $this->assertArrayHasKey('controller',$fileContents['fo'],"Didn't figure out that fo.js was a controller");
        $this->assertArrayHasKey('mobile',$fileContents['fo']['controller'],"Didn't find the mobile controller");
        $this->assertArrayHasKey('base',$fileContents['fo']['controller'],"Didn't find the base controller");
        $this->assertEquals($baseController,$fileContents['fo']['controller']['base'],"Didn't correctly place the fo (base) controller in the base section");
        $this->assertEquals($mobileController,$fileContents['fo']['controller']['mobile'],"Didn't correctly place the fo (mobile) controller in the mobile section");

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('meta',$fileContents['fo'],"Didn't find the metadata for fo");
        $this->assertArrayHasKey('erma',$fileContents['fo']['meta'],"Didn't correctly put erma in the metadata section");
        $this->assertEquals('mobile',$fileContents['fo']['meta']['erma'],"Did not correctly read in the mobile metadata");

        $fileList = MetaDataFiles::getClientFiles(array('base'),'view','Accounts');

        $this->assertArrayHasKey($this->createdFiles[0],$fileList,"2 The file list should contain ".$this->createdFiles[0]);
        $this->assertArrayHasKey($this->createdFiles[1],$fileList,"2 The file list should contain ".$this->createdFiles[1]);
        $this->assertArrayHasKey($this->createdFiles[2],$fileList,"2 The file list should contain ".$this->createdFiles[2]);
        $this->assertArrayNotHasKey($this->createdFiles[3],$fileList,"2 The file list should NOT contain ".$this->createdFiles[3]);
        $this->assertArrayNotHasKey($this->createdFiles[4],$fileList,"2 The file list should NOT contain ".$this->createdFiles[4]);
        $this->assertArrayNotHasKey($this->createdFiles[5],$fileList,"2 The file list should NOT contain ".$this->createdFiles[5]);

        $fileContents = MetaDataFiles::getClientFileContents($fileList,'view','Accounts');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section. 2");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbs was a template 2");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section 2");
        $this->assertEquals($baseTemplate,$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the base contents of the rizzle template");

        $this->assertArrayHasKey('controller',$fileContents['fo'],"Didn't figure out that fo.js was a controller 2");
        $this->assertArrayNotHasKey('mobile',$fileContents['fo']['controller'],"Found the mobile controller when it shouldn't have");
        $this->assertArrayHasKey('base',$fileContents['fo']['controller'],"Didn't find the base controller");
        $this->assertEquals($baseController,$fileContents['fo']['controller']['base'],"Didn't correctly place the fo (base) controller in the base section");

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section. 2");
        $this->assertArrayHasKey('meta',$fileContents['fo'],"Didn't find the metadata for fo. 2");
        $this->assertArrayHasKey('erma',$fileContents['fo']['meta'],"Didn't correctly put erma in the metadata section. 2");
        $this->assertEquals('base',$fileContents['fo']['meta']['erma'],"Did not correctly read in the base metadata");

        $fileList = MetaDataFiles::getClientFiles(array('base'),'data','Activity');
        $this->assertArrayHasKey($this->createdFiles[6],$fileList,"3 The function getClientFiles should get client submodule files ".$this->createdFiles[6]);
    }


    public function testLoadingExtFiles() {
        //Start with base app extensions
        $baseFilePath = 'custom/clients/base/views/fo/fo.php';
        $this->createdFiles[] = $baseFilePath;
        $this->createdDirs[] = dirname($baseFilePath);
        SugarAutoLoader::ensureDir(dirname($baseFilePath));

        $baseMetaContents = '<?php' . "\n" . '$viewdefs["base"]["view"]["fo"] = array("erma"=>"base");';
        SugarAutoLoader::put($baseFilePath, $baseMetaContents);


        $extFilePath = 'custom/application/Ext/clients/base/views/fo/fo.ext.php';
        $this->createdFiles[] = $extFilePath;
        $this->createdDirs[] = dirname($extFilePath);
        SugarAutoLoader::ensureDir(dirname($extFilePath));
        $baseExtMetaContents = '<?php' . "\n" . '$viewdefs["base"]["view"]["fo"]["ext"] = "baseByExt";';
        SugarAutoLoader::put($extFilePath, $baseExtMetaContents);

        $baseFileList = MetaDataFiles::getClientFiles(array('base'),'view');

        $this->assertArrayHasKey($baseFilePath, $baseFileList, "Didn't find the fo section.");
        $this->assertArrayHasKey($extFilePath, $baseFileList, "Didn't find the fo extension");

        $results  = MetaDataFiles::getClientFileContents($baseFileList, "view");

        $this->assertArrayHasKey("fo", $results, "Didn't load the fo meta.");
        $this->assertArrayHasKey("ext", $results['fo']['meta'], "Didn't load the fo meta extension correctly");
        $this->assertArrayHasKey("erma", $results['fo']['meta'], "The metadata extension was not merged with the base meta");

    }


    public function testLoadingModuleExtFiles() {
        //Check module specific extensions

        $baseFilePath = 'modules/Accounts/clients/base/views/fo/fo.php';
        $this->createdFiles[] = $baseFilePath;
        $this->createdDirs[] = dirname($baseFilePath);
        SugarAutoLoader::ensureDir(dirname($baseFilePath));
        $acctMetaContents = '<?php' . "\n" . '$viewdefs["Accounts"]["base"]["view"]["fo"] = array("erma"=>"baseAcct");';
        SugarAutoLoader::put($baseFilePath, $acctMetaContents);

        $extFilePath = 'custom/modules/Accounts/Ext/clients/base/views/fo/fo.ext.php';
        $this->createdFiles[] = $extFilePath;
        $this->createdDirs[] = dirname($extFilePath);
        SugarAutoLoader::ensureDir(dirname($extFilePath));
        $acctExtMetaContents = '<?php' . "\n" . '$viewdefs["Accounts"]["base"]["view"]["fo"]["ext"] = "baseAcctByExt";';
        SugarAutoLoader::put($extFilePath, $acctExtMetaContents);

        $accountFileList = MetaDataFiles::getClientFiles(array('base'),'view','Accounts');

        $this->assertArrayHasKey($baseFilePath, $accountFileList, "Didn't find the Accounts fo section.");
        $this->assertArrayHasKey($extFilePath, $accountFileList, "Didn't find the Accounts fo extension");

        $results  = MetaDataFiles::getClientFileContents($accountFileList, "view", "Accounts");

        $this->assertArrayHasKey("fo", $results, "Didn't load the Accounts fo meta.");
        $this->assertArrayHasKey("ext", $results['fo']['meta'], "Didn't load the Accounts fo meta extension correctly");
        $this->assertArrayHasKey("erma", $results['fo']['meta'], "The Accounts metadata extension was not merged with the base meta");
    }

    /**
     * Test merging the extension file to the global file.
     *
     * @return none
     */
    public function testMergeModuleExtFiles2Base()
    {

        //Load the base file
        $baseFilePath = 'clients/base/layouts/fo/fo.php';
        $this->createdFiles[] = $baseFilePath;
        $this->createdDirs[] = dirname($baseFilePath);
        SugarAutoLoader::ensureDir(dirname($baseFilePath));
        $baseMetaContents = '<?php'."\n".'$viewdefs["base"]["layout"]["fo"] = array("erma"=>"baseLayouts");';
        SugarAutoLoader::put($baseFilePath, $baseMetaContents);

        //Load the extension file
        $extFilePath = 'custom/modules/Cases/Ext/clients/base/layouts/fo/fo.ext.php';
        $this->createdFiles[] = $extFilePath;
        $this->createdDirs[] = dirname($extFilePath);
        SugarAutoLoader::ensureDir(dirname($extFilePath));
        $caseExtMetaContents = '<?php'."\n".'$viewdefs["Cases"]["base"]["layout"]["fo"]["ext"] = "baseCaseByExt";';
        SugarAutoLoader::put($extFilePath, $caseExtMetaContents);

        $caseFileList = MetaDataFiles::getClientFiles(array('base'), 'layout', 'Cases');
        $this->assertArrayHasKey($extFilePath, $caseFileList, "Didn't find the Cases fo extension");

        $results  = MetaDataFiles::getClientFileContents($caseFileList, "layout", "Cases");
        $this->assertArrayHasKey("fo", $results, "Didn't load the Cases fo meta.");
        $this->assertArrayHasKey("ext", $results['fo']['meta'], "Didn't load the Cases fo meta extension correctly");
        $this->assertArrayHasKey(
            "erma",
            $results['fo']['meta'],
            "The Cases metadata extension was not merged with the base meta"
        );
    }

    /**
     * Test merging the extension file to the template file.
     *
     * @return none
     */
    public function testMergeModuleExtFiles2Template()
    {

        //Load the template file
        $templateFilePath = 'include/SugarObjects/templates/basic/clients/base/views/fo/fo.php';
        $this->createdFiles[] = $templateFilePath;
        $this->createdDirs[] = dirname($templateFilePath);
        SugarAutoLoader::ensureDir(dirname($templateFilePath));
        $baseMetaContents = '<?php'."\n".'$module_name = "<module_name>";'."\n".
            '$viewdefs[$module_name]["base"]["view"]["fo"] = array("erma"=>"baseViews");';
        SugarAutoLoader::put($templateFilePath, $baseMetaContents);

        //Load the extension file
        $extFilePath = 'custom/modules/Cases/Ext/clients/base/views/fo/fo.ext.php';
        $this->createdFiles[] = $extFilePath;
        $this->createdDirs[] = dirname($extFilePath);
        SugarAutoLoader::ensureDir(dirname($extFilePath));
        $caseExtMetaContents = '<?php'."\n".'$viewdefs["Cases"]["base"]["view"]["fo"]["ext"] = "baseCaseByExt";';
        SugarAutoLoader::put($extFilePath, $caseExtMetaContents);

        $caseFileList = MetaDataFiles::getClientFiles(array('base'), 'view', 'Cases');
        $this->assertArrayHasKey($templateFilePath, $caseFileList, "Didn't find the template fo section.");
        $this->assertArrayHasKey($extFilePath, $caseFileList, "Didn't find the Cases fo extension");

        $results  = MetaDataFiles::getClientFileContents($caseFileList, "view", "Cases");
        $this->assertArrayHasKey("fo", $results, "Didn't load the Cases fo meta.");
        $this->assertArrayHasKey("ext", $results['fo']['meta'], "Didn't load the Cases fo meta extension correctly");
        $this->assertArrayHasKey(
            "erma",
            $results['fo']['meta'],
            "The Cases metadata extension was not merged with the base meta"
        );
    }

}
