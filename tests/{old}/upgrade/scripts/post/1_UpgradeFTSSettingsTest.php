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

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'upgrade/scripts/post/1_UpdateFTSSettings.php';

/**
 * @coversDefaultClass SugarUpgradeUpdateFTSSettings
 * Tests for FTS settings upgrade.
 */
class UpgradeFTSSettingsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::mergeModuleList
     * @dataProvider providerTestMergeModuleList
     */
    public function testMergeModuleList(array $oldModuleList, array $newModuleList, array $expected)
    {
        $stub = $this->getClassMock(array('getOldModuleList', 'getNewModuleList'));

        // stub the old module list
        $stub->expects($this->any())
            ->method('getOldModuleList')
            ->will($this->returnValue($oldModuleList));

        // stub the new module list
        $stub->expects($this->any())
            ->method('getNewModuleList')
            ->will($this->returnValue($newModuleList));

        $result = SugarTestReflection::callProtectedMethod($stub, 'mergeModuleList', array());
        $this->assertEquals($expected, $result);
    }

    public function providerTestMergeModuleList()
    {
        return array(
            //same OOB modules
            array(
                array('Accounts' => true, 'Contacts' => true),
                array('Accounts' => true, 'Contacts' => true),
                array(array('Accounts', 'Contacts'), array()),
            ),
            //disabled OOB module
            array(
                array('Accounts' => true, 'Tasks' => false),
                array('Accounts' => true, 'Tasks' => true),
                array(array('Accounts'), array('Tasks')),
            ),
            //disabled custom module
            array(
                array('Accounts' => true, 'Exz_Basic' => false),
                array('Accounts' => true, 'Exz_Basic' => false),
                array(array('Accounts'), array('Exz_Basic')),
            ),
            //enabled custom module
            array(
                array('Accounts' => true, 'Exz_Basic' => true),
                array('Accounts' => true, 'Exz_Basic' => true),
                array(array('Accounts', 'Exz_Basic'), array()),
            ),
            //enabled custom module, but not included in the new enabled modules
            array(
                array('Accounts' => true, 'Exz_Basic' => true),
                array('Accounts' => true, 'Exz_Basic' => false),
                array(array('Accounts', 'Exz_Basic'), array()),
            ),
            //Extra OOB module
            array(
                array('Accounts' => true),
                array('Accounts' => true, 'Employees' => true),
                array(array('Accounts', 'Employees'), array()),
            ),
            //Knowledge Base module enabled
            array(
                array('KBContents' => true),
                array('KBContents' => true),
                array(array('KBContents'), array()),
            ),
            //Knowledge Base module disabled
            array(
                array('KBContents' => false),
                array('KBContents' => false),
                array(array(), array('KBContents')),
            ),
            //Knowledge Base module missing
            array(
                array(),
                array('KBContents' => true),
                array(array('KBContents'), array()),
            ),
        );
    }

    /**
     * @covers ::getOldModuleList
     * @dataProvider providerTestGetOldModuleList
     */
    public function testGetOldModuleList(array $usaModuleList, array $expected)
    {
        $stub = $this->getClassMock(array('getUsaModuleList'));

        // stub the usa module list
        $stub->expects($this->any())
            ->method('getUsaModuleList')
            ->will($this->returnValue($usaModuleList));

        $result = SugarTestReflection::callProtectedMethod($stub, 'getOldModuleList', array());
        $this->assertEquals($expected, $result);
    }

    public function providerTestGetOldModuleList()
    {
        return array(
            //with Knowledge Base module
            array(
                array(
                    'Accounts' => array(
                        'visible' => true,
                    ),
                    'Contacts' => array(
                        'visible' => true,
                    ),
                    'KBDocuments' => array(
                        'visible' => true,
                    ),
                    'Tasks' => array(
                        'visible' => false,
                    ),
                ),
                array(
                    'Accounts' => true,
                    'Contacts' => true,
                    'KBContents' => true,
                    'Tasks' => false,
                ),
            ),
            //without Knowledge Base module
            array(
                array(
                    'Accounts' => array(
                        'visible' => true,
                    ),
                    'Contacts' => array(
                        'visible' => true,
                    ),
                    'Tasks' => array(
                        'visible' => false,
                    ),
                ),
                array(
                    'Accounts' => true,
                    'Contacts' => true,
                    'Tasks' => false,
                ),
            ),
        );
    }

    /**
     * @covers ::getNewModuleList
     * @dataProvider providerTestGetNewModuleList
     */
    public function testGetNewModuleList(array $ftsEnabled, array $ftsDisabled, array $expected)
    {
        $stub = $this->getClassMock(array('getFTSModuleList'));

        // stub the usa module list
        $stub->expects($this->any())
            ->method('getFTSModuleList')
            ->will($this->returnValue(array($ftsEnabled, $ftsDisabled)));

        $result = SugarTestReflection::callProtectedMethod($stub, 'getNewModuleList', array());
        $this->assertEquals($expected, $result);
    }

    public function providerTestGetNewModuleList()
    {
        return array(
            //empty enabled modules
            array(
                array(),
                array('Compaigns', 'Tasks'),
                array('Compaigns' => false, 'Tasks' => false),
            ),
            //empty disabled modules
            array(
                array('Accounts','Contacts','KBContents'),
                array(),
                array('Accounts' => true, 'Contacts' => true, 'KBContents' => true),
            ),
            //empty both
            array(
                array(),
                array(),
                array(),
            ),
            //non-empty both
            array(
                array('Accounts','Contacts','KBContents'),
                array('Tasks'),
                array('Accounts' => true, 'Contacts' => true, 'KBContents' => true, 'Tasks' => false),
            ),
        );
    }

    /**
     * @covers ::getSugarFieldName
     * @dataProvider providerTestGetSugarFieldName
     */
    public function testGetSugarFieldName($file, $expected)
    {
        $stub = $this->getClassMock();

        $result = SugarTestReflection::callProtectedMethod($stub, 'getSugarFieldName', array($file));
        $this->assertEquals($expected, $result);
    }

    public function providerTestGetSugarFieldName()
    {
        return array(
            //custom field
            array(
                'custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_aaa1_c.php',
                'aaa1_c',
            ),
            //regualr modules
            array(
                'custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_facebook.php',
                'facebook',
            ),
            //no match
            array(
                'custom/Extension/modules/Accounts/Ext/Vardefs/sugarfieldfacebook.php',
                '',
            ),
            //no match
            array(
                'custom/Extension/modules/Accounts/Ext/Vardefs/full_text_search_admin.php',
                '',
            ),
            //missing file
            array(
                '',
                '',
            ),
        );
    }

    /**
     * @covers ::mergeFtsDefs
     * @dataProvider providerTestMergeFtsDefs
     */
    public function testMergeFtsDefs($oldDef, $newDef, $expected)
    {
        $stub = $this->getClassMock();

        $result = SugarTestReflection::callProtectedMethod($stub, 'mergeFtsDefs', array($oldDef, $newDef));
        $this->assertEquals($expected, $result);
    }

    public function providerTestMergeFtsDefs()
    {
        return array(
            //empty def
            array(
                array(),
                array(),
                array(),
            ),
            //not supported type
            array(
                array('type' => 'float'),
                array(),
                array(),
            ),
            //supported type & FTS enabled
            array(
                array(
                    'type' => 'varchar',
                    'full_text_search' => array(
                        'boost' => 2,
                        'enabled' => true,
                    ),
                ),
                array(),
                array(
                    'boost' => 1,
                    'enabled' => true,
                    'searchable' => true,
                ),
            ),
            //supported type & FTS disabled
            array(
                array(
                    'type' => 'varchar',
                    'full_text_search' => array(
                        'boost' => 2,
                        'enabled' => false,
                    ),
                ),
                array(),
                array(
                    'boost' => 1,
                    'enabled' => true,
                    'searchable' => false,
                ),
            ),
            //supported type & FTS enabled & new boost value
            array(
                array(
                    'type' => 'varchar',
                    'full_text_search' => array(
                        'boost' => 2,
                        'enabled' => false,
                    ),
                ),
                array(
                    'full_text_search' => array(
                        'boost' => 1.6,
                        'enabled' => true,
                    ),
                ),
                array(
                    'boost' => 1.6,
                    'enabled' => true,
                    'searchable' => false,
                ),
            ),
            //supported type & FTS enabled & boost value in both defs
            array(
                array(
                    'type' => 'name',
                    'full_text_search' => array(
                        'boost' => 3,
                        'enabled' => true,
                    ),
                ),
                array(
                    'type' => 'name',
                    'full_text_search' => array(
                        'boost' => 1.55,
                        'enabled' => true,
                        'searchable' => true,
                    ),
                ),
                array(
                    'boost' => 1.55,
                    'enabled' => true,
                    'searchable' => true,
                ),
            ),
        );
    }

    public function getClassMock($methods = null)
    {
        return  $stub = $this->getMockBuilder('SugarUpgradeUpdateFTSSettings')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

}
