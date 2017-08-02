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

require_once 'modules/DynamicFields/FieldCases.php';

/**
 * Bug #52173
 *
 * Dashlets | Adding relationships (Accounts, Contacts, custom modules) to dashlet filters do not work
 * @ticket 52173
 */

class Bug52173Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Account */
    protected $account = null;

    /** @var Contact */
    protected $contact1 = null;

    /** @var Contact */
    protected $contact2 = null;

    /** @var SugarWidgetFieldrelate */
    protected $sugarWidget = null;

    /** @var TemplateRelatedTextField */
    protected static $relateField;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));

        self::$relateField = SugarTestHelper::setUp('custom_field', array('Contacts', array(
            'name' => 'Bug58931_relateField',
            'type' => 'relate',
            'ext2' => 'Accounts',
        )));
    }

    public function setUp()
    {
        parent::setUp();

        $this->getSugarWidgetFieldRelate();

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact2 = SugarTestContactUtilities::createContact();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        parent::tearDown();
    }

    /**
     * Create SugarWidget for relate field
     */
    public function getSugarWidgetFieldRelate()
    {
        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('context', 'Report');
        $db = new stdClass();
        $db->db = $GLOBALS['db'];
        $db->report_def_str = '';
        $layoutManager->setAttributePtr('reporter', $db);
        $this->sugarWidget = new SugarWidgetFieldRelate($layoutManager);
    }

    /*
    * Check correct execution of the query for Dashlets if filter contains default bean's relate field
    * @return void
    */
    public function testDefaultRelateField()
    {
        $this->contact2->account_id = $this->account->id;
        $this->contact2->save();
        $layoutDef = array( 'name'        => 'account_name',
                            'id_name'     => 'account_id',
                            'type'        => 'relate',
                            'link'        => 'accounts_contacts',
                            'table'       => 'contacts',
                            'table_alias' => 'contacts',
                            'module'      => 'Contacts',
                            'input_name0' => array( 0 => $this->account->id ),
        );
        $out = $this->sugarWidget->queryFilterone_of($layoutDef);
        $this->assertContains($this->contact2->id, $out, 'The request for existing relate field was made incorrectly');
    }

    /*
    * Check correct execution of the query for Dashlets
    * if filter contains default bean's relate field with same LHS and RHS modules
    * @return void
    */
    public function testDefaultRelateFieldForSameLHSAndRHSModules()
    {
        $this->contact2->reports_to_id = $this->contact1->id;
        $this->contact2->save();
        $layoutDef = array( 'name'        => 'report_to_name',
                            'id_name'     => 'reports_to_id',
                            'type'        => 'relate',
                            'link'        => 'contact_direct_reports',
                            'table'       => 'contacts',
                            'table_alias' => 'contacts',
                            'module'      => 'Contacts',
                            'input_name0' => array( 0 => $this->contact2->reports_to_id ),
        );
        $out = $this->sugarWidget->queryFilterone_of($layoutDef);
        $this->assertContains($this->contact2->id, $out, 'The request for existing relate field which has same LHS and RHS modules was made incorrectly');
    }

    /*
    * Check correct execution of the query for Dashlets if filter contains custom relate field
    * @return void
    */
    public function testCustomRelateFieldInDashlet()
    {
        $id = self::$relateField->ext3;
        $this->contact2->$id = $this->account->id;
        $this->contact2->save();
        $layoutDef = array( 'name'          => self::$relateField->name,
                            'id_name'       => self::$relateField->ext3,
                            'type'          => 'relate',
                            'ext2'          => 'Accounts',
                            'custom_module' => 'Contacts',
                            'table'         => 'contacts_cstm',
                            'table_alias'   => 'contacts',
                            'module'        => 'Accounts',
                            'input_name0'   => array( 0 => $this->account->id ),
        );
        $out = $this->sugarWidget->queryFilterone_of($layoutDef);
        $this->assertContains($this->contact2->id, $out, 'The request for custom relate field was made incorrectly');
    }
}
