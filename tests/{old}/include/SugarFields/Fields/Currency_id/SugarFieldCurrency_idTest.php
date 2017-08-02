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
 

class SugarFieldCurrency_idTest extends Sugar_PHPUnit_Framework_TestCase
{
    
     /**
     * @ticket 61047
     */
	public function testEmptyCurrencyIdField()
	{
        $field = SugarFieldHandler::getSugarField('currency_id');

        $bean = new SugarBean();
        $bean->currency_id = '';

        $emptyOutput = array();
        $service = SugarTestRestUtilities::getRestServiceMock();

        $field->apiFormatField($emptyOutput, $bean, array(), 'currency_id', array(
            'type' => 'currency_id',
            'dbType' => 'currency_id',
        ), array('currency_id'), $service);

        $filledOutput = array();
        $bean->currency_id = 'IF-YOU-LIKE-PINA-COLADAS';
        $field->apiFormatField($filledOutput, $bean, array(), 'currency_id', array(
            'type' => 'currency_id',
            'dbType' => 'currency_id',
        ), array('currency_id'), $service);

        $this->assertEquals('-99',$emptyOutput['currency_id'],"The currency id was not defaulted to -99 in the apiFormatField function");
        $this->assertEquals('IF-YOU-LIKE-PINA-COLADAS',$filledOutput['currency_id'],"The currency id was not in the apiFormatField function");
    }
}