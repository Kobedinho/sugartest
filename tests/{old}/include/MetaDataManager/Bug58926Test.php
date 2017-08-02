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
 * Bug 58926 
 */
class Bug58926Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests if an app_list_string or app_string has special html characters in it if it will be decoded properly in the MetadataManager
     * when requested
     * 
     * @group Bug58926
     */
    public function testAppStringsWithSpecialChars()
    {

        $result = array(
                'app_list_strings' => array(
                        'moduleList' => array(
                                'Leads' => "Lead's Are Special",
                            ),
                        'moduleListSingular' => array(
                                'Leads' => "Leads' Are Special",
                            ),
                    ),
                'app_strings' => array(
                        'LBL_NEXT' => "Next's Are the worst",
                    ),
            );

        $mm = new MetaDataManagerBug58926();
        
        $test['app_list_strings']['moduleList']['Leads'] = $mm->getDecodeStrings("Lead&#39;s Are Special");
        $test['app_list_strings']['moduleListSingular']['Leads'] = $mm->getDecodeStrings("Leads&#39; Are Special");
        $test['app_strings']['LBL_NEXT'] = $mm->getDecodeStrings("Next&#39;s Are the worst");
                

        $this->assertEquals($test, $result, "Decoding did not work");
    }
}

/**
 * Accessor class to the metadatamanager to allow access to protected methods
 */
class MetaDataManagerBug58926 extends MetaDataManager
{
    public function getDecodeStrings($data)
    {
        return $this->decodeStrings($data);
    }
}