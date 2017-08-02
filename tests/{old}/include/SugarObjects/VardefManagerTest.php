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

class VardefManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $module = 'Tests';
    protected $object = 'Test';
    protected $objectName = 'test';

    public function setup()
    {
        // Handle parent setup
        parent::setup();

        // For testing table name getting
        $GLOBALS['dictionary']['Hillbilly']['table'] = 'hillbillies';
    }

    public function tearDown()
    {
        // Get rid of the globals stuff that was setup just for this test
        unset($GLOBALS['dictionary']['Hillbilly']);

        // Let the parent finish things up
        parent::tearDown();
    }

    /**
     * Tests the getTemplates method of the VardefManager. The tested method is
     * wrapped in cache clear calls to prevent downstream tests from suffering.
     *
     * @dataProvider providerGetTemplates
     */
    public function testGetTemplates($module, $object, $template, $object_name, $expect)
    {
        // Clear the fetched templates cache first
        VardefManager::clearFetchedTemplates();

        // Grab the templates for this test
        $actual = VardefManager::getTemplates($module, $object, $template, $object_name);

        // Clear the fetched templates cache again to make sure it doesn't mess
        // things up in later tests
        VardefManager::clearFetchedTemplates();

        // Handle assertions
        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests getting all templates recursively
     *
     *  @dataProvider providerAccountTemplates
     */
    public function testGetAllTemplates($templates, $module, $object, $expect)
    {
        $actual = VardefManager::getAllTemplates($templates, $module, $object);

        // Assert emptiness of array diff, since order is NOT important in this
        // case and an assertEquals will check order as well
        $this->assertEmpty(array_diff($expect['core'], $actual[0]));
        $this->assertEmpty(array_diff($expect['impl'], $actual[1]));
    }

    /**
     * Tests getting the proper object name
     *
     * @dataProvider providerGetObjectName
     */
    public function testGetObjectName($object, $name, $nameOnly, $expect)
    {
        $actual = VardefManager::getObjectName($object, $name, $nameOnly);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests getting the proper table name
     *
     * @dataProvider providerGetTableName
     */
    public function testGetTableName($module, $object, $expect)
    {
        $actual = VardefManager::getTableName($module, $object);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests getting all loadable templates in the correct order
     *
     *  @dataProvider providerLoadableTemplates
     */
    public function testGetLoadableTemplates($templates, $module, $object, $expect)
    {
        $actual = VardefManager::getLoadableTemplates($templates, $module, $object);
        $this->assertEquals($expect, $actual);
    }

    public function testGetCoreTemplates()
    {
        $expect = array(
            'default',
            'basic',
            'company',
            'file',
            'issue',
            'person',
            'sale',
        );

        $actual = VardefManager::getCoreTemplates();
        $this->assertEquals($expect, $actual);
    }

    public function providerGetTemplates()
    {
        return array(
            // Tests handling of Person template
            array(
                'module' => $this->module,
                'object' => $this->object,
                'template' => 'person',
                'object_name' => $this->objectName,
                'expect' => array(
                    'person',
                    'email_address',
                    'taggable',
                ),
            ),
            // Tests handling of 'default' template
            array(
                'module' => $this->module,
                'object' => $this->object,
                'template' => 'default',
                'object_name' => $this->objectName,
                'expect' => array(
                    'basic',
                    'following',
                    'favorite',
                    'taggable',
                    'lockable_fields',
                ),
            ),
        );
    }

    public function providerGetObjectName()
    {
        return array(
            // Tests passing in of object name with mutation
            array(
                'object' => $this->module,
                'name' => $this->object,
                'nameOnly' => false,
                'expect' => $this->objectName,
            ),
            // Tests passing in of object name with no mutation
            array(
                'object' => $this->module,
                'name' => $this->object,
                'nameOnly' => true,
                'expect' => $this->object,
            ),
            // Tests not passing in of object name with mutation
            array(
                'object' => $this->module,
                'name' => '',
                'nameOnly' => false,
                'expect' => 'tests',
            ),
            // Tests not passing in of object name with NO mutation
            array(
                'object' => $this->module,
                'name' => '',
                'nameOnly' => true,
                'expect' => $this->module,
            ),
        );
    }

    public function providerGetTableName()
    {
        return array(
            // Tests no vardef defined
            array(
                'module' => 'Hucksters',
                'object' => 'Huckster',
                'expect' => 'hucksters',
            ),
            // Tests vardef defined
            array(
                'module' => 'Womprats',
                'object' => 'Hillbilly',
                'expect' => 'hillbillies',
            ),
        );
    }

    public function providerLoadableTemplates()
    {
        return array(
            array(
                'templates' => array(
                    'default',
                    'assignable',
                    'team_security',
                    'company',
                ),
                'module' => 'Accounts',
                'object' => 'Account',
                'expect' => array(
                    'company',
                    'basic',
                    'following',
                    'favorite',
                    'taggable',
                    'lockable_fields',
                    'assignable',
                    'team_security',
                    'email_address',
                ),
            ),
        );
    }

    public function providerAccountTemplates()
    {
        return array(
            array(
                'templates' => array(
                    'default',
                    'assignable',
                    'team_security',
                    'company',
                ),
                'module' => 'Accounts',
                'object' => 'Account',
                'expect' => array(
                    'core' => array(
                        'company',
                        'basic',
                    ),
                    'impl' => array(
                        'following',
                        'favorite',
                        'taggable',
                        'assignable',
                        'team_security',
                        'email_address',
                    ),
                ),
            ),
        );
    }
}

