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


class Bug67650Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $customIncludeDir = 'custom/data';
    protected $customIncludeFile = 'SugarBeanApiHelper.php';

    public function setUp()
    {
        // create a custom include file
        $customIncludeFileContent = <<<EOQ
<?php
class CustomSugarBeanApiHelper
{
}
EOQ;
        if (!file_exists($this->customIncludeDir)) {
            sugar_mkdir($this->customIncludeDir, 0777, true);
        }
          
        SugarAutoLoader::put($this->customIncludeDir . '/' . $this->customIncludeFile, $customIncludeFileContent);
        // clean cache
        unset(ApiHelper::$moduleHelpers['Campaigns']);
    }

    public function tearDown()
    {
        // clean cache
        unset(ApiHelper::$moduleHelpers['Campaigns']);

        // remove the custom file
        if (file_exists($this->customIncludeDir . '/' . $this->customIncludeFile)) {
            SugarAutoLoader::unlink($this->customIncludeDir . '/' . $this->customIncludeFile);
        }
    }

    public function testFindCustomHelper()
    {
        $api = new RestService();
        $bean = BeanFactory::newBean('Campaigns');
        $helper = ApiHelper::getHelper($api,$bean);
        $this->assertEquals('CustomSugarBeanApiHelper',get_class($helper));
    }
}
