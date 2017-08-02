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
 * @group bug45767
 */
class Bug45767Test extends Sugar_PHPUnit_Framework_TestCase {
    public function setUp() {
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }

    public function tearDown() {
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    public function testCanGetFieldsFromTargetPanel() {
        $parser = new GridLayoutMetaDataParser(MB_EDITVIEW, 'Contacts');
        $fields = $parser->getFieldsInPanel('lbl_contact_information');
        $this->assertSame(array("description", "(empty)",), $fields);
    }
}
