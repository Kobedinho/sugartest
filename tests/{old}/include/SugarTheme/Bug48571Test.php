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


class Bug48571Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $globalDefaultTheme;
    var $unavailableThemes;
    var $customThemeDef;

    public function setUp()
    {
        if(isset($GLOBALS['sugar_config']['default_theme']))
        {
            $this->globalDefaultTheme = $GLOBALS['sugar_config']['default_theme'];
            unset($GLOBALS['sugar_config']['default_theme']);
        }

        if(isset($GLOBALS['sugar_config']['disabled_themes']))
        {
            $this->unavailableThemes = $GLOBALS['sugar_config']['disabled_themes'];
            unset($GLOBALS['sugar_config']['disabled_themes']);
        }

        if(file_exists('custom/themes/default/themedef.php'))
        {
            $this->customThemeDef = file_get_contents('custom/themes/default/themedef.php');
            SugarAutoLoader::unlink('custom/themes/default/themedef.php');
        }

        //Blowout all existing cache/themes that may not have been cleaned up
        if(file_exists('cache/themes'))
        {
            rmdir_recursive('cache/themes');
        }

    }

    public function tearDown()
    {
        if(!empty($this->globalDefaultTheme))
        {
            $GLOBALS['sugar_config']['default_theme'] = $this->globalDefaultTheme;
            unset($this->globalDefaultTheme);
        }

        if(!empty($this->unavailableThemes))
        {
            $GLOBALS['sugar_config']['disabled_themes'] = $this->unavailableThemes;
            unset($this->unavailableThemes);
        }

        if(!empty($this->customThemeDef))
        {
            SugarAutoLoader::put('custom/themes/default/themedef.php', $this->customThemeDef);
        }
    }

    public function testBuildRegistry()
    {

        SugarThemeRegistry::buildRegistry();
        $themeObject = SugarThemeRegistry::current();
        $this->assertRegExp('/Racer X/i', $themeObject->__get('name'), 'Assert that buildRegistry defaults to the Sugar theme');

    }

}

?>
