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
require_once 'upgrade/scripts/post/7_UpdateFullNameOnListViewForPersonClasses.php';

/**
 * Class UpdateFullNameOnListViewForPersonClassesTest test for
 * SugarUpgradeUpdateFullNameOnListViewForPersonClasses script.
 */
class UpdateFullNameOnListViewForPersonClassesTest extends UpgradeTestCase
{
    /**
     * @var string Class we are testing.
     */
    protected $testClassName = 'SugarUpgradeUpdateFullNameOnListViewForPersonClasses';

    /**
     * @dataProvider providerViewdefs
     * @param array $oldDefs
     * @param string $saveCalledTimes
     * @param array $newDefs
     */
    public function testRun($oldDefs, $saveCalledTimes, $newDefs)
    {
        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getCustomViewDefs', 'saveViewDefsToFile'))
            ->getMock();
        $mock->from_version = '7.6';

        $mock->expects($this->any())
            ->method('getCustomViewDefs')
            ->will($this->returnValue(array('someFileName' => $oldDefs)));

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

        $mock->from_version = '7.7';

        $mock->expects($this->never())
            ->method('getCustomViewDefs');

        $mock->expects($this->never())
            ->method('fixFullNameField');

        $mock->run();
    }

    /**
     * @dataProvider providerViewdefsForFixFullName
     * @param array $viewdefs
     * @param string $calledTimes
     */
    public function testFixFullNameFieldCall($viewdefs, $calledTimes)
    {
        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getCustomViewDefs', 'fixFullNameField'))
            ->getMock();
        $mock->from_version = '7.6';

        $mock->expects($this->any())
            ->method('getCustomViewDefs')
            ->will($this->returnValue(array('someFileName' => $viewdefs)));

        $mock->expects($this->$calledTimes())
            ->method('fixFullNameField')
            ->with($this->equalTo($viewdefs));

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
                                                array(
                                                    'name' => 'full_name',
                                                    //missing type, needs fix
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
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
                                                array(
                                                    'name' => 'full_name',
                                                    //not missing type, no fix needed
                                                    'type' => 'fullname',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'never',
                null,
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
                                                array(
                                                    //no full_name field, no fix needed
                                                    'name' => 'foo',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'never',
                null,
            ),
        );
    }

    public function providerViewdefsForFixFullName()
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
}
