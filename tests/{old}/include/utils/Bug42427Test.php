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
 * @ticket 42427
 */
class Bug42427Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
        sugar_cache_clear('app_list_strings.de_test');

        if ( isset($sugar_config['default_language']) ) {
            $this->_backup_default_language = $sugar_config['default_language'];
        }
    }

    public function tearDown()
    {
        unlink('include/language/fr_test.lang.php');
        SugarAutoLoader::delFromMap('include/language/fr_test.lang.php', false);
        unlink('include/language/de_test.lang.php');
        SugarAutoLoader::delFromMap('include/language/de_test.lang.php', false);

        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
        sugar_cache_clear('app_list_strings.de_test');

        if ( isset($this->_backup_default_language) ) {
            $sugar_config['default_language'] = $this->_backup_default_language;
        }
    }

    public function testWillLoadEnUsStringIfDefaultLanguageIsNotEnUs()
    {
        file_put_contents('include/language/fr_test.lang.php', '<?php $app_list_strings = array(); ?>');
        SugarAutoLoader::addToMap('include/language/fr_test.lang.php', false);
        file_put_contents('include/language/de_test.lang.php', '<?php $app_list_strings = array(); ?>');
        SugarAutoLoader::addToMap('include/language/de_test.lang.php', false);

        $sugar_config['default_language'] = 'fr_test';

        $strings = return_app_list_strings_language('de_test');

        $this->assertArrayHasKey('lead_source_default_key',$strings);
    }
}
