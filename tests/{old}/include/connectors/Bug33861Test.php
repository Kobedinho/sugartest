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

require_once 'include/connectors/utils/ConnectorUtils.php';

class Bug33861Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $has_custom_connectors_file;
    public $has_custom_display_config_file;
    public $has_custom_accounts_detailviewdefs_file;
    public $has_custom_leads_detailviewdefs_file;
    public $has_custom_contacts_detailviewdefs_file;

    public function setUp()
    {
        $this->markTestIncomplete("Marked as skipped until we can resolve Hoovers nusoapclient issues.");

          return;

        if (file_exists('custom/modules/connectors/metadata/connectors.php')) {
            $this->has_custom_connectors_file = true;
            copy('custom/modules/connectors/metadata/connectors.php', 'custom/modules/connectors/metadata/connectors.php.bak');
            unlink('custom/modules/connectors/metadata/connectors.php');
        }

        if (file_exists('custom/modules/connectors/metadata/display_config.php')) {
            $this->has_custom_display_config_file = true;
            copy('custom/modules/connectors/metadata/display_config.php', 'custom/modules/connectors/metadata/display_config.php.bak');
            unlink('custom/modules/connectors/metadata/display_config.php');
        }

        if (file_exists('custom/modules/accounts/metadata/detailviewdefs.php')) {
            $this->has_custom_accounts_detailviewdefs_file = true;
            copy('custom/modules/accounts/metadata/detailviewdefs.php', 'custom/modules/accounts/metadata/detailviewdefs.php.bak');
            unlink('custom/modules/accounts/metadata/detailviewdefs.php');
        }

        if (file_exists('custom/modules/contactss/metadata/detailviewdefs.php')) {
            $this->has_custom_contacts_detailviewdefs_file = true;
            copy('custom/modules/contacts/metadata/detailviewdefs.php', 'custom/modules/contacts/metadata/detailviewdefs.php.bak');
            unlink('custom/modules/contacts/metadata/detailviewdefs.php');
        }

        if (file_exists('custom/modules/accounts/metadata/detailviewdefs.php')) {
            $this->has_custom_leads_detailviewdefs_file = true;
            copy('custom/modules/leads/metadata/detailviewdefs.php', 'custom/modules/leads/metadata/detailviewdefs.php.bak');
            unlink('custom/modules/leads/metadata/detailviewdefs.php');
        }

        if (file_exists('custom/modules/Connectors/metadata/mergeviewdefs.php')) {
            unlink('custom/modules/Connectors/metadata/mergeviewdefs.php');
        }
    }

    public function tearDown()
    {
        if ($this->has_custom_connectors_file) {
            copy('custom/modules/connectors/metadata/connectors.php.bak', 'custom/modules/connectors/metadata/connectors.php');
            unlink('custom/modules/connectors/metadata/connectors.php.bak');
        }

        if ($this->has_custom_display_config_file) {
            copy('custom/modules/connectors/metadata/display_config.php.bak', 'custom/modules/connectors/metadata/display_config.php');
            unlink('custom/modules/connectors/metadata/display_config.php.bak');
        }

        if ($this->has_custom_accounts_detailviewdefs_file) {
            copy('custom/modules/accounts/metadata/detailviewdefs.php.bak', 'custom/modules/accounts/metadata/detailviewdefs.php');
            unlink('custom/modules/accounts/metadata/detailviewdefs.php.bak');
        }

        if ($this->has_custom_contacts_detailviewdefs_file) {
            copy('custom/modules/contacts/metadata/detailviewdefs.php.bak', 'custom/modules/contacts/metadata/detailviewdefs.php');
            unlink('custom/modules/contacts/metadata/detailviewdefs.php.bak');
        }

        if ($this->has_custom_leads_detailviewdefs_file) {
            copy('custom/modules/leads/metadata/detailviewdefs.php.bak', 'custom/modules/leads/metadata/detailviewdefs.php');
            unlink('custom/modules/leads/metadata/detailviewdefs.php.bak');
        }
    }

    public function testDefaultConnectors()
    {
        $this->installConnectors();
        if (!file_exists('custom/modules/connectors/metadata/display_config.php')) {
            $this->markTestSkipped('Mark test skipped.  Likely no permission to write to custom directory.');
        }

        $this->assertTrue(file_exists('custom/modules/connectors/metadata/display_config.php'), "Assert custom/modules/connectors/metadata/display_config.php file created.");
        $this->assertTrue(file_exists('custom/modules/connectors/metadata/connectors.php'), "Assert custom/modules/connectors/metadata/connectors.php file created.");
        $this->assertTrue(file_exists('custom/modules/Accounts/metadata/detailviewdefs.php'), "Assert custom/modules/Accounts/metadata/detailviewdefs.php file created.");
        $this->assertTrue(file_exists('custom/modules/Contacts/metadata/detailviewdefs.php'), "Assert custom/modules/Contacts/metadata/detailviewdefs.php file created.");

        require 'custom/modules/connectors/metadata/connectors.php';
        require 'custom/modules/connectors/metadata/display_config.php';

        $this->assertEquals(count($connectors), 4, "Assert that there are four connectors enabled.");
        $this->assertEquals(count($modules_sources), 3, "Assert that there are two modules (Accounts, Contacts) enabled.");

        $viewdefs = array();

        require 'custom/modules/Accounts/metadata/detailviewdefs.php';
        $this->assertTrue(in_array('CONNECTOR', $viewdefs['Accounts']['DetailView']['templateMeta']['form']['buttons']), "Assert that the Get Data button is added to Accounts detailviewdefs.php file.");

        $accounts_hover_link_set = false;

        foreach ($viewdefs['Accounts']['DetailView']['panels'] as $panels) {
            foreach ($panels as $panel) {
                foreach ($panel as $row => $col) {
                    if (is_array($col) && $col['name'] == 'name') {
                        if (isset($col['displayParams']) && count($col['displayParams']['connectors']) == 1) {
                            $accounts_hover_link_set = true;
                        }
                    }
                }
            }
        }

        $this->assertTrue($accounts_hover_link_set, "Assert that the Accounts hover link is properly set.");
    }

    private function installConnectors()
    {
        $default_connectors = array (
          'ext_rest_zoominfoperson' =>
          array (
            'id' => 'ext_rest_zoominfoperson',
            'name' => 'Zoominfo - Person',
            'enabled' => true,
            'directory' => 'modules/Connectors/connectors/sources/ext/rest/zoominfoperson',
            'modules' =>
            array (
              0 => 'Accounts',
              1 => 'Contacts',
            ),
          ),
          'ext_rest_zoominfocompany' =>
          array (
            'id' => 'ext_rest_zoominfocompany',
            'name' => 'Zoominfo - Company',
            'enabled' => true,
            'directory' => 'modules/Connectors/connectors/sources/ext/rest/zoominfocompany',
            'modules' =>
            array (
              0 => 'Accounts',
            ),
          ),
        );

        $default_modules_sources = array (
          'Accounts' =>
          array (
            'ext_rest_zoominfoperson' => 'ext_rest_zoominfoperson',
            'ext_rest_zoominfocompany' => 'ext_rest_zoominfocompany',
          ),
          'Contacts' =>
          array (
            'ext_rest_zoominfoperson' => 'ext_rest_zoominfoperson',
            'ext_rest_zoominfocompany' => 'ext_rest_zoominfocompany',
          ),
        );

        if (!file_exists('custom/modules/Connectors/metadata')) {
            mkdir_recursive('custom/modules/Connectors/metadata');
        }

        if (!write_array_to_file('connectors', $default_connectors, 'custom/modules/Connectors/metadata/connectors.php')) {
            $GLOBALS['log']->fatal('Cannot write file custom/modules/Connectors/metadata/connectors.php');
        }

        if (!write_array_to_file('modules_sources', $default_modules_sources, 'custom/modules/Connectors/metadata/display_config.php')) {
            $GLOBALS['log']->fatal('Cannot write file custom/modules/Connectors/metadata/display_config.php');
        }

        require_once 'include/connectors/utils/ConnectorUtils.php';
        if (!ConnectorUtils::updateMetaDataFiles()) {
            $GLOBALS['log']->fatal('Cannot update metadata files for connectors');
        }
    }
}
