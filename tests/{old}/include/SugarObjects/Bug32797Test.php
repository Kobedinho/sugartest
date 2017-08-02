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
 * @group bug32797
 */
class Bug32797Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_old_sugar_config = null;

    public function setUp()
    {
        $this->_old_sugar_config = $GLOBALS['sugar_config'];
        $GLOBALS['sugar_config'] = array('require_accounts' => false);
    }

    public function tearDown()
    {
        $config = SugarConfig::getInstance();
        $config->clearCache();
        $GLOBALS['sugar_config'] = $this->_old_sugar_config;
    }

    public function vardefProvider()
    {
        return array(
            array(
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => true))),
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => false))),
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => null))),
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => true))),
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => true)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => false))),
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array())),
                array('fields' => array('account_name' => array()))
            ),
            array(
                array('fields' => array()),
                array('fields' => array())
            )
        );
    }

    /**
     * @dataProvider vardefProvider
     */
    public function testApplyGlobalAccountRequirements($vardef, $vardefToCompare)
    {
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }

    public function vardefProvider1()
    {
        return array(
            array(
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => true))),
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => true)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => false))),
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => true)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => true))),
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => true)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => false))),
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => false)))
            )
        );
    }

    /**
     * @dataProvider vardefProvider1
     */
    public function testApplyGlobalAccountRequirements1($vardef, $vardefToCompare)
    {
        $GLOBALS['sugar_config']['require_accounts'] = true;
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }

    public function vardefProvider2()
    {
        return array(
            array(
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => true))),
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => true)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => false))),
                array('fields' => array('account_name' => array('type'=> 'relate', 'required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => false))),
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => true))),
                array('fields' => array('account_name' => array('type'=> 'varchar', 'required' => true)))
            )
        );
    }

    /**
     * @dataProvider vardefProvider2
     */
    public function testApplyGlobalAccountRequirements2($vardef, $vardefToCompare)
    {
        unset($GLOBALS['sugar_config']['require_accounts']);
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }
}