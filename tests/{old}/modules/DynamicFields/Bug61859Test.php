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


class Bug61859Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $dynamicFields;

    /**
     * @group 61859
     */
    public function testFieldExists()
    {
        $this->assertFalse($this->dynamicFields->fieldExists('contact'));
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('dictionary');

        $leadBean = $bean = BeanFactory::newBean('Leads');
        $this->dynamicFields = new DynamicField('Leads');
        $this->dynamicFields->setup($leadBean);

        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

}
