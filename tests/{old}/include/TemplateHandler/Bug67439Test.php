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



class Bug67439Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $oldObjectList;

    protected static $filesToUnlink = array(
        'cache/modules/Teams/EditView.tpl',
        'cache/modules/Teams/SearchForm_1.tpl',
        'cache/modules/Teams/DetailView.tpl',
    );

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('mod_strings', array('Teams'));

        $GLOBALS['beanFiles']['CustomTeam'] = 'custom/modules/Teams/Team.php';
        $GLOBALS['beanList']['Teams'] = 'CustomTeam';

        if (isset($GLOBALS['objectList'])) {
            static::$oldObjectList = $GLOBALS['objectList'];
        } else {
            $GLOBALS['objectList'] = static::$oldObjectList = array();
        }
        $GLOBALS['objectList']['Teams'] = 'Team';
    }

    protected function tearDown()
    {
        $GLOBALS['objectList'] = static::$oldObjectList;
        foreach(static::$filesToUnlink as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    /**
     * @dataProvider dataProviderForBuildTemplate
     */
    public function testBuildTemplate($view, $metaDataDefs)
    {
        $templateHandler = $this->getMockBuilder('TemplateHandler')
            ->setMethods(array(
                'loadSmarty',
                'createQuickSearchCode',
                'createDependencyJavascript')
            )
            ->disableOriginalConstructor()
            ->getMock();
        $templateHandler->expects($this->any())
            ->method('createQuickSearchCode')
            ->with($this->equalTo($GLOBALS['dictionary']['Team']['fields']));
        $templateHandler->expects($this->any())
            ->method('createDependencyJavascript')
            ->with($this->equalTo($GLOBALS['dictionary']['Team']['fields']));

        $sugarSmarty = $this->getMockBuilder('Sugar_Smarty')
            ->setMethods(array('assign', 'fetch'))
            ->getMock();
        $sugarSmarty->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue('template content'));

        $templateHandler->ss = $sugarSmarty;

        $templateHandler->buildTemplate('Teams', $view, 'tpl', false, $metaDataDefs);
    }

    public function dataProviderForBuildTemplate()
    {
        $metaDataDefs = array(
            'panels' => array(
                array(
                    array(
                        array('name' => 'some_name'),
                        'other_name'
                    )
                )
            )
        );

        return array(
            array('EditView', $metaDataDefs),
            array('SearchForm_1', array()),
            array('DetailView', array())
        );
    }
}
