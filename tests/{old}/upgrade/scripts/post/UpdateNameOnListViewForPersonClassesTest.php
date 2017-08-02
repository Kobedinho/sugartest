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
require_once 'upgrade/scripts/post/7_UpdateNameOnListViewForPersonClasses.php';

/**
 * Class UpdateNameOnListViewForPersonClassesTest test for SugarUpgradeUpdateNameOnListViewForPersonClasses script.
 */
class UpdateNameOnListViewForPersonClassesTest extends UpgradeTestCase
{
    /**
     * @var string Class we are testing.
     */
    protected $testClassName = 'SugarUpgradeUpdateNameOnListViewForPersonClasses';

    /**
     * @dataProvider providerViewdefs
     * @param array $oldDefs
     * @param array $fullNameDefs
     * @param string $saveCalledTimes
     * @param array $newDefs
     */
    public function testRun($oldDefs, $fullNameDefs, $saveCalledTimes, $newDefs)
    {
        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getCustomViewDefs', 'getFullNameFieldDefinition', 'saveViewDefsToFile'))
            ->getMock();
        $mock->from_version = '7.5';

        $mock->expects($this->any())
            ->method('getCustomViewDefs')
            ->will($this->returnValue(array('someFileName' => $oldDefs)));

        $mock->expects($this->any())
            ->method('getFullNameFieldDefinition')
            ->will($this->returnValue($fullNameDefs));

        $mock->expects($this->$saveCalledTimes())
            ->method('saveViewDefsToFile')
            ->with($this->equalTo($newDefs));

        $mock->run();
    }

    public function testRunNotVersion()
    {
        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getCustomViewDefs', 'fixNameField'))
            ->getMock();

        $mock->from_version = '7.6';

        $mock->expects($this->never())
            ->method('getCustomViewDefs');

        $mock->expects($this->never())
            ->method('fixNameField');

        $mock->run();
    }

    /**
     * @dataProvider providerViewdefsForFixName
     * @param array $viewdefs
     * @param string $calledTimes
     */
    public function testFixNameFieldCall($viewdefs, $calledTimes)
    {
        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getCustomViewDefs', 'fixNameField'))
            ->getMock();
        $mock->from_version = '7.5';

        $mock->expects($this->any())
            ->method('getCustomViewDefs')
            ->will($this->returnValue(array('someFileName' => $viewdefs)));

        $mock->expects($this->$calledTimes())
            ->method('fixNameField')
            ->with($this->equalTo($viewdefs));

        $mock->run();
    }

    /**
     * @dataProvider providerForGetFullNameFieldDefinition
     * @param string $module
     * @param bool $isFromPerson
     * @param array $viewdefs
     * @param string $calledTimes
     */
    public function testGetFullNameFieldDefinitionCall($module, $isFromPerson, $viewdefs, $calledTimes)
    {
        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('extendsPerson', 'getCustomViewDefs', 'getFullNameFieldDefinition'))
            ->getMock();
        $mock->from_version = '7.5';

        $mock->expects($this->any())
            ->method('getCustomViewDefs')
            ->will($this->returnValue(array('someFileName' => $viewdefs)));

        $mock->expects($this->once())
            ->method('extendsPerson')
            ->with($this->equalTo($module))
            ->will($this->returnValue($isFromPerson));

        $mock->expects($this->$calledTimes())
            ->method('getFullNameFieldDefinition')
            ->with($this->equalTo($module));

        $mock->run();
    }

    public function providerViewdefs()
    {
        return array(
            array(
                array(
                    'Contacts' => array(
                        'base' => array(
                            'view' => array(
                                'list' => array(
                                    'panels' => array(
                                        array(
                                            'fields' => array(
                                                array('name' => 'name'),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'name' => 'full_name',
                    'type' => 'fullname'
                ),
                'once',
                array(
                    'panels' => array(
                        array(
                            'fields' => array(
                                array(
                                    'name' => 'full_name',
                                    'type' => 'fullname',
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            array(
                array(
                    'Contacts' => array(
                        'base' => array(
                            'view' => array(
                                'list' => array(
                                    'panels' => array(
                                        array(
                                            'fields' => array(
                                                array('name' => 'name'),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                null,
                'never',
                null,
            ),
        );
    }

    public function providerViewdefsForFixName()
    {
        return array(
            array(
                array(),
                'never',
            ),
            array(
                array('notEmptyViewdefs'),
                'once',
            ),
        );
    }

    public function providerForGetFullNameFieldDefinition()
    {
        return array(
            array(
                'Contacts',
                true,
                array(
                    'Contacts' => array(
                        'base' => array(
                            'view' => array(
                                'list' => array(
                                    'panels' => array(
                                        array(
                                            'fields' => array(
                                                array('name' => 'name'),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'once',
            ),

            array(
                'Contacts',
                true,
                array(
                    'Contacts' => array(
                        'base' => array(
                            'view' => array(
                                'list' => array(),
                            ),
                        ),
                    ),
                ),
                'never',
            ),

            array(
                'Quotes',
                false,
                array(
                    'Quotes' => array(
                        'base' => array(
                            'view' => array(
                                'list' => array(
                                    'panels' => array(
                                        array(
                                            'fields' => array(
                                                array('name' => 'name'),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'never',
            ),
        );
    }
}
