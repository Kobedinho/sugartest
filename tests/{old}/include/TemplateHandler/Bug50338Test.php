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

 
class Bug50338Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $TemplateHandler;
    private $fieldDefs;

    function testCreateFieldFefs()
    {
        $this->TemplateHandler = new MockTemplateHandler ();
        $this->fieldDefs = array(
                             'amount' => Array (
                                          'calculated' => true,
                                          'formula' => 'add($calc1_c, $calc2_c)',
                                        ),
                             'calc1_c' => Array (
                                        'id' => 'Opportunitiescalc1_c'
                                        ),
                             'calc2_c' => Array (
                                        'id' => 'Opportunitiescalc2_c'
                                        ),
                           );
        $fieldDefs = $this->TemplateHandler->mockPrepareCalculationFields($this->fieldDefs, 'Opportunities');
        $this->assertArrayHasKey('Opportunitiesamount', $fieldDefs);
        $this->assertContains('Opportunitiescalc1_c', $fieldDefs['Opportunitiesamount']['formula']);
        $this->assertContains('Opportunitiescalc2_c', $fieldDefs['Opportunitiesamount']['formula']);
        unset($this->TemplateHandler);
        unset($this->fieldDefs);
    }
}

class MockTemplateHandler extends TemplateHandler
{
    public function mockPrepareCalculationFields($fieldDefs, $module)
    {
        return $this->prepareCalculationFields($fieldDefs, $module);
    }
}
