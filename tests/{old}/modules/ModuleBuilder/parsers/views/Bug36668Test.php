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
 * Bug #36668
 * Name field is no longer a hyperlink after moving the field from Default to Hidden back to Default 
 * in the Studio subpanel definition for custom module
 * @ticket 36668
 */
class LinkFieldTest extends SubpanelMetaDataParser
{
    /**
     * Field defs without id_name properties were throwing errors. Adding id_name
     * here to allow tests to run around modification to the core code.
     * 
     * @var array
     */
    public $_fielddefs = array(
        'name' => array('module' => 'test', 'id_name' => 'test'),
    );
    
    function __construct()
    {
        return true;
    }
    
    function makeFieldsAsLink($defs)
    {
        return $this->makeRelateFieldsAsLink($defs);
    }
}

class Bug36668Test extends Sugar_PHPUnit_Framework_TestCase
{
    function fieldDefProvider()
    {
        return array(
            array(true, 'relate', '0'),
            array(true, 'name', '1'),
            array(false, 'name', '0'),
        );
    }

    /**
     * @dataProvider fieldDefProvider
     * @group 36668
     */
    public function testMakeRelateFieldsAsLink($flag, $type, $link)
    {
        $defs = array('name' => array('type' => $type, 'link' => $link));
        
        $lt = new LinkFieldTest();
        $newDefs = $lt->makeFieldsAsLink($defs);

        $this->assertTrue(array_key_exists('widget_class', $newDefs['name']) == $flag);
    }
}
