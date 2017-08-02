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
require_once('modules/Administration/upgrade_custom_relationships.php');

class UpgradeIncorrectRelationshipTest extends Sugar_PHPUnit_Framework_TestCase {
    public function setUp()
    {
        mkdir_recursive('custom/metadata');
        mkdir_recursive('custom/Extension/modules/unittest_rel/Ext/Vardefs');
        mkdir_recursive('custom/Extension/modules/unittest_rel/Ext/Layoutdefs');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        
        $GLOBALS['current_user']->is_admin = true;
    }
    public function tearDown()
    {
        if ( file_exists('custom/metadata/t_bas_t_bas_1MetaData.php') ) {
            unlink('custom/metadata/t_bas_t_bas_1MetaData.php');
        }
        rmdir_recursive('custom/Extension/modules/unittest_rel');
        SugarTestHelper::tearDown();
    }

    public function testBadOneToMany()
    {
        $testFile = 'custom/Extension/modules/unittest_rel/Ext/Vardefs/bad_relate_example.php';

        $badExample = <<<EOF
<?php
// created: 2013-01-04 17:09:07
\$dictionary["Testrel"]["fields"]["t_person_testrels"] = array (
  'name' => 't_person_testrels',
  'type' => 'link',
  'relationship' => 't_person_testrels',
  'source' => 'non-db',
  'vname' => 'LBL_T_PERSON_TESTRELS_FROM_T_PERSON_TITLE',
  'id_name' => 't_person_testrelst_person_ida',
);
\$dictionary["Testrel"]["fields"]["t_person_testrels_name"] = array (
  'name' => 't_person_testrels_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_T_PERSON_TESTRELS_FROM_T_PERSON_TITLE',
  'save' => true,
  'id_name' => 't_person_testrelst_person_ida',
  'link' => 't_person_testrels',
  'table' => 't_person',
  'module' => 't_person',
  'rname' => 'name',
  'db_concat_fields' => 
  array (
    0 => 'first_name',
    1 => 'last_name',
  ),
);
\$dictionary["Testrel"]["fields"]["t_person_testrelst_person_ida"] = array (
  'name' => 't_person_testrelst_person_ida',
  'type' => 'link',
  'relationship' => 't_person_testrels',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_T_PERSON_TESTRELS_FROM_TESTRELS_TITLE',
);            
EOF;

        file_put_contents($testFile,$badExample);
        
        upgrade_custom_relationships();

        require($testFile);
        $this->assertTrue(isset($dictionary['Testrel']['fields']['t_person_testrelst_person_ida']));
        $this->assertEquals('id',$dictionary['Testrel']['fields']['t_person_testrelst_person_ida']['type']);
    }

    public function testBadSelfOneToMany()
    {
        $testFile = 'custom/Extension/modules/unittest_rel/Ext/Vardefs/bad_relate_example.php';
        $testLayout = 'custom/Extension/modules/unittest_rel/Ext/Layoutdefs/t_bas_t_bas_1_t_bas.php';
        $testMetaData = 'custom/metadata/t_bas_t_bas_1MetaData.php';

        $badExample = <<<EOF
<?php
// created: 2013-01-17 15:47:11
\$dictionary["t_bas"]["fields"]["t_bas_t_bas_1"] = array (
  'name' => 't_bas_t_bas_1',
  'type' => 'link',
  'relationship' => 't_bas_t_bas_1',
  'source' => 'non-db',
  'vname' => 'LBL_T_BAS_T_BAS_1_FROM_T_BAS_L_TITLE',
  'id_name' => 't_bas_t_bas_1t_bas_ida',
);
\$dictionary["t_bas"]["fields"]["t_bas_t_bas_1_name"] = array (
  'name' => 't_bas_t_bas_1_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_T_BAS_T_BAS_1_FROM_T_BAS_L_TITLE',
  'save' => true,
  'id_name' => 't_bas_t_bas_1t_bas_ida',
  'link' => 't_bas_t_bas_1',
  'table' => 't_bas',
  'module' => 't_bas',
  'rname' => 'name',
);
\$dictionary["t_bas"]["fields"]["t_bas_t_bas_1t_bas_ida"] = array (
  'name' => 't_bas_t_bas_1t_bas_ida',
  'type' => 'link',
  'relationship' => 't_bas_t_bas_1',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_T_BAS_T_BAS_1_FROM_T_BAS_R_TITLE',
);
EOF;
        file_put_contents($testFile,$badExample);

        $badLayout = <<<EOF
<?php
 // created: 2013-01-17 15:47:11
\$layout_defs["t_bas"]["subpanel_setup"]['t_bas_t_bas_1t_bas_ida'] = array (
  'order' => 100,
  'module' => 't_bas',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_T_BAS_T_BAS_1_FROM_T_BAS_R_TITLE',
  'get_subpanel_data' => 't_bas_t_bas_1t_bas_ida',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);
EOF;
        file_put_contents($testLayout,$badLayout);

        $badMetaData = <<<EOF
<?php
// created: 2013-02-05 11:32:02
\$dictionary["t_bas_t_bas_1"] = array (
  'true_relationship_type' => 'one-to-many',
  'relationships' => 
  array (
    't_bas_t_bas_1' => 
    array (
      'lhs_module' => 't_bas',
      'lhs_table' => 't_bas',
      'lhs_key' => 'id',
      'rhs_module' => 't_bas',
      'rhs_table' => 't_bas',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 't_bas_t_bas_1_c',
      'join_key_lhs' => 't_bas_t_bas_1t_bas_ida',
      'join_key_rhs' => 't_bas_t_bas_1t_bas_idb',
    ),
  ),
  'table' => 't_bas_t_bas_1_c',
  'fields' => 
  array (
    0 => 
    array (
      'name' => 'id',
      'type' => 'varchar',
      'len' => 36,
    ),
    1 => 
    array (
      'name' => 'date_modified',
      'type' => 'datetime',
    ),
    2 => 
    array (
      'name' => 'deleted',
      'type' => 'bool',
      'len' => '1',
      'default' => '0',
      'required' => true,
    ),
    3 => 
    array (
      'name' => 't_bas_t_bas_1t_bas_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 't_bas_t_bas_1t_bas_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 't_bas_t_bas_1spk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 't_bas_t_bas_1_ida1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 't_bas_t_bas_1t_bas_ida',
      ),
    ),
    2 => 
    array (
      'name' => 't_bas_t_bas_1_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 't_bas_t_bas_1t_bas_idb',
      ),
    ),
  ),
);
EOF;

        file_put_contents($testMetaData,$badMetaData);        

        upgrade_custom_relationships();

        require($testFile);
        $this->assertTrue(isset($dictionary['t_bas']['fields']['t_bas_t_bas_1t_bas_ida']),
                          "I loaded the correct field definition");
        $this->assertTrue(isset($dictionary['t_bas']['fields']['t_bas_t_bas_1_right']),
                          "I did not create the right hand side of the relationship");
        $this->assertEquals('id',$dictionary['t_bas']['fields']['t_bas_t_bas_1t_bas_ida']['type'],
                            "The field type was correctly updated.");
        $this->assertEquals('t_bas_t_bas_1',$dictionary['t_bas']['fields']['t_bas_t_bas_1t_bas_ida']['link'],
                            "The id field link was not set correctly");
        $this->assertEquals('t_bas_t_bas_1',$dictionary['t_bas']['fields']['t_bas_t_bas_1_name']['link'],
                            "The name field link was not set correctly");

        require($testLayout);
        $this->assertTrue(isset($layout_defs['t_bas']['subpanel_setup']['t_bas_t_bas_1']),
            "The layout index has been correctly changed");
        $this->assertEquals(
            't_bas_t_bas_1_right',
            $layout_defs['t_bas']['subpanel_setup']['t_bas_t_bas_1']['get_subpanel_data'],
            "The get_subpanel_data has been properly adjusted."
        );
    }
}
