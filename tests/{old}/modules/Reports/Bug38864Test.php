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
require_once('modules/Reports/config.php');

class Bug38864Test extends Sugar_PHPUnit_Framework_TestCase
{
	protected $modListHeader = null;

	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->modListHeader = query_module_access_list($GLOBALS['current_user']);

        sugar_mkdir("custom/modules/Reports/metadata/",null,true);

        SugarAutoLoader::put(
            "custom/modules/Reports/metadata/reportmodulesdefs.php",
            "<?php
\$additionalModules[] = 'ProspectLists';
\$exemptModules[] = 'Accounts';"
            );

	}

	public function tearDown()
	{
	    SugarAutoLoader::unlink("custom/modules/Reports/metadata/reportmodulesdefs.php");
	    unset($GLOBALS['current_user']);
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}

	public function testCustomReportmoduledefsExemptModulesIsParsed()
	{
	    $modules = getAllowedReportModules($this->modListHeader,true);

	    $this->assertArrayNotHasKey('Accounts',$modules);

	    return $modules;
	}

	/**
     * @depends testCustomReportmoduledefsExemptModulesIsParsed
     */
	public function testCustomReportmoduledefsAdditionalModulesIsParsed($modules)
	{
	    $this->assertArrayHasKey('ProspectLists',$modules);
	}
}
