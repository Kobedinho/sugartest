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
 * @covers SidecarSubpanelLayoutMetaDataParser
 */
class SidecarSubpanelLayoutMetaDataParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider relationshipFieldsProvider
     */
    public function testRelationshipFields($moduleName, $subPanelName, $field, $expected)
    {
        $parser = new SidecarSubpanelLayoutMetaDataParser($subPanelName, $moduleName);
        $this->assertArrayHasKey($field, $parser->_fielddefs);
        $isValid = $parser->isValidField($field, $parser->_fielddefs[$field]);
        $this->assertEquals($expected, $isValid);
    }

    public static function relationshipFieldsProvider()
    {
        return array(
            array(
                'Opportunities',
                'contacts',
                'opportunity_role',
                true,
            ),
            array(
                'Accounts',
                'contacts',
                'opportunity_role',
                false,
            ),
            array(
                'Accounts',
                'opportunities',
                'contact_role',
                false,
            ),
        );
    }
}
