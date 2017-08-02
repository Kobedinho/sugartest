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

/**
 * SugarSpriteBuilderTest
 *
 * This test simply checks that we can run the rebuildSprite function which in turn runs SugarSpriteBuilder
 *
 */
class SugarSpriteBuilderTest extends Sugar_PHPUnit_Framework_TestCase
{

var $useSprites;

public function setUp()
{
    if(!function_exists('imagecreatetruecolor'))
    {
        $this->markTestSkipped('imagecreatetruecolor function not found.  skipping test');
        return;
    }
    if (empty($GLOBALS['sugar_config']['use_sprites']))
    {
        $GLOBALS['sugar_config']['use_sprites'] = null;
    }

    $this->useSprites = $GLOBALS['sugar_config']['use_sprites'];
    $GLOBALS['sugar_config']['use_sprites'] = true;

    if(file_exists('cache/sprites'))
    {
        rmdir_recursive('cache/sprites');
    }
}

public function tearDown()
{
    $GLOBALS['sugar_config']['use_sprites'] = $this->useSprites;
}

public function testSugarSpriteBuilder()
{
    $this->markTestIncomplete('This is failing due to file issue on upgrade wizard. Needs to be fixed by FRM team.');
    require_once('modules/UpgradeWizard/uw_utils.php');
    rebuildSprites(true);
    $this->assertTrue(file_exists('cache/sprites'), 'Assert that we have built the sprites directory');
    $files = glob('cache/sprites/default/*.png');
    $this->assertTrue(!empty($files), 'Assert that we have created .png sprite images');
}

}
