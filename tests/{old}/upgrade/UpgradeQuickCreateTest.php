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

class UpgradeQuickCreateTest extends UpgradeTestCase
{
    /**
     * @var string
     */
    public $quickCreateFile;

    /**
     * @var string
     */
    public $module = 'Accounts';

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');

        $this->quickCreateFile = "custom/modules/{$this->module}/clients/base/menus/quickcreate/quickcreate.php";

        $metadata = array(
            'layout' => 'create',
            'label' => 'LNK_NEW_ACCOUNT',
            'visible' => true, // Only visible modules have order.
            'icon' => 'fa-plus',
        );

        sugar_mkdir(dirname($this->quickCreateFile), null, true);
        write_array_to_file(
            "viewdefs['{$this->module}']['base']['menu']['quickcreate']",
            $metadata,
            $this->quickCreateFile
        );
    }

    public function tearDown()
    {
        if (is_file($this->quickCreateFile)) {
            SugarAutoLoader::unlink($this->quickCreateFile);
        }
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Order should be populated with default value if not exists.
     */
    public function testModifyCustomQuickCreateFilesWithOrder()
    {
        $script = $this->upgrader->getScript('post', '5_UpgradeCustomViews');
        $script->run();

        require $this->quickCreateFile;
        $meta = $viewdefs[$this->module]['base']['menu']['quickcreate'];

        $this->assertArrayHasKey('order', $meta);
    }
}
