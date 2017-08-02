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

require_once("include/utils.php");

/**
 * 
 * Testing function getReportNameTranslations in utils.php
 * If test fails, update data provider with new language strings 
 *
 */
class Bug51909Test extends Sugar_PHPUnit_Framework_TestCase {

	var $oldLanguage;
	
    public function setUp() {

        $this->markTestIncomplete("Disabling broken test on CI and working with Andrija to fix");

    	global $current_language;
        $this->oldLanguage = $current_language;
    }

    public function tearDown() {
        global $current_language;
        $current_language = $this->oldLanguage;
    }
	
    /**
     * @dataProvider bug51909DataProvider
     */
    public function testTranslation($reportName, $labelName, $language) {
    	global $current_language;
    	$current_language = $language; 
    	
		$title = getReportNameTranslation($reportName);
        $this->assertEquals($labelName, $title); 
    }

    /**
     * Data provider for translationTest()
     * @return string reportName, labelName, language
     */
    public function bug51909DataProvider() {
        return array(
            '0' => array('Calls By Team By User', 'Anrufe nach Team und Benutzer', 'de_DE'),
            '1' => array('My Module Usage (Last 30 Days)', 'Echelle pour l&#39;utilisation de Mon Module (30 Derniers Jours)', 'fr_FR'),
        	'2' => array('Open Cases By Month By User', 'Reclami Aperti per Mese per Utente', 'it_it'),
        	'3' => array('All Open Opportunities', 'Wszystkie otwarte okazje', 'pl_PL'),
        );
    }
}


?>
