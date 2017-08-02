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


class FetchByQueryCallsProcessRecordTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $hasCustomAccountLogicHookFile = false;
    var $accountsHookFile = 'custom/modules/Accounts/logic_hooks.php';
    var $accountsLogicHookFile = 'custom/modules/Accounts/checkProcess.php';

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        //Setup mock logic hook files
        if(file_exists($this->accountsHookFile))
        {
            $this->hasCustomAccountLogicHookFile = true;
            copy($this->accountsHookFile, $this->accountsHookFile.'.bak');
        }
        $hookArrayCont = '<?php
        $hook_version = 1;
        $hook_array = array();
        $hook_array[\'process_record\'] = array();
        $hook_array[\'process_record\'][] = array(1,\'test\',\'custom/modules/Accounts/checkProcess.php\',\'checkProcess\',\'account_check\',);
        ?>';
        file_put_contents($this->accountsHookFile, $hookArrayCont);
        SugarAutoLoader::addToMap($this->accountsHookFile, false);

        //now  write out the script that the logichook executes.  This will keep track of times called
        $fileCont = '<?php class checkProcess {
            function account_check($bean, $event, $arguments){
                global $accountHookRunCount;
                if($event =="process_record" && $bean->module_dir == "Accounts") {
                    $accountHookRunCount++;
                }}}?>';
        file_put_contents($this->accountsLogicHookFile, $fileCont);
        SugarAutoLoader::addToMap($this->accountsLogicHookFile, false);

        LogicHook::refreshHooks();

        //finally, lets make sure there is at least one account to fetch
        SugarTestAccountUtilities::createAccount();

    }

    public function tearDown()
    {
        //Remove the custom logic hook files
        if($this->hasCustomAccountLogicHookFile && file_exists($this->accountsHookFile.'.bak'))
        {
            copy($this->accountsHookFile.'.bak', $this->accountsHookFile);
            unlink($this->accountsHookFile.'.bak');
        } else if(file_exists($this->accountsHookFile)) {
            SugarAutoLoader::unlink($this->accountsHookFile);
        }
        SugarAutoLoader::unlink($this->accountsLogicHookFile);
        unset($GLOBALS['accountHookRunCount']);
        unset($GLOBALS['logic_hook']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testFetchByQueryCallsProcessRecordLogicHooks()
    {
        global $accountHookRunCount;
        $accountHookRunCount = 0;

        //fetch the bean of the module to query
        $bean = BeanFactory::newBean('Accounts');

        //create query for accounts
        $sugarQuery = new SugarQuery();
        $sugarQuery->select(array('id', 'name'));
        $sugarQuery->from($bean);
        //run fetch from Query to test that logic hook event gets fired
        $bean->fetchFromQuery($sugarQuery);
        $this->assertGreaterThan(0, $accountHookRunCount, 'logic hook did not update run count');
    }

}?>
