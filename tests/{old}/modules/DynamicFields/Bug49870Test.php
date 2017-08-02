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

require_once('modules/DynamicFields/FieldCases.php');

class Bug49870Test extends Sugar_PHPUnit_Framework_TestCase {

    private $_modulename = 'Accounts';
    var $field;

    public function setUp() {
        $this->field = get_widget('html');
    }

    public function tearDown() {
        unset($this->field);
    }

    public function testSourceIsNonDBForHTMLField() {
        $defs = $this->field->get_field_def();
        $this->assertSame('non-db', $defs['source']);
    }


}
