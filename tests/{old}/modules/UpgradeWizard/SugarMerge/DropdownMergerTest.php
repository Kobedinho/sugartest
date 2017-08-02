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
 * @covers DropdownMerger
 */
class DropdownMergerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DropdownMerger
     */
    protected $merger;

    public function setUp()
    {
        parent::setUp();

        $this->merger = new DropdownMerger();
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewInsertsAnOptionAtTheBeginning_NoOtherChanges_OptionInsertedAtTheBeginning()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Insert_At_Beginning' => 'Insert At Beginning',
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewInsertsAnOptionInTheMiddle_NoOtherChanges_OptionInsertedInTheMiddle()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Insert_In_Middle' => 'Insert In Middle',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewInsertsAnOptionAtTheEnd_NoOtherChanges_OptionInsertedAtTheEnd()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
            'Insert_At_End' => 'Insert At End',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomInsertsAnOptionAtTheBeginning_NoOtherChanges_OptionInsertedAtTheBeginning()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Insert_At_Beginning' => 'Insert At Beginning',
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomInsertsAnOptionInTheMiddle_NoOtherChanges_OptionInsertedInTheMiddle()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Insert_In_Middle' => 'Insert In Middle',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomInsertsAnOptionAtTheEnd_NoOtherChanges_OptionInsertedAtTheEnd()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
            'Insert_At_End' => 'Insert At End',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewInsertsAnOptionWhoseKeyAlreadyExistsInCustom_TheOptionFromCustomIsPreserved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
            'SMS' => 'Text Message',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
            'SMS' => 'SMS Message',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewInsertsAnOptionWhoseKeyAlreadyExistsInCustomButInADifferentOrder_TheOrderFromNewIsUsed()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
            'SMS' => 'Text Message',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'SMS' => 'SMS Message',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'SMS' => 'Text Message',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewRemovesAnOptionFromTheBeginning_NoOtherChanges_OptionRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewRemovesAnOptionFromTheMiddle_NoOtherChanges_OptionRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewRemovesAnOptionFromTheEnd_NoOtherChanges_OptionRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomRemovesAnOptionFromTheBeginning_NoOtherChanges_OptionRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomRemovesAnOptionFromTheMiddle_NoOtherChanges_OptionRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomRemovesAnOptionFromTheEnd_NoOtherChanges_OptionRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewRemovesAnOptionThatIsMovedInCustom_OptionIsRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Meeting' => 'Meeting', // moved
            'Call' => 'Call',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewRemovesAnOptionWhoseValueWasChangedByCustom_OptionIsRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'To Do', // value changed
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomRemovesAnOptionWhoseValueWasChangedByNew_OptionIsRemoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'To Do', // value changed
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewChangesAnOptionValue_NoOtherChanges_OptionValueIsChanged()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'To Do', // value changed
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomChangesAnOptionValue_NoOtherChanges_OptionValueIsChanged()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'To Do', // value changed
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomChangesAnOptionValue_NewChangesTheSameOptionValue_TheOptionValueFromCustomIsUsed()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'To Do', // value changed
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Item', // value changed
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewMovesAnOptionFromTheBeginningToTheMiddle_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Call' => 'Call', // moved
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewMovesAnOptionFromTheMiddleToTheBeginning_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Task' => 'Task', // moved
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewMovesAnOptionFromTheMiddleToTheEnd_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
            'Task' => 'Task', // moved
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewMovesAnOptionFromTheBeginningToTheEnd_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
            'Call' => 'Call', // moved
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewMovesAnOptionFromTheEndToTheBeginning_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Note' => 'Note', // moved
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_NewMovesAnOptionFromTheMiddleToTheMiddle_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Task' => 'Task',
            'Email' => 'Email',
            'Meeting' => 'Meeting', // moved
            'Note' => 'Note',
        );

        $expected = $new;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomMovesAnOptionFromTheBeginningToTheMiddle_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Call' => 'Call', // moved
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomMovesAnOptionFromTheMiddleToTheBeginning_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Task' => 'Task', // moved
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomMovesAnOptionFromTheMiddleToTheEnd_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
            'Task' => 'Task', // moved
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomMovesAnOptionFromTheBeginningToTheEnd_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
            'Call' => 'Call', // moved
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomMovesAnOptionFromTheEndToTheBeginning_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Note' => 'Note', // moved
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomMovesAnOptionFromTheMiddleToTheMiddle_NoOtherChanges_OptionIsMoved()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Task' => 'Task',
            'Email' => 'Email',
            'Meeting' => 'Meeting', // moved
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomSwapsTwoOptionsAndInsertsAnOptionBetweenThem()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Task' => 'Task',
            'Custom1' => 'Custom1',
            'Meeting' => 'Meeting',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomSwapsTwoOptionsAndInsertsAnOptionBefore()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Custom1' => 'Custom1',
            'Email' => 'Email',
            'Task' => 'Task',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomSwapsTwoOptionsAndInsertsAnOptionAfter()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Task' => 'Task',
            'Meeting' => 'Meeting',
            'Custom1' => 'Custom1',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $new = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $expected = $custom;
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_CustomAddsNewOption_NewAddsNewOption_BothAdded()
    {
        $old = array();

        $custom = array(
            'foo' => 'bar',
            'biz' => 'baz',
        );

        $new = array(
            'foo' => 'foo',
            'fizz' => 'buzz'
        );

        $expected = array(
            'foo' => 'bar',
            'fizz' => 'buzz',
            'biz' => 'baz',
        );
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    public function customMakesANumberOfChangesAndNewMakesOnlyOneChangeProvider()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Phone Call', // value changed
            'Meeting' => 'Meeting',
            'Note' => 'Note', // moved
            'Task' => 'Task',
            //'Email' => 'Email', // removed
            'SMS' => 'Text Message', // added
        );

        return array(
            array(
                $old,
                $custom,
                array(
                    'None' => 'None', // added
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'Task' => 'Task',
                    'Email' => 'Email',
                    'Note' => 'Note',
                ),
                array(
                    'None' => 'None',
                    'Call' => 'Phone Call',
                    'Meeting' => 'Meeting',
                    'Note' => 'Note',
                    'Task' => 'Task',
                    'SMS' => 'Text Message',
                )
            ),
            // NEW adds an option after "Meeting" and its location is preserved
            array(
                $old,
                $custom,
                array(
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'New1' => 'New1', // added
                    'Task' => 'Task',
                    'Email' => 'Email',
                    'Note' => 'Note',
                ),
                array(
                    'Call' => 'Phone Call',
                    'Meeting' => 'Meeting',
                    'New1' => 'New1',
                    'Note' => 'Note',
                    'Task' => 'Task',
                    'SMS' => 'Text Message',
                ),
            ),
            // adding key=SMS to NEW only affects the order, the value is retained from CUSTOM
            array(
                $old,
                $custom,
                array(
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'SMS' => 'SMS Message', // value changed
                    'Task' => 'Task',
                    'Email' => 'Email',
                    'Note' => 'Note',
                ),
                array(
                    'Call' => 'Phone Call',
                    'Meeting' => 'Meeting',
                    'SMS' => 'Text Message',
                    'Note' => 'Note',
                    'Task' => 'Task',
                ),
            ),
            // NEW changes an option's value, but that option is removed in CUSTOM... it's a noop
            array(
                $old,
                $custom,
                array(
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'Task' => 'Task',
                    'Email' => 'Email Message', // value changed
                    'Note' => 'Note',
                ),
                $custom,
            ),
            // NEW moves an option that is removed in CUSTOM... it's a noop
            array(
                $old,
                $custom,
                array(
                    'Email' => 'Email', // moved
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'Task' => 'Task',
                    'Note' => 'Note',
                ),
                $custom,
            ),
            array(
                $old,
                $custom,
                array(
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'Task' => 'Task',
                    'Note' => 'Note',
                    'Email' => 'Email',
                ),
                $custom,
            ),
        );
    }

    /**
     * @covers DropdownMerger::merge
     * @dataProvider customMakesANumberOfChangesAndNewMakesOnlyOneChangeProvider
     * @param $old
     * @param $custom
     * @param $new
     * @param $expected
     */
    public function testMerge_CustomMakesANumberOfChanges_NewMakesOneChange($old, $custom, $new, $expected)
    {
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    public function customMovesAnOptionDownAndNewMovesTheSameOptionProvider()
    {
        return array(
            // NEW moves "Note" further down
            array(
                array(
                    'Call' => 'Call',
                    'Task' => 'Task',
                    'Email' => 'Email',
                    'Note' => 'Note',
                    'Meeting' => 'Meeting', // moved
                ),
            ),
            // NEW moves "Note" in different direction
            array(
                array(
                    'Meeting' => 'Meeting', // moved
                    'Call' => 'Call',
                    'Task' => 'Task',
                    'Email' => 'Email',
                    'Note' => 'Note',
                ),
            ),
        );
    }

    /**
     * @covers DropdownMerger::merge
     * @dataProvider customMovesAnOptionDownAndNewMovesTheSameOptionProvider
     * @param $new
     */
    public function testMerge_CustomMovesAnOptionDown_NewMovesTheSameOption_OrderFromCustomIsUsed($new)
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Task' => 'Task',
            'Email' => 'Email',
            'Meeting' => 'Meeting', // moved
            'Note' => 'Note',
        );

        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($custom), json_encode($actual));
    }

    public function customMovesAnOptionUpAndNewMovesTheSameOptionProvider()
    {
        return array(
            // NEW moves "Email" further up
            array(
                array(
                    'Email' => 'Email', // moved
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'Task' => 'Task',
                    'Note' => 'Note',
                ),
            ),
            // NEW moves "Email" in different direction
            array(
                array(
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'Task' => 'Task',
                    'Note' => 'Note',
                    'Email' => 'Email', // moved
                ),
            ),
        );
    }

    /**
     * @covers DropdownMerger::merge
     * @dataProvider customMovesAnOptionUpAndNewMovesTheSameOptionProvider
     * @param $new
     */
    public function testMerge_CustomMovesAnOptionUp_NewMovesTheSameOption_OrderFromCustomIsUsed($new)
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Call' => 'Call',
            'Email' => 'Email', // moved
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Note' => 'Note',
        );

        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($custom), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_LotsOfConflictingChangesInCustomAndNew()
    {
        $old = array(
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task',
            'Email' => 'Email',
            'Note' => 'Note',
        );

        $custom = array(
            'Custom0' => 'Custom0',
            //'Meeting' => 'Meeting', // removed
            'Note' => 'Note', // moved
            'Custom1' => 'Custom1',
            'Task' => 'Task',
            'Call' => 'Call', // moved
            //'Email' => 'Email', // removed
            'Custom2' => 'Custom2',
        );

        $new = array(
            'New0' => 'New0',
            'Meeting' => 'Meeting',
            'Call' => 'Call', // moved
            'New1' => 'New1',
            'Task' => 'Task',
            //'Email' => 'Email', // removed
            //'Note' => 'Note', // removed
        );

        $expected = array(
            'New0' => 'New0',
            'Custom0' => 'Custom0',
            'Custom1' => 'Custom1',
            'Task' => 'Task',
            'Call' => 'Call',
            'New1' => 'New1',
            'Custom2' => 'Custom2',
        );
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers DropdownMerger::merge
     */
    public function testMerge_KeysAreIntegers()
    {
        $old = array(
            60 => '1 minute',
            300 => '5 minutes',
            600 => '10 minutes',
            900 => '15 minutes',
            1800 => '30 minutes',
            3600 => '1 hour',
        );

        $custom = array(
            60 => '1 minute',
            //300 => '5 minutes', // removed
            //600 => '10 minutes', // removed
            //900 => '15 minutes', // removed
            1800 => '30 minutes',
            3600 => '1 hour',
        );

        $new = array(
            0 => '0 minutes', // added
            60 => '1 minute',
            300 => '5 minutes',
            600 => '10 minutes',
            900 => '15 minutes',
            1800 => '30 minutes',
            3600 => '1 hour',
        );

        $expected = array(
            0 => '0 minutes',
            60 => '1 minute',
            1800 => '30 minutes',
            3600 => '1 hour',
        );
        $actual = $this->merger->merge($old, $new, $custom);
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }
}
