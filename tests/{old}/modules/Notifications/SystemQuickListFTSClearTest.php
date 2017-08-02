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

class SystemQuickListFTSClearTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testFTSFlagRemoval() {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $GLOBALS['current_user']->is_admin = 1;
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('info', 'fts_index_done', 1);

        $cfg = new Configurator();
        $cfg->config['fts_disable_notification'] = true;
        $cfg->handleOverride();
        

        $vsql = new ViewSystemQuicklistMock();
        $vsql->clear();

        $cfg->loadConfig();
        $this->assertFalse($cfg->config['fts_disable_notification'], "FTS Disabled Notification is not false, it was: " . var_export($cfg->config['fts_disable_notification'], true));
        $settings = $admin->retrieveSettings();
        $this->assertEmpty($settings->settings['info_fts_index_done'], "FTS Index Done Flag not cleared, it was: " . var_export($settings->settings['info_fts_index_done'], true));

    }
}

class ViewSystemQuicklistMock extends ViewSystemQuicklist {
    public function clear() {
        return $this->clearFTSFlags();
    }
}
