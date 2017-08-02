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
 * Bug #44428
 * Studio | Tab Order causes layout errors
 * @ticket 44428
 */
class Bug44428Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['sugar_config']['default_language']);
    }

    public function tearDown()
    {
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    public function providerField()
    {
        return array(
            array('quote_name', '1'),
            array('opportunity_name', ''),
            array(array('name' => 'quote_num', 
                        'type' => 'readonly'), '3'),
            ); 
    }
    /**
     * @dataProvider providerField 
     * @group 44428
     */
    public function testGetNewRowItem($name, $tabindex)
    {
        $source = $name;
        $fielddef['tabindex'] = $tabindex;
        
        $glmdp = new GridLayoutMetaDataParser('editview', 'Quotes');
        $result = $glmdp->getNewRowItem($source, $fielddef);
        
        if (is_array($name))
        {
            $this->assertEquals($result['name'], $name['name']);
        }
        else
        {
            if (empty($tabindex))
            {
                $this->assertEquals($result, $name);
            }
            else
            {
                $this->assertEquals($result['name'], $name);
            }
        }
    }
}
