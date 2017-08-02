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
 * @ticket bug40216
 */
class Bug40216Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $moduleName;

    public function setUp()
    {
    	   $this->moduleName = 'TestModule'.mt_rand();

        sugar_mkdir("modules/{$this->moduleName}/metadata",null,true);
        sugar_mkdir("custom/modules/{$this->moduleName}/metadata",null,true);
    }

    public function tearDown()
    {
        rmdir_recursive("modules/{$this->moduleName}");
        rmdir_recursive("custom/modules/{$this->moduleName}");
        SugarAutoLoader::delFromMap("modules/{$this->moduleName}", false);
        SugarAutoLoader::delFromMap("custom/modules/{$this->moduleName}", false);
    }

    public function testAdditionalDetailsMetadataFileIsFound()
    {
    	   SugarAutoLoader::touch("modules/{$this->moduleName}/metadata/additionalDetails.php");

    	   $viewObject = new Bug40216Mock;

    	   $this->assertEquals(
    	       "modules/{$this->moduleName}/metadata/additionalDetails.php",
    	       $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
    	       );
    }

    public function testCustomAdditionalDetailsMetadataFileIsFound()
    {
    	   SugarAutoLoader::touch("custom/modules/{$this->moduleName}/metadata/additionalDetails.php");

    	   $viewObject = new Bug40216Mock;

    	   $this->assertEquals(
    	       "custom/modules/{$this->moduleName}/metadata/additionalDetails.php",
    	       $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
    	       );
    }

    public function testCustomAdditionalDetailsMetadataFileIsUsedBeforeNonCustomOne()
    {
    	   SugarAutoLoader::touch("modules/{$this->moduleName}/metadata/additionalDetails.php");
    	   SugarAutoLoader::touch("custom/modules/{$this->moduleName}/metadata/additionalDetails.php");

    	   $viewObject = new Bug40216Mock;

    	   $this->assertEquals(
    	       "custom/modules/{$this->moduleName}/metadata/additionalDetails.php",
    	       $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
    	       );
    }
}

class Bug40216Mock extends HomeViewAdditionaldetailsretrieve
{
    public function getAdditionalDetailsMetadataFile(
        $moduleName
        )
    {
        return parent::getAdditionalDetailsMetadataFile($moduleName);
    }
}
