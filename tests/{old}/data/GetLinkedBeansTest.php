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

class GetLinkedBeansTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();

    public function setUp()
	{
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
	}

	public function tearDown()
	{
	    foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
        SugarTestHelper::tearDown();
	}

    public function testGetLinkedBeans()
    {
        //Test the accounts_leads relationship
        $account = BeanFactory::newBean("Accounts");
        $account->name = "GetLinkedBeans Test Account";
        $account->save();
        $this->createdBeans[] = $account;

        $case  = BeanFactory::newBean("Cases");
        $case->name = "GetLinkedBeans Test Cases";
        $case->save();
        $this->createdBeans[] = $case;

        $this->assertTrue($account->load_relationship("cases"));
        $this->assertInstanceOf("Link2", $account->cases);
        $this->assertTrue($account->cases->loadedSuccesfully());
        $account->cases->add($case);
        $account->save();

        $where = array(
                 'lhs_field' => 'id',
                 'operator' => ' LIKE ',
                 'rhs_value' => "{$case->id}",
        );

        $cases = $account->get_linked_beans('cases', 'Case', array(), 0, 10, 0, $where);
        $this->assertEquals(1, count($cases), 'Assert that we have found the test case linked to the test account');

        $contact  = BeanFactory::newBean("Contacts");
        $contact->first_name = "First Name GetLinkedBeans Test Contacts";
        $contact->last_name = "First Name GetLinkedBeans Test Contacts";
        $contact->save();
        $this->createdBeans[] = $contact;

        $this->assertTrue($account->load_relationship("contacts"));
        $this->assertInstanceOf("Link2", $account->contacts);
        $this->assertTrue($account->contacts->loadedSuccesfully());
        $account->contacts->add($contact);

        $where = array(
                 'lhs_field' => 'id',
                 'operator' => ' LIKE ',
                 'rhs_value' => "{$contact->id}",
        );

        $contacts = $account->get_linked_beans('contacts', 'Contact', array(), 0, -1, 0, $where);
        $this->assertEquals(1, count($contacts), 'Assert that we have found the test contact linked to the test account');
    }

}
?>