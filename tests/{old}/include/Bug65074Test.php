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

require_once('include/utils.php');

function testFuncString()
{
    return 'func string';
}

function testFuncArgs($args)
{
    return $args;
}

class testBeanParam
{
    public function testFuncBean()
    {
        return 'func bean';
    }
}

/**
 * @ticket 65074
 */
class Bug65074Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $customIncludeDir = 'custom/include';
    protected $customIncludeFile = 'bug65074_include.php';

    public function setUp()
    {

        // create a custom include file
        $customIncludeFileContent = <<<EOQ
<?php
function testFuncInclude()
{
        return 'func include';
}
EOQ;
        if (!file_exists($this->customIncludeDir)) {
            sugar_mkdir($this->customIncludeDir, 0777, true);
        }

        SugarAutoLoader::put($this->customIncludeDir . '/' . $this->customIncludeFile, $customIncludeFileContent, true);
    }

    public function tearDown()
    {
        // remove the custom include file
        if (file_exists($this->customIncludeDir . '/' . $this->customIncludeFile)) {
            SugarAutoLoader::unlink($this->customIncludeDir . '/' . $this->customIncludeFile, true);
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testGetFunctionValue
     */
    public function dataProviderForTestGetFunctionValue()
    {
        return array(
                array(null, 'testFuncString', array(), 'func string'),
                array(null, 'testFuncArgs', array('func args'), 'func args'),
                array(new testBeanParam(), 'testFuncBean', array(), 'func bean'),
                array('', array('name'=>'testFuncInclude', 'include'=>$this->customIncludeDir . '/' . $this->customIncludeFile), array(), 'func include')
        );
    }

    /**
     * Tests function getFunctionValue()
     * @dataProvider dataProviderForTestGetFunctionValue
     */
    public function testGetFunctionValue($bean, $function, $args, $value)
    {
        $this->assertEquals($value, getFunctionValue($bean, $function, $args), 'Function getFunctionValue() returned wrong result.');
    }
}
