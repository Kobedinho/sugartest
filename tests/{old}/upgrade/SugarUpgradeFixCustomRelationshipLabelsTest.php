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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';

/**
 * @covers SugarUpgradeFixCustomRelationshipLabels
 */
class SugarUpgradeFixCustomRelationshipLabelsTest extends UpgradeTestCase
{
    protected function setUp()
    {
        SugarTestHelper::setUp('moduleList');
        parent::setUp();
    }

    public function testFixCustomLabelsForRelationships()
    {
        $accountsFile = $this->createLanguageFile('Accounts', 'Accounts Upgrade Test');
        $contactsFile = $this->createLanguageFile('Contacts', 'Contacts Upgrade Test');

        $script = $this->upgrader->getScript('post', '2_FixCustomRelationshipLabels');
        $script->silent = true;
        $script->run();

        $this->assertLabelsRebuilt($accountsFile, 'Accounts Upgrade Test');
        $this->assertLabelsRebuilt($contactsFile, 'Contacts Upgrade Test');
    }

    private function createLanguageFile($module, $label)
    {
        $src = 'custom/modules/' . $module . '/Ext/Language/en_us.lang.ext.php';
        $dst = 'custom/Extension/modules/' . $module . '/Ext/Language/en_us.lang.php';
        $files[$module] = $dst;
        SugarTestHelper::saveFile($src);
        SugarTestHelper::saveFile($dst);

        mkdir_recursive(dirname($src));
        file_put_contents($src, <<<SRC
<?php

\$mod_strings['LBL_UPGRADE_TEST'] = '$label';
SRC
        );

        return $dst;
    }

    protected function assertLabelsRebuilt($file, $expected)
    {
        $mod_strings = array();
        $this->assertFileExists($file);
        require $file;

        $this->assertArrayHasKey('LBL_UPGRADE_TEST', $mod_strings);
        $this->assertEquals($expected, $mod_strings['LBL_UPGRADE_TEST']);
    }
}
