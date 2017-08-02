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

class Bug53002Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;

    public function setUp()
    {
        if(empty($this->_db)){
            $this->_db = DBManagerFactory::getInstance();
        }
    }

    public function tearDown()
    {
    }

    public function test_order_by_amount()
    {
        $query = "SELECT * FROM opportunities ORDER BY amount ASC";
        $this->_db->query($query);
        // and make no error messages are asserted
        $this->assertEmpty($this->_db->lastError(), "lastError should return false of the last legal query that is ordering by amount against opportunities");

        $query = "SELECT * FROM opportunities ORDER BY amount_usdollar ASC";
        $this->_db->query($query);
        // and make no error messages are asserted
        $this->assertEmpty($this->_db->lastError(), "lastError should return false of the last legal query that is ordering by amount_usdollar against opportunities");
    }
}
