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
require_once 'upgrade/scripts/post/7_FixCustomQuotesDetailView.php';

/**
 * Class FixCustomQuotesDetailViewTest test for SugarUpgradeFixCustomQuotesDetailView upgrader script
 */
class FixCustomQuotesDetailViewTest extends UpgradeTestCase
{
    protected $file;
    protected $testScript;

    public function setUp()
    {
        parent::setUp();
        $this->testScript = new TestSugarUpgradeFixCustomQuotesDetailView($this->upgrader);
        SugarAutoLoader::ensureDir('custom/modules/Quotes/metadata');
        $this->file = 'custom/modules/Quotes/metadata/test.php';
    }

    /**
     * Test removing of $LAYOUT_OPTIONS.
     * @param array $data
     * @param array $expected
     *
     * @dataProvider provider
     */
    public function testRun($data, $expected)
    {
        $dataContents = array();
        $dataContents['Quotes']['DetailView'] = array(
            'templateMeta' => array(
                'form' => $data
            ),
        );
        $expectedContents = array();
        $expectedContents['Quotes']['DetailView'] = array(
            'templateMeta' => array(
                'form' => $expected
            ),
        );

        SugarTestHelper::saveFile($this->file);
        write_array_to_file("viewdefs", $dataContents, $this->file);

        $this->testScript->run();

        require($this->file);
        $this->assertEquals($viewdefs, $expectedContents);
    }

    /**
     * Data provider.
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                array(
                    'links' => array(
                        '{$MOD.PDF_FORMAT} <select name="layout" id="layout">{$LAYOUT_OPTIONS}</select></form>',
                    ),
                ),
                array(),
            ),
            array(
                array(
                    'links' => array(
                        '{$MOD.PDF_FORMAT} <select name="layout" id="layout">{$LAYOUT_OPTIONS}</select></form>',
                        'someOtherLink',
                    ),
                ),
                array(
                    'links' => array(
                        'someOtherLink',
                    ),
                ),
            ),
        );
    }
}

/**
 * Test class with additional "mock" logic for tests
 */
class TestSugarUpgradeFixCustomQuotesDetailView extends SugarUpgradeFixCustomQuotesDetailView
{
    public function getFilesToProcess()
    {
        return array('custom/modules/Quotes/metadata/test.php');
    }
}
