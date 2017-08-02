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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/7_RepairImportMaps.php';

class RepairImportMapsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM import_maps WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'");

        SugarTestHelper::tearDown();
    }

    /**
     * @param $content - Import Map content
     * @param $expected - Repaired Import Map content
     *
     * @dataProvider dataProviderRepairImportMaps
     */
    public function testRepairImportMaps($content, $expected)
    {
        $repairImportMaps = new SugarUpgradeRepairImportMaps(null);

        $reflectionClass = new ReflectionClass(get_class($repairImportMaps));
        $reflectionMethod = $reflectionClass->getMethod('repairImportMap');
        $reflectionMethod->setAccessible(true);

        $importMap = BeanFactory::newBean('Import_1');

        $importMap->content = $content;
        $importMap->save(
            $GLOBALS['current_user']->id,
            'testRepairImportMaps',
            'test',
            'csv',
            1,
            ',',
            '"'
        );

        $reflectionMethod->invokeArgs($repairImportMaps, array($importMap->id));

        $importMap = BeanFactory::getBean('Import_1', $importMap->id);

        $this->assertEquals(
            $expected,
            html_entity_decode($importMap->content),
            'Old Import Map not upgraded.'
        );
    }

    public static function dataProviderRepairImportMaps()
    {
        return array(
            array(
                'importlocale_charset=UTF-8&importlocale_dateformat=m/d/Y&importlocale_timeformat=h:ia&importlocale_timezone=Europe/Podgorica&importlocale_currency=-99&importlocale_default_currency_significant_digits=2&importlocale_num_grp_sep=,&importlocale_dec_sep=.&importlocale_default_locale_name_format=s f l&First Name=first_name&Last Name=last_name&Title=title&Department=department&Primary Address Street=primary_address_street&Primary Address City=primary_address_city&Primary Address State=primary_address_state&Primary Address Country=primary_address_country&Status=status&Lead Source=lead_source&Converted=converted&Campaign=campaign_name&Do Not Call=do_not_call&Assigned User ID=assigned_user_id&Team ID=team_id&Team Set ID=team_set_id',
                'importlocale_charset=UTF-8,importlocale_dateformat=m/d/Y,importlocale_timeformat=h:ia,importlocale_timezone=Europe/Podgorica,importlocale_currency=-99,importlocale_default_currency_significant_digits=2,"importlocale_num_grp_sep=,",importlocale_dec_sep=.,"importlocale_default_locale_name_format=s f l","First Name=first_name","Last Name=last_name",Title=title,Department=department,"Primary Address Street=primary_address_street","Primary Address City=primary_address_city","Primary Address State=primary_address_state","Primary Address Country=primary_address_country",Status=status,"Lead Source=lead_source",Converted=converted,Campaign=campaign_name,"Do Not Call=do_not_call","Assigned User ID=assigned_user_id","Team ID=team_id","Team Set ID=team_set_id"',
            ),
        );
    }
}
