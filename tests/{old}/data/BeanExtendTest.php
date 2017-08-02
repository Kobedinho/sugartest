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


class BeanExtendTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass()
    {
	    SugarTestHelper::tearDown();
	}

	public function testBeans()
	{
	    for($i=1;$i<=8;$i++) {
	        $name = "TestBean$i";
	        $bean = new $name;
	        $this->assertTrue($bean->ok);
	    }
	}
}

class TestBean1 extends SugarBean
{
    public $ok;
    public function __construct()
    {
		parent::__construct();
		$this->ok = true;
	}
}

class TestBean2 extends SugarBean
{
    public $ok;
    public function __construct()
    {
        parent::__construct();
		$this->ok = true;
    }
}

class TestBean3 extends SugarBean
{
    public $ok;
    function __construct() {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean4 extends SugarBean
{
    public $ok;
    function __construct() {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean5 extends Basic
{
    public $ok;
    public function __construct()
    {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean6 extends Basic
{
    public $ok;
    public function __construct()
    {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean7 extends Basic
{
    public $ok;
    function __construct() {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean8 extends Basic
{
    public $ok;
    function __construct() {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean9 extends Basic
{
    public $ok;

    function __construct() {
        parent::__construct();
        $this->ok = true;
    }

}

class TestBean10 extends TestBean9
{
    public $ok;

    function __construct() {
        parent::__construct();
        $this->ok = true;
    }
}



