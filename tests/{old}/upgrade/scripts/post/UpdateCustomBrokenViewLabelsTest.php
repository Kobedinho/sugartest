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

class UpdateCustomBrokenViewLabelsTest extends UpgradeTestCase
{
    protected $viewdefs;

    public function setUp()
    {
        parent::setUp();
        $this->viewdefs = array(
            'foo'=>'bar',
            'label'=>'{$MOD.MY_LABEL||strip_semicolon}',
            'baz' => array(
                'foo'=>'bar',
                'label'=>'{$MOD.MY_LABEL||strip_semicolon}',
            ),
            'biz' => array(
                'foo' => 'bar',
                'label' => 'test test test',
            ),
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->viewdefs = null;
    }

    /**
     * Remove smarty template syntax viewdef labels, test that they are removed
     */
    public function testUpdateCustomBrokenViewLabels()
    {
        $script = $this->upgrader->getScript('post', '7_UpdateCustomBrokenViewLabels');
        $script->fixLabels($this->viewdefs);
        $this->checkViewdefs($this->viewdefs);
    }

    /**
     * @param $viewdefs viewdefs to check
     */
    public function checkViewdefs($viewdefs)
    {
        foreach ($viewdefs as $key => $val) {
            if (is_array($val)) {
                $this->checkViewdefs($val);
            } elseif ($key === 'label') {
                $this->assertTrue(strpos($val, 'strip_semicolon') === false);
            }
        }
    }
}
