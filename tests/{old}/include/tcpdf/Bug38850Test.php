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
 
require_once("include/Sugarpdf/sugarpdf_config.php");
require_once('vendor/tcpdf/config/lang/eng.php');
require_once('vendor/tcpdf/tcpdf.php');
/**
 * @ticket 38850
 */
class Bug38850Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testCanInterjectCodeInTcpdfTag()
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $dom = array(
            0 => array(
                'value' => 'html'
                ),
            1 => array(
                'parent' => 0,
                'value' => 'tcpdf',
                'attribute' => array(
                    'method' => 'Close',
                    'params' => serialize(array(");echo ('Can Interject Code'")),
                    ),
                ),
            );

        SugarTestReflection::callProtectedMethod($pdf, 'openHTMLTagHandler', array(&$dom, 1));
        $this->expectOutputNotRegex('/Can Interject Code/');
    }
}
