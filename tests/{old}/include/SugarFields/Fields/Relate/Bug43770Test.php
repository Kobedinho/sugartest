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


class Bug43770Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_fieldOutput;

    public function setUp()
    {
        $sfr = new SugarFieldRelate('relate');
        $vardef = array(
            'name' => 'assigned_user_name',
            'id_name' => 'assigned_user_id',
            'module' => 'Users'
        );
        $displayParams = array(
            'idName' => 'Contactsassigned_user_name'
        );
        $this->_fieldOutput = $sfr->getEditViewSmarty(array(), $vardef, $displayParams, 1);
    }
    /**
     * @group	bug43770
     */
    public function testCustomIdName()
    {
        $this->assertContains('id="Contacts{$Array.assigned_user_name.id_name}"', $this->_fieldOutput);
    }

    public function testCustomIdNameJS()
    {
        $this->assertContains('"id":"Contactsassigned_user_id"', $this->_fieldOutput);
    }
}