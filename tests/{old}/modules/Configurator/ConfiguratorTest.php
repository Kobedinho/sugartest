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

class ConfiguratorTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testPopulateFromPostConvertsBoolValuesFromStrings()
    {
        $_POST = array(
            'disable_export' => 'true',
            'admin_export_only' => 'false',
            'upload_dir' => 'yummy'
            );
        
    	$cfg = new Configurator();
    	
        $cfg->populateFromPost();
        
        $this->assertEquals($cfg->config['disable_export'], true);
        $this->assertEquals($cfg->config['admin_export_only'], false);
        $this->assertEquals($cfg->config['upload_dir'], 'yummy');
        
        $_POST = array();
    }
}
