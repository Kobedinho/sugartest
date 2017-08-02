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

require_once 'include/dir_inc.php';

class SugarThemeTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_themeDef;
    /**
     * @var SugarTheme
     */
    private $_themeObject;
    private $_themeDefChild;
    private $_themeObjectChild;

    public function setup()
    {
        parent::setUp();

        $themedef = array();
        include('themes/'.SugarTestThemeUtilities::createAnonymousTheme().'/themedef.php');

        $this->_themeDef = $themedef;
        SugarThemeRegistry::add($this->_themeDef);
        $this->_themeObject = SugarThemeRegistry::get($this->_themeDef['dirName']);

        $themedef = array();
        include('themes/'.SugarTestThemeUtilities::createAnonymousChildTheme($this->_themeObject->__toString()).'/themedef.php');

        $this->_themeDefChild = $themedef;
        SugarThemeRegistry::add($this->_themeDefChild);
        $this->_themeObjectChild = SugarThemeRegistry::get($this->_themeDefChild['dirName']);

        $GLOBALS['sugar_config']['developerMode'] = false;
        $GLOBALS['sugar_config']['minify_resources'] = true;
    }

    public function testMagicIssetWorks()
    {
        $this->assertTrue(isset($this->_themeObject->dirName));
    }

    public function tearDown()
    {
        SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();

        parent::tearDown();
    }

    public function testCaching()
    {
        $this->_themeObject->getCSSURL("style.css");
        $themename = $this->_themeObject->__toString();
        $pathname = "themes/{$themename}/css/style.css";

        // test if it's in the local cache
        $this->assertTrue(isset($this->_themeObject->_cssCache['style.css']));
        $this->assertEquals($pathname, $this->_themeObject->_cssCache['style.css']);

        // destroy object
        $this->_themeObject->__destruct();
        unset($this->_themeObject);

        // now recreate object
        SugarThemeRegistry::add($this->_themeDef);
        $this->_themeObject = SugarThemeRegistry::get($this->_themeDef['dirName']);

        // should still be in local cache
        $this->assertTrue(isset($this->_themeObject->_cssCache['style.css']));
        $this->assertEquals($pathname, $this->_themeObject->_cssCache['style.css']);

        // now, let's tell the theme we want to clear the cache on destroy
        $this->_themeObject->clearCache();

        // destroy object
        $this->_themeObject->__destruct();
        unset($this->_themeObject);

        // now recreate object
        SugarThemeRegistry::add($this->_themeDef);
        $this->_themeObject = SugarThemeRegistry::get($this->_themeDef['dirName']);

        // should not be in local cache
        $this->assertFalse(isset($this->_themeObject->_cssCache['style.css']));
    }

    public function testClearImageCache()
    {
        // populate image cache first
        $this->_themeObject->getAllImages();
        $this->_themeObject->clearImageCache();
        $this->assertAttributeEmpty('_imageCache', $this->_themeObject);
    }

    public function testCreateInstance()
    {
        foreach ( $this->_themeDef as $key => $value )
            $this->assertEquals($this->_themeObject->$key,$value);
    }

    public function testGetFilePath()
    {
        $this->assertEquals($this->_themeObject->getFilePath(),
            'themes/'.$this->_themeDef['name']);
    }

    public function testGetImagePath()
    {
        $this->assertEquals($this->_themeObject->getImagePath(),
            'themes/'.$this->_themeDef['name'].'/images');
    }

    public function testGetCSSPath()
    {
        $this->assertEquals($this->_themeObject->getCSSPath(),
            'themes/'.$this->_themeDef['name'].'/css');
    }

    /**
     *
     */
    public function testGetCSS()
    {
        $matches = array();
        preg_match_all('/href="([^"]+)"/',$this->_themeObject->getCSS(),$matches);
        $i = 0;
        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/yui.css/',$matches[1][$i++]);
        $this->assertRegExp('/include\/javascript\/jquery\/themes\/base\/jquery\.ui\.all\.css/',$matches[1][$i++]);
        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/deprecated.css/',$matches[1][$i++]);
        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/style.css/',$matches[1][$i++]);

        $output = file_get_contents(sugar_cached('themes/').$this->_themeObject->__toString().'/css/style.css');
        $this->assertRegExp('/h2\{display:inline\}/',$output);
    }

    public function testGetCSSWithParams()
    {
        $matches = array();
        preg_match_all('/href="([^"]+)"/',$this->_themeObject->getCSS('blue','small'),$matches);
        $i = 0;
        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/yui.css/',$matches[1][$i++]);
        $this->assertRegExp('/include\/javascript\/jquery\/themes\/base\/jquery\.ui\.all\.css/',$matches[1][$i++]);
        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/deprecated.css/',$matches[1][$i++]);
        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/style.css/',$matches[1][$i++]);

        $output = file_get_contents(sugar_cached('themes/').$this->_themeObject->__toString().'/css/style.css');
        $this->assertRegExp('/h2\{display:inline\}/',$output);
    }

    public function testGetCSSWithCustomStyleCSS()
    {
        create_custom_directory('themes/'.$this->_themeObject->__toString().'/css/');
        sugar_file_put_contents('custom/themes/'.$this->_themeObject->__toString().'/css/style.css','h3 { color: red; }');
        SugarAutoLoader::addToMap('custom/themes/'.$this->_themeObject->__toString().'/css/style.css', false);

        $matches = array();
        preg_match_all('/href="([^"]+)"/',$this->_themeObject->getCSS(),$matches);
        $i = 0;

        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/yui.css/',$matches[1][$i++]);
        $this->assertRegExp('/include\/javascript\/jquery\/themes\/base\/jquery\.ui\.all\.css/',$matches[1][$i++]);
        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/deprecated.css/',$matches[1][$i++]);
        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/css\/style.css/',$matches[1][$i++]);

        $output = file_get_contents(sugar_cached('themes/').$this->_themeObject->__toString().'/css/style.css');
        $this->assertRegExp('/h2\{display:inline\}h3\{color:red\}/',$output);
    }

    public function testGetCSSWithParentTheme()
    {
        $matches = array();
        preg_match_all('/href="([^"]+)"/',$this->_themeObjectChild->getCSS(),$matches);
        $i = 0;

        $this->assertRegExp('/themes\/'.$this->_themeObjectChild->__toString().'\/css\/yui.css/',$matches[1][$i++]);
        $this->assertRegExp('/include\/javascript\/jquery\/themes\/base\/jquery\.ui\.all\.css/',$matches[1][$i++]);
        $this->assertRegExp('/themes\/'.$this->_themeObjectChild->__toString().'\/css\/deprecated.css/',$matches[1][$i++]);
        $this->assertRegExp('/themes\/'.$this->_themeObjectChild->__toString().'\/css\/style.css/',$matches[1][$i++]);

        $output = file_get_contents(sugar_cached('themes/').$this->_themeObjectChild->__toString().'/css/style.css');
        $this->assertRegExp('/h2\{display:inline\}h3\{display:inline\}/',$output);
    }

    public function testGetCSSURLWithInvalidFileSpecifed()
    {
        $this->assertFalse($this->_themeObject->getCSSURL('ThisFileDoesNotExist.css'));
    }

    public function testGetCSSURLAddsJsPathIfSpecified()
    {
        // check one may not hit cache
        $this->assertRegExp('/style\.css\?/',$this->_themeObject->getCSSURL('style.css'));
        // check two definitely should hit cache
        $this->assertRegExp('/style\.css\?/',$this->_themeObject->getCSSURL('style.css'));
        // check three for the jspath not being added
        $this->assertNotContains('?',$this->_themeObject->getCSSURL('style.css',false));
    }

    public function testGetJS()
    {
        $matches = array();
        preg_match_all('/src="([^"]+)"/',$this->_themeObject->getJS(),$matches);
        $i = 0;

        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/js\/style-min.js/',$matches[1][$i++]);

        $output = file_get_contents(sugar_cached('themes/').$this->_themeObject->__toString().'/js/style-min.js');
        $this->assertRegExp('/var dog="cat";/',$output);
    }

    public function testGetJSCustom()
    {
        create_custom_directory('themes/'.$this->_themeObject->__toString().'/js/');
        SugarAutoLoader::put('custom/themes/'.$this->_themeObject->__toString().'/js/style.js','var x = 1;');

        $matches = array();
        preg_match_all('/src="([^"]+)"/',$this->_themeObject->getJS(),$matches);
        $i = 0;

        $this->assertRegExp('/themes\/'.$this->_themeObject->__toString().'\/js\/style-min.js/',$matches[1][$i++]);

        $output = file_get_contents(sugar_cached('themes/').$this->_themeObject->__toString().'/js/style-min.js');
        $this->assertRegExp('/var dog="cat";/',$output);
        $this->assertRegExp('/var x=1;/',$output);
    }

    public function testGetJSWithParentTheme()
    {
        $matches = array();
        preg_match_all('/src="([^"]+)"/',$this->_themeObjectChild->getJS(),$matches);
        $i = 0;

        $this->assertRegExp('/themes\/'.$this->_themeObjectChild->__toString().'\/js\/style-min.js/',$matches[1][$i++]);

        $output = file_get_contents(sugar_cached('themes/').$this->_themeObjectChild->__toString().'/js/style-min.js');
        $this->assertRegExp('/var dog="cat";var bird="frog";/',$output);
    }

    public function testGetJSURLWithInvalidFileSpecifed()
    {
        $this->assertFalse($this->_themeObject->getJSURL('ThisFileDoesNotExist.js'));
    }

    public function testGetJSURLAddsJsPathIfSpecified()
    {
        // check one may not hit cache
        $this->assertRegExp('/style-min\.js\?/',$this->_themeObject->getJSURL('style.js'));
        // check two definitely should hit cache
        $this->assertRegExp('/style-min\.js\?/',$this->_themeObject->getJSURL('style.js'));
        // check three for the jspath not being added
        $this->assertNotContains('?',$this->_themeObject->getJSURL('style.js',false));
    }

    public function testGetImageURL()
    {
        $this->assertEquals('themes/'.$this->_themeObject->__toString().'/images/Accounts.gif',
            $this->_themeObject->getImageURL('Accounts.gif',false));
    }

    public function testGetImageURLWithInvalidFileSpecifed()
    {
        $this->assertFalse($this->_themeObject->getImageURL('ThisFileDoesNotExist.gif'));
    }

    public function testGetImageURLCustom()
    {
        create_custom_directory('themes/'.$this->_themeObject->__toString().'/images/');
        SugarAutoLoader::touch('custom/themes/'.$this->_themeObject->__toString().'/images/Accounts.gif');

        $this->assertEquals('custom/themes/'.$this->_themeObject->__toString().'/images/Accounts.gif',
            $this->_themeObject->getImageURL('Accounts.gif',false));
    }

    public function testGetImageURLCustomDifferentExtension()
    {
        create_custom_directory('themes/'.$this->_themeObject->__toString().'/images/');
        SugarAutoLoader::touch('custom/themes/'.$this->_themeObject->__toString().'/images/Accounts.png');
        $this->assertEquals('custom/themes/'.$this->_themeObject->__toString().'/images/Accounts.png',
            $this->_themeObject->getImageURL('Accounts.gif',false));
    }

    public function testGetImageURLDefault()
    {
        $this->assertEquals('themes/default/images/Emails.gif',$this->_themeObject->getImageURL('Emails.gif',false));
    }

    public function testGetImageURLDefaultCustom()
    {
        create_custom_directory('themes/default/images/');
        SugarAutoLoader::touch('custom/themes/default/images/Emails.gif');

        $this->assertEquals('custom/themes/default/images/Emails.gif',
            $this->_themeObject->getImageURL('Emails.gif',false));

        SugarAutoLoader::unlink('custom/themes/default/images/Emails.gif');
    }

    public function testGetImageURLNotFound()
    {
        $this->assertEquals('',$this->_themeObject->getImageURL('NoImageByThisName.gif',false));
    }

    public function testGetImageURLAddsJsPathIfSpecified()
    {
        // check one may not hit cache
        $this->assertRegExp('/Accounts\.gif\?/',$this->_themeObject->getImageURL('Accounts.gif'));
        // check two definitely should hit cache
        $this->assertRegExp('/Accounts\.gif\?/',$this->_themeObject->getImageURL('Accounts.gif'));
        // check three for the jspath not being added
        $this->assertNotContains('?',$this->_themeObject->getImageURL('Accounts.gif',false));
    }

    public function testGetImageURLWithParentTheme()
    {
        $this->assertEquals('themes/'.$this->_themeObject->__toString().'/images/Accounts.gif',
            $this->_themeObjectChild->getImageURL('Accounts.gif',false));
    }

    public function testGetTemplate()
    {
        $this->assertEquals('themes/'.$this->_themeObject->__toString().'/tpls/header.tpl',
            $this->_themeObject->getTemplate('header.tpl'));
    }

    public function testGetTemplateCustom()
    {
        create_custom_directory('themes/'.$this->_themeObject->__toString().'/tpls/');
        SugarAutoLoader::touch('custom/themes/'.$this->_themeObject->__toString().'/tpls/header.tpl');

        $this->assertEquals('custom/themes/'.$this->_themeObject->__toString().'/tpls/header.tpl',
            $this->_themeObject->getTemplate('header.tpl'));
    }

    public function testGetTemplateDefaultCustom()
    {
        create_custom_directory('themes/default/tpls/');
        SugarAutoLoader::touch('custom/themes/default/tpls/SomeDefaultTemplate.tpl');

        $this->assertEquals('custom/themes/default/tpls/SomeDefaultTemplate.tpl',
            $this->_themeObject->getTemplate('SomeDefaultTemplate.tpl'));

        unlink('custom/themes/default/tpls/SomeDefaultTemplate.tpl');
    }

    public function testGetTemplateWithParentTheme()
    {
        $this->assertEquals('themes/'.$this->_themeObject->__toString().'/tpls/header.tpl',
            $this->_themeObjectChild->getTemplate('header.tpl'));
    }

    public function testGetTemplateNotFound()
    {
        $this->assertFalse($this->_themeObject->getTemplate('NoTemplateWithThisName.tpl'));
    }

    public function testGetAllImages()
    {
        $images = $this->_themeObject->getAllImages();

        $this->assertEquals(
            $this->_themeObject->getImageURL('Emails.gif',false),
            $images['Emails.gif']);
    }

    public function testGetAllImagesWhenImageIsInParentTheme()
    {
        $images = $this->_themeObjectChild->getAllImages();

        $this->assertEquals(
            $this->_themeObjectChild->getImageURL('Accounts.gif',false),
            $images['Accounts.gif']);

        $this->assertContains(
            $this->_themeObject->getImagePath(),
            $images['Accounts.gif']);
    }

    public function testGetImageSpecifyingWidthAndHeightAndOtherAttributes()
    {
        $this->assertEquals(
            $this->_themeObject->getImage('Emails','',20,30,'.gif',"Emails"),
            "<img src=\"". $this->_themeObject->getImageURL('Emails.gif') ."\"  width=\"20\" height=\"30\"  alt=\"Emails\" />"
            );

        // check again to see if caching of the image size works as expected
        $this->assertEquals(
            $this->_themeObject->getImage('Emails','',30,40,'.gif',"Emails"),
            "<img src=\"". $this->_themeObject->getImageURL('Emails.gif') ."\"  width=\"30\" height=\"40\"  alt=\"Emails\" />"
            );
    }

    public function testGetImageDetectingImageHeightAndWidth()
    {
        $this->markTestIncomplete("Sprites have been turned off for now, so skipping test");
        if ($GLOBALS['sugar_flavor'] == 'CE' || $GLOBALS['sugar_flavor'] == 'COM')
            $this->markTestSkipped("skipping for CE UNTIL JELLE gets a chance to fix the related sprites issue next week");
        $size = getimagesize($this->_themeObject->getImageURL('Contacts.gif',false));
        $this->assertRegExp('/<span\s+?class[^>]+?><\/span>/', $this->_themeObject->getImage('Contacts','',null,null,'.gif',"Contacts"));
    }

    public function testGetImageWithInvalidImage()
    {
        $this->assertFalse($this->_themeObject->getImage('ThisImageDoesNotExist'));
    }
}
