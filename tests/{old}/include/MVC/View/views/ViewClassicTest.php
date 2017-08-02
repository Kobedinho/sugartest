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


class ViewClassicTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testConstructor()
	{
        $view = new ViewClassic();

        $this->assertEquals('',$view->type);
	}

	public function testDisplayWithClassicView()
	{
        $view = $this->createPartialMock('ViewClassic', array('includeClassicFile'));

	    $view->module = 'testmodule'.mt_rand();
	    $view->action = 'testaction'.mt_rand();

	    sugar_mkdir("modules/{$view->module}",null,true);
	    SugarAutoLoader::touch("modules/{$view->module}/{$view->action}.php", false);

	    $return = $view->display();

	    rmdir_recursive("modules/{$view->module}");
	    SugarAutoLoader::delFromMap("modules/{$view->module}");

	    $this->assertTrue($return);
	}

	public function testDisplayWithClassicCustomView()
	{
        $view = $this->createPartialMock('ViewClassic', array('includeClassicFile'));

	    $view->module = 'testmodule'.mt_rand();
	    $view->action = 'testaction'.mt_rand();

	    sugar_mkdir("custom/modules/{$view->module}",null,true);
	    SugarAutoLoader::touch("custom/modules/{$view->module}/{$view->action}.php", false);

	    $return = $view->display();

	    rmdir_recursive("custom/modules/{$view->module}");
	    SugarAutoLoader::delFromMap("custom/modules/{$view->module}");

	    $this->assertTrue($return);
	}

	public function testDisplayWithNoClassicView()
	{
        $view = $this->createPartialMock('ViewClassic', array('includeClassicFile'));

	    $view->module = 'testmodule'.mt_rand();
	    $view->action = 'testaction'.mt_rand();

	    $this->assertFalse($view->display());
	}
}
