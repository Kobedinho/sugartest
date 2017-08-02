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


class SaveRelationshipChangesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setRelationshipInfoDataProvider()
    {
        return array(
            array(
                1,
                'accounts_contacts',
                array(1, 'contacts'),
            ),
            array(
                1,
                'member_accounts',
                array(1, 'member_of'),
            ),
            array(
                1,
                'accounts_opportunities',
                array(1, 'opportunities'),
            ),
        );
    }

    /**
     * @dataProvider setRelationshipInfoDataProvider
     */
    public function testSetRelationshipInfoViaRequestVars($id, $rel, $expected)
    {
        $bean = new Account();

        $_REQUEST['relate_to'] = $rel;
        $_REQUEST['relate_id'] = $id;

        $return = SugarTestReflection::callProtectedMethod($bean, 'set_relationship_info');

        $this->assertSame($expected, $return);
    }

    /**
     * @dataProvider setRelationshipInfoDataProvider
     */
    public function testSetRelationshipInfoViaBeanProperties($id, $rel, $expected)
    {
        $bean = new Account();

        $bean->not_use_rel_in_req = true;
        $bean->new_rel_id = $id;
        $bean->new_rel_relname = $rel;

        $return = SugarTestReflection::callProtectedMethod($bean, 'set_relationship_info');

        $this->assertSame($expected, $return);
    }

    public function testHandlePresetRelationshipsAdd()
    {
        $contactId = 'some_contact_id';
        $account = $this->createPartialMock('Account', array('load_relationship'));
        $account->expects($this->once())
            ->method('load_relationship')
            ->with('contacts')
            ->willReturn(true);

        $account->contacts = $this->createPartialMock('Link2', array('add'));
        $account->contacts->expects($this->once())
            ->method('add')
            ->with($contactId)
            ->willReturn(true);

        $account->contact_id = $contactId;
        $new_rel_id = SugarTestReflection::callProtectedMethod(
            $account,
            'handle_preset_relationships',
            array($contactId, 'contacts')
        );
        $this->assertFalse($new_rel_id);
    }

    public function testHandlePresetRelationshipsDelete()
    {
        $contactId = 'some_contact_id';
        $accountId = 'some_account_id';
        $account = $this->createPartialMock('Account', array('load_relationship'));
        $account->id = $accountId;
        $account->expects($this->once())
            ->method('load_relationship')
            ->with('contacts')
            ->willReturn(true);

        $account->contacts = $this->createPartialMock('Link2', array('delete'));
        $account->contacts->expects($this->once())
            ->method('delete')
            ->with($accountId, $contactId)
            ->willReturn(true);

        $account->rel_fields_before_value['contact_id'] = $contactId;
        $new_rel_id = SugarTestReflection::callProtectedMethod(
            $account,
            'handle_preset_relationships',
            array($contactId, 'contacts')
        );
        $this->assertEquals($contactId, $new_rel_id);
    }

    public function testHandleRemainingRelateFields()
    {
        $thisId = 'this_id';
        $relateId = 'relate_id';

        $account = $this->createPartialMock('Account', array('load_relationship'));
        $account->expects($this->atLeastOnce())
            ->method('load_relationship')
            ->with('relate_field_link')
            ->willReturn(true);

        $account->relate_field_link = $this->createPartialMock('Link2', array('add', 'delete'));
        $account->relate_field_link->expects($this->once())
            ->method('add')
            ->with($relateId)
            ->willReturn(true);
        $account->relate_field_link->expects($this->once())
            ->method('delete')
            ->with($thisId, $relateId)
            ->willReturn(true);

        $account->field_defs['relate_field'] = array(
            'name' => 'relate_field',
            'id_name' => 'relate_field_id',
            'type' => 'relate',
            'save' => true,
            'link' => 'relate_field_link',
        );
        $account->field_defs['relate_field_id'] = array(
            'name' => 'relate_field_id',
            'type' => 'id',
        );
        $account->field_defs['relate_field_link'] = array(
            'name' => 'relate_field_link',
            'type' => 'link',
        );

        SugarBean::clearLoadedDef('Account');

        $account->id = $thisId;
        $account->relate_field_id = $relateId;
        $ret = SugarTestReflection::callProtectedMethod($account, 'handle_remaining_relate_fields');
        $this->assertContains('relate_field_link', $ret['add']['success']);

        $account->rel_fields_before_value['relate_field_id'] = $relateId;
        $account->relate_field_id = '';
        $ret = SugarTestReflection::callProtectedMethod($account, 'handle_remaining_relate_fields');
        $this->assertContains('relate_field_link', $ret['remove']['success']);
    }

    public function testHandleRequestRelate()
    {
        $relateId = 'relate_id';

        $account = $this->createPartialMock('Account', array('load_relationship'));
        $account->expects($this->any())
            ->method('load_relationship')
            ->with('member_of')
            ->willReturn(true);

        $account->member_of = $this->createPartialMock('Link2', array('add', 'delete'));
        $account->member_of->expects($this->once())
            ->method('add')
            ->with($relateId)
            ->willReturn(true);

        $ret = SugarTestReflection::callProtectedMethod(
            $account,
            'handle_request_relate',
            array($relateId, 'member_of')
        );
        $this->assertTrue($ret);
    }

    public function testHandleRequestRelateWithWrongLetterCase()
    {
        $relateId = 'relate_id';

        $account = $this->createPartialMock('Account', array('load_relationship'));
        $account->expects($this->at(0))
            ->method('load_relationship')
            ->with('MEMBER_OF')
            ->willReturn(false);

        $account->expects($this->at(1))
            ->method('load_relationship')
            ->with('member_of')
            ->willReturn(true);

        $account->member_of = $this->createPartialMock('Link2', array('add', 'delete'));
        $account->member_of->expects($this->once())
            ->method('add')
            ->with($relateId)
            ->willReturn(true);

        $ret = SugarTestReflection::callProtectedMethod(
            $account,
            'handle_request_relate',
            array($relateId, 'MEMBER_OF')
        );
        $this->assertTrue($ret);
    }

    public function testHandleRequestRelateWhenLinkNameDoesNotExist()
    {
        $rel_link_name = 'some_non_existing_link_name';
        $relateId = 'relate_id';

        $account = $this->createPartialMock('Account', array('load_relationship'));
        $account->expects($this->any())
            ->method('load_relationship')
            ->willReturn(false);

        $ret = SugarTestReflection::callProtectedMethod(
            $account,
            'handle_request_relate',
            array($relateId, $rel_link_name)
        );
        $this->assertFalse($ret);
    }
}
