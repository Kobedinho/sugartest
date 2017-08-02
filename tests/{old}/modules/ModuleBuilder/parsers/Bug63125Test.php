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
 * @group 63125
 */
class Bug63125Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testValidDef($studio, $expected)
    {
        $actual = AbstractMetaDataParser::validField(
            array(
                'studio' => $studio,
            )
        );

        $this->assertEquals($expected, $actual);
    }

    public static function provider()
    {
        return array(
            array(true, true),
            array('true', true),
            array(false, false),
            array('false', false),
            array('hidden', false),
            array('any-unknown-value', true),
        );
    }
}
