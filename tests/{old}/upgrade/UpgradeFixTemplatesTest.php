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

class UpgradeFixTemplatesTest extends UpgradeTestCase
{

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
        mkdir_recursive('custom/modules/fixtest/metadata');
        mkdir_recursive('custom/modules/fixtest/language/application');

        $this->bad_template = "<?php 1; ?>\n\n\n";

        file_put_contents('custom/modules/fixtest/metadata/testfile.php', $this->bad_template);
        file_put_contents('custom/modules/fixtest/language/testfile2.php', $this->bad_template);
        file_put_contents('custom/modules/fixtest/language/application/testfile3.php', $this->bad_template);

        $this->script = $this->upgrader->getScript("post", "7_FixTemplates");

    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestHelper::tearDown();
        rmdir_recursive("custom/modules/fixtest");
    }

    public function testFixNotMB()
    {
        $this->upgrader->state['MBModules'] = array();
        $this->script->run();

        $this->assertEquals($this->bad_template, file_get_contents('custom/modules/fixtest/metadata/testfile.php'));
        $this->assertEquals($this->bad_template, file_get_contents('custom/modules/fixtest/language/testfile2.php'));
        $this->assertEquals($this->bad_template, file_get_contents('custom/modules/fixtest/language/application/testfile3.php'));
    }

    public function testFixTemplate()
    {
        $this->upgrader->state['MBModules'] = array('fixtest');

        $this->script->run();

        $this->assertEquals('<?php 1; ', file_get_contents('custom/modules/fixtest/metadata/testfile.php'));
        $this->assertEquals('<?php 1; ', file_get_contents('custom/modules/fixtest/language/testfile2.php'));
        $this->assertEquals('<?php 1; ', file_get_contents('custom/modules/fixtest/language/application/testfile3.php'));
    }
}
