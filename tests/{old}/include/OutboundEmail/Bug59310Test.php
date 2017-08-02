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
 * @ticket 59310
*/
class Bug59310Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM outbound_email WHERE type='test'");
    }

    public function getFields() {
        return array(
            array('mail_smtpssl'),
            array('mail_smtpport'),
            array('mail_smtppass'),
            array('mail_smtpuser'),
        );
    }

    /**
     * @dataProvider getFields
     * @param string $field
     */
    public function testFieldsEncoding($field)
    {
        // testing insert
        $ob = new OutboundEmail();
        $ob->type = 'test';
        $ob->id = create_guid();
        $ob->new_with_id = true;
        $ob->name = 'Test '.$ob->id;
        $ob->user_id = '1';
        $ob->$field = mt_rand()." test \\ 'test' ".mt_rand();
        $ob->save();
        // testing update
        $ob->new_with_id = false;
        $ob->name = 'Update '.$ob->id;
        $ob->user_id = '1';
        $ob->$field = mt_rand()." test2 \\ 'test2' ".mt_rand();
        $ob->save();
    }
}