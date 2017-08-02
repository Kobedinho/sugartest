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



require_once 'ModuleInstall/PackageManager/PackageManager.php';


class Bug44896Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (is_dir(dirname(Bug44896PackageManger::$location))) {
            rmdir_recursive(dirname(Bug44896PackageManger::$location));
        }
        sugar_mkdir(dirname(Bug44896PackageManger::$location));
        if (is_dir(Bug44896PackageManger::$location))
        {
            rmdir_recursive(Bug44896PackageManger::$location);
        }
        sugar_mkdir(Bug44896PackageManger::$location);

        $manage = new Bug44896PackageManger();
        $manage->createTempModule();
    }

    public function tearDown()
    {
        unlink(Bug44896PackageManger::$manifest_location);
        if (is_dir(dirname(Bug44896PackageManger::$location))) {
            rmdir_recursive(Bug44896PackageManger::$location);
            rmdir_recursive(dirname(Bug44896PackageManger::$location));
        }
    }

    public function testCheckedArrayKey()
    {
        $package = new PackageManager();
        $returnJson = $package->getPackagesInStaging('module');
        foreach ($returnJson as $module) {
            $this->assertArrayHasKey('unFile', $module, 'Key "unFile" is missing in return array');
        }
    }

}

class Bug44896PackageManger
{
	static $manifest_location = "upload://upgrades/module/Bug44896-manifest.php";
    static $zip_location = "upload://upgrades/module/Bug44896.zip";
    static $location = "upload://upgrades/module/";

	public function __construct()
    {
	   $this->manifest_content = <<<EOQ
<?php
\$manifest = array (
         'acceptable_sugar_versions' =>
          array (
            '6.4.0'
          ),
          'acceptable_sugar_flavors' =>
          array(
            'ENT'
          ),
          'readme'=>'',
          'key'=>'tf1',
          'author' => '',
          'description' => '',
          'icon' => '',
          'is_uninstallable' => false,
          'name' => 'Bug44896',
          'published_date' => '2010-10-20 22:10:01',
          'type' => 'module',
          'version' => '1287612601',
          'remove_tables' => 'prompt',
          );
\$installdefs = array (
  'id' => 'asdfqq',
  'copy' =>
  array (
     0 => array (
      'from' => '<basepath>/Extension/modules/Cases/Ext/Vardefs/dummy_extension2.php',
      'to' => 'custom/Extension/modules/Cases/Ext/Vardefs/dummy_extension2.php',
    ),
  ),
);

EOQ;
	}

	public function createTempModule()
    {
	   if (!is_file(self::$manifest_location))
       {
            file_put_contents(self::$manifest_location, $this->manifest_content);
            zip_files_list(self::$zip_location, array(self::$manifest_location));
       }
	}
}