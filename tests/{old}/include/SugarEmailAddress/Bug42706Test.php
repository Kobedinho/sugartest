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
 * @ticket 42706
 */
class Bug42706Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function providerGetPrimaryAddress()
        {
            return array(
                array('test1@test.com', true),
                array('test2@test.com', false)
            );
        }

    /**
     * @group bug42706
     * @dataProvider providerGetPrimaryAddress
     */
    public function testGetPrimaryAddress($email, $invalid)
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->emailAddress->addAddress($email, false, false, $invalid);
        $user->emailAddress->save($user->id, $user->module_dir);

        if ($invalid == true)
        {
            $this->assertEmpty($user->emailAddress->getPrimaryAddress($user), 'Primary email should be empty');
        }
        else
        {
            $this->assertEquals($email, $user->emailAddress->getPrimaryAddress($user), 'Primary email should be '.$email);
        }
    }
}
