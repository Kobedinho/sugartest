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
 * @covers ExpressionEngineController
 */
class ExpressionEngine_ControllerTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        $_REQUEST = array();
        parent::tearDown();
    }

    /**
     * @param string $module
     * @param array  $fields
     * @param string $id
     *
     * @dataProvider badRequestProvider
     */
    public function testBadRequest($module, $fields, $id = null)
    {
        $_REQUEST = array(
            'tmodule' => $module,
            'fields' => $fields,
            'record_id' => $id,
        );


        /** @var PHPUnit_Framework_MockObject_MockObject | ExpressionEngineController $controller */
        $controller = $this->createPartialMock('ExpressionEngineController', array('display'));

        // assert that display method was invoked which means no PHP error was triggered
        $controller->expects($this->once())->method('display');
        $controller->action_getRelatedValues();
    }

    public static function badRequestProvider()
    {
        return array(
            'non-json-string' => array('Accounts', 'non-json-string'),
            'bad-common-field-defs' => array('Accounts', json_encode(array(array()))),
            'bad-relate-field-defs' => array('Accounts', json_encode(array(array(
                'link' => 'foo',
                'type' => 'related',
            )))),
            'bad-rollup-field-defs' => array('Accounts', json_encode(array(array(
                'link' => 'foo',
                'type' => 'rollupSum',
            )))),
            'bean-not-found' => array('Accounts', json_encode(array(array(
                'link' => 'contacts',
                'type' => 'count',
            ))), 'non-existing-id'),
        );
    }
}
