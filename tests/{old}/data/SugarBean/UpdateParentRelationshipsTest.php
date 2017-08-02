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


class UpdateParentRelationshipsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**#@+
     * @var Account
     */
    private static $account1;
    private static $account2;
    /**#@-*/

    /**
     * @var Call
     */
    private static $call;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');

        self::$account1 = SugarTestAccountUtilities::createAccount();
        self::$account2 = SugarTestAccountUtilities::createAccount();
        self::$call = SugarTestCallUtilities::createCall();

        // link call to account
        self::$call->load_relationship('accounts');
        self::$call->accounts->add(self::$account1);
    }

    public static function tearDownAfterClass()
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testUpdateParentRelationships()
    {
        /** @var Call $call */
        $call = BeanFactory::getBean('Calls', self::$call->id, array(
            'use_cache' => false,
        ));

        $call->load_relationship('accounts');
        $def = SugarRelationshipFactory::getInstance()->getRelationshipDef('account_calls');

        $relationship = $this->getMockBuilder('One2MBeanRelationship')
            ->setConstructorArgs(array($def))
            ->setMethods(array('callAfterAdd', 'callAfterDelete'))
            ->getMock();

        $linked = $unlinked = array();
        $this->collectInvocations($relationship, 'callAfterAdd', $linked);
        $this->collectInvocations($relationship, 'callAfterDelete', $unlinked);

        SugarTestReflection::setProtectedValue($call->accounts, 'relationship', $relationship);

        // link call to another account
        $call->parent_id = self::$account2->id;
        $call->save();

        // make sure unlink from old account is tracked from both sides
        $this->assertContains(array(
            self::$call->id,
            self::$account1->id,
            'accounts',
        ), $unlinked);

        $this->assertContains(array(
            self::$account1->id,
            self::$call->id,
            'calls',
        ), $unlinked);

        // make sure link to new account is tracked from both sides
        $this->assertContains(array(
            self::$call->id,
            self::$account2->id,
            'accounts',
        ), $linked);

        $this->assertContains(array(
            self::$account2->id,
            self::$call->id,
            'calls',
        ), $linked);
    }

    private function collectInvocations(PHPUnit_Framework_MockObject_MockObject $mock, $method, &$result)
    {
        $mock->expects($this->any())
            ->method($method)
            ->will($this->returnCallback(function (SugarBean $focus, SugarBean $related, $link) use (&$result) {
                $result[] = array($focus->id, $related->id, $link);
            }));
    }
}
