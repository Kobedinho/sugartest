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
require_once 'include/EditView/EditView2.php';

class Bug46365Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_o = null;

    public function setUp()
    {
        $this->_o = $this->getMockBuilder('EditView')
            ->setMethods(array('requiredFirst'))
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown()
    {
        unset($this->_o);
    }

    public function provider()
    {
        return array(
            // 1 row, 1 element, 2 columns
            array(
                array(
                    'templateMeta' => array(
                        'maxColumns' => '2',
                        'widths' => array(
                            array('label' => '10', 'field' => '30'),
                            array('label' => '10', 'field' => '30'),
                        ),
                    ),
                    'panels' => array(
                        'panel1' => array(
                            array(
                                array('name' => 'name1',),
                            ),
                        ),
                    ),
                ),
                true
            ),

            // 1 row, 2 elements, 3 columns
            array(
                array(
                    'templateMeta' => array(
                        'maxColumns' => '3',
                        'widths' => array(
                            array('label' => '10', 'field' => '30'),
                            array('label' => '10', 'field' => '30'),
                            array('label' => '10', 'field' => '30'),
                        ),
                    ),
                    'panels' => array(
                        'panel1' => array(
                            array(
                                array('name' => 'name1',),
                                array('name' => 'name2',),
                            ),
                        ),
                    ),
                ),
                true
            ),
        );
    }

    /**
     * @dataProvider provider
     */
    public function testPanelWithOneFullWidthItem($defs, $shouldHaveFiller)
    {
        $this->_o->defs = $defs;
        $this->_o->render();

        $this->assertEquals($this->hasFiller($defs), $shouldHaveFiller);
    }

    private function hasFiller($defs)
    {
        return (boolean) count(intval($defs['panels']['panel1'][0]) - intval((int) $this->_o->defs['panels']['panel1'][0]));
    }

}

?>