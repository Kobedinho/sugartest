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

require_once "tests/{old}/upgrade/UpgradeTestCase.php";

class CategoryTypeRecordViewFixTest extends UpgradeTestCase
{
    protected $file = 'custom/modules/Products/clients/base/views/record/record.php';
    protected $fileContents = false;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        if (SugarAutoLoader::fileExists($this->file)) {
            $this->fileContents = sugar_file_get_contents($this->file);
        }

        sugar_mkdir(dirname($this->file), null, true);
        sugar_file_put_contents(
            $this->file,
            file_get_contents(__DIR__ . '/_files/record.php')
        );
    }

    public function tearDown()
    {
        rmdir_recursive(dirname($this->file));

        if (is_string($this->fileContents)) {
            sugar_mkdir(dirname($this->file), null, true);
            sugar_file_put_contents($this->file, $this->fileContents);
        }

        parent::tearDown();
    }

    public function testRun()
    {
        $this->upgrader->setVersions('6.7.4', 'ent', '7.2.0', 'ent');
        $script = $this->upgrader->getScript('post', '7_CategoryTypeRecordViewFix');
        $script->run();

        $viewdefs = null;

        include($this->file);

        $this->assertNotEmpty($viewdefs);
        $fields = $viewdefs['Products']['base']['view']['record']['panels'][1]['fields'];
        foreach ($fields as $field) {
            $this->assertNotContains('_id', $field['name']);
            $this->assertContains('_name', $field['name']);
        }

        $viewdefs = null;
    }
}
