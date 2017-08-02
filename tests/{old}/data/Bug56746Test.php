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
 * Bug #56746 - Dependent Field that uses a Checkbox does not display on module
 * List View if the checkbox is not on the List View.
 *
 * @ticket 46230
 * @ticket 54042
 * @ticket 56746
 */
class Bug56746Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Account
     */
    protected $account;
    private $stored_service_object;

    protected function setUp()
    {
        parent::setUp();

        //Unset global service_object variable so that the code in updateDependencyBean is run in SugarBean.php
        if(isset($GLOBALS['service_object'])) {
            $this->stored_service_object = $GLOBALS['service_object'];
            unset($GLOBALS['service_object']);
        }

        $time = mt_rand();

        $this->account = new Account();
        $this->account->name = 'SugarAccount' . $time;
        $this->account->email1 = 'account@' . $time . 'sugar.com';

        $this->account->field_defs['checkbox_c'] = array(
            'name' => 'checkbox_c',
            'vname' => 'LBL_CHECKBOX_C',
            'type' => 'bool',
            'default' => '0',
            'comment' => 'Custom checkbox field',
        );
        $this->account->field_defs['text_c'] = array(
            'name' => 'text_c',
            'vname' => 'LBL_TEXT_C',
            'type' => 'varchar',
            'dependency' => 'equal($checkbox_c,true)',
            'comment' => 'Custom field with custom field dependency',
        );
    }

    public function tearDown()
    {
        if(!empty($this->stored_service_object)) {
            $GLOBALS['service_object'] = $this->stored_service_object;
        }
	    unset($this->account->field_defs['checkbox_c']);

        unset($this->account->field_defs['text_c']);
        parent::tearDown();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if the List View shows dependent fields visibility correctly when
     * defined in listviewdefs.php.
     *
     * When we define a field that depends on another on the listviewdefs.php
     * and then only show that field on the List View, we should see the field
     * being displayed based on the data from the field it depends.
     *
     * @group 54042
     * @group 56746
     *
     * @dataProvider providerGetListViewArray
     */
    public function testGetListViewArrayWithDependentFields(
        $checkbox,
        $text,
        $isTextHidden
    ) {
        $filterFields = array(
            'name' => true,
            'city' => true,
            'text_c' => true,
        );

        $this->account->checkbox_c = $checkbox;
        $this->account->text_c = $text;

        $this->account->updateDependentFieldForListView('', $filterFields);
        $this->assertArrayHasKey(
            'hidden',
            $this->account->field_defs['text_c']
        );
        $list = $this->account->get_list_view_array($filterFields);
        if ($isTextHidden) {
            $this->assertEmpty($list['TEXT_C']);
        } else {
            $this->assertSame($text, $list['TEXT_C']);
        }
    }

    /**
     * Test if the List View is performant when there is no dependent fields on
     * listviewdefs.php.
     *
     * @group 54042
     * @group 56746
     *
     * @dataProvider providerGetListViewArray
     */
    public function testGetListViewArrayWithoutDependentFields(
        $checkbox,
        $text,
        $isTextHidden
    ) {
        $filterFields = array(
            'name' => true,
            'city' => true,
            'phone' => true,
        );

        $this->account->checkbox_c = $checkbox;
        $this->account->text_c = $text;

        $this->account->updateDependentFieldForListView('', $filterFields);
        $this->assertArrayNotHasKey(
            'hidden',
            $this->account->field_defs['text_c']
        );
        $list = $this->account->get_list_view_array();
        $this->assertSame($text, $list['TEXT_C']);
    }

    /**
     * Data provider for the testGetListViewArray.
     *
     * @return array
     *   An array with the Account data for: checkbox, text and the result -
     *   boolean if it should hide the field on list view or not.
     *
     * @see Bug56746::testGetListViewArray()
     */
    public function providerGetListViewArray()
    {
        return array(
            array(0, 'Text hidden', true),
            array(1, 'Text being shown properly!', false),
            array(0, 'Text to hide', true),
        );
    }
}
