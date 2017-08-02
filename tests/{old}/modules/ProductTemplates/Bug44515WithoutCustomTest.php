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

require_once "modules/ProductTemplates/Formulas.php";

class Bug44515WithoutCustomTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        
    }


    public function tearDown()
    {
    }

    /**
     * @group 44515
     */
    public function testLoadCustomFormulas()
    {
      refresh_price_formulas();
      // At this point I expect to have only the 5 standard formulas
      $expectedIndexes = 5;
      $this->assertEquals($expectedIndexes, count($GLOBALS['price_formulas']));
    }
}

