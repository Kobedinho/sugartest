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

class M2MRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $opportunity;
    private $opportunity2;
    private $contact;
    private $def;

    public function setUp()
    {

        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->opportunity2 = SugarTestOpportunityUtilities::createOpportunity();
        $GLOBALS['db']->commit();

        $this->def = array(
            'table'=>'opportunities_contacts',
            'join_table'=>'opportunities_contacts',
            'name'=>'opportunities_contacts',
            'lhs_module' => 'opportunities',
            'rhs_module' => 'contacts'
        );
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * @group SP-1043
     */
    public function testM2MRelationshipFields()
    {
        $this->opportunity->load_relationship('contacts');
        $this->opportunity->contacts->add($this->contact, array('contact_role' => 'test'));

        $m2mRelationship = new M2MRelationship($this->def);
        $m2mRelationship->join_key_lhs = 'opportunity_id';
        $m2mRelationship->join_key_rhs = 'contact_id';
        $result = $m2mRelationship->relationship_exists($this->opportunity, $this->contact);

        $entry_id = $GLOBALS['db']->getOne("SELECT id FROM opportunities_contacts WHERE opportunity_id='{$this->opportunity->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals($entry_id, $result);

        $role = $GLOBALS['db']->getOne("SELECT contact_role FROM opportunities_contacts WHERE opportunity_id='{$this->opportunity->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals("test", $role);

        $result = $m2mRelationship->relationship_exists($this->opportunity2, $this->contact);
        $this->assertEmpty($result);
    }

    /**
     * @group SP-1043
     */
    public function testM2MRelationshipFieldUpdate()
    {
        $this->opportunity->load_relationship('contacts');
        $this->opportunity->contacts->add($this->contact, array('contact_role' => 'test'));

        $m2mRelationship = new M2MRelationship($this->def);
        $m2mRelationship->join_key_lhs = 'opportunity_id';
        $m2mRelationship->join_key_rhs = 'contact_id';
        $result = $m2mRelationship->relationship_exists($this->opportunity, $this->contact);

        $entry_id = $GLOBALS['db']->getOne("SELECT id FROM opportunities_contacts WHERE opportunity_id='{$this->opportunity->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals($entry_id, $result);

        $role = $GLOBALS['db']->getOne("SELECT contact_role FROM opportunities_contacts WHERE opportunity_id='{$this->opportunity->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals("test", $role);

        $this->opportunity->contacts->add($this->contact, array('contact_role' => 'test2'));

        $second_id = $GLOBALS['db']->getOne("SELECT id FROM opportunities_contacts WHERE opportunity_id='{$this->opportunity->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals($entry_id, $second_id, "Entry ID shouldn't change when updating relationship fields");

        $role = $GLOBALS['db']->getOne("SELECT contact_role FROM opportunities_contacts WHERE opportunity_id='{$this->opportunity->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals("test2", $role);
    }

    /**
     * Test join alias when building joins - BR-2039
     * @covers M2MRelationship::buildJoinSugarQuery
     */
    public function testBuildJoinSugarQueryJoinAlias()
    {
        $m2m = $this->getMockBuilder('M2MRelationship')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $query = $this->getSugarQueryMockBase(array('getJoinTableAlias'));

        // Ensure join table alias is not called as `$isLink`
        $query->expects($this->once())
            ->method('getJoinTableAlias')
            ->with($this->anything(), false, false);

        $link = $this->getLinkMock();
        $m2m->buildJoinSugarQuery($link, $query, array());
    }

    /**
     * Return SugqrQuery mock
     * @param array|null $methods Mockbuilder methods
     * @return SugarQuery
     */
    private function getSugarQueryMockBase($methods)
    {
        return $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Return Link2 mock
     * @param string $side LHS or RHS
     * @return Link2
     */
    private function getLinkMock($side = 'LHS')
    {
        $link = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();

        $link->expects($this->any())
            ->method('getSide')
            ->will($this->returnValue($side));

        return $link;
    }

    /**
     * @covers M2MRelationship::getType
     */
    public function testGetType()
    {
        $relationship = $this->getMockBuilder('M2MRelationship')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(REL_TYPE_MANY, $relationship->getType(REL_LHS));
        $this->assertEquals(REL_TYPE_MANY, $relationship->getType(REL_RHS));
    }
    
    /**
     * provider for testGetFields
     * @return array
     */
    public function providerTestGetFields()
    {
        global $dictionary;
        $template = [
            'name'=>'opportunities_contacts',
            'lhs_module' => 'opportunities',
            'rhs_module' => 'contacts',
        ];
        return [
            [array_merge($template, ['fields' => ['test']]), ['test']],
            [
                array_merge($template, ['table' => 'opportunities_contacts']),
                $dictionary['opportunities_contacts']['fields'],
            ],
        ];
    }
    
    /**
     * @covers M2MRelationship::getFields
     * @dataProvider providerTestGetFields
     * @param array $def
     * @param array $result
     */
    public function testGetFields($def, $result)
    {
        $rel = new M2MRelationship($def);
        $this->assertEquals($result, $rel->getFields());
    }
}
