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
 * Test if non-primary emails are being imported properly from a CSV file
 * on Accounts module, or modules based on Person
 */
class ImportEmailsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $importObject;
    private $file;
    private $cleanId;
    private $emails = array();

    public function setUp()
    {
        $this->file = dirname(__FILE__) . '/Bug25736Test.csv';

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $_REQUEST['importlocale_charset'] = 'UTF-8';
        $_REQUEST['importlocale_dateformat'] = "m/d/Y";
        $_REQUEST['importlocale_timeformat'] = "h:i a";
        $_REQUEST['importlocale_timezone'] = 'GMT';
        $_REQUEST['importlocale_default_currency_significant_digits'] = '2';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_dec_sep'] = '.';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_default_locale_name_format'] = 's f l';
        $_REQUEST['importlocale_num_grp_sep'] = ',';
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM email_addr_bean_rel WHERE bean_id = '{$this->cleanId}' AND bean_module = '{$this->importObject->module_dir}'");
        $GLOBALS['db']->query("DELETE FROM email_addresses WHERE email_address IN ('" . implode("', '", $this->emails) . "')");
        $GLOBALS['db']->query("DELETE FROM {$this->importObject->table_name} WHERE created_by = '{$GLOBALS['current_user']->id}'");

        $_REQUEST = array();

        SugarTestHelper::tearDown();
    }

    /**
     * Check if semi-colon separated non-primary mails
     * are being imported properly
     *
     * @dataProvider providerEmailImport
     */
    public function testEmailImport($module, $nameField, $name, $csvData, $expected)
    {
        $fileCreated = sugar_file_put_contents($this->file, $csvData);
        $this->assertGreaterThan(0, $fileCreated, 'Failed to write to ' . $this->file);

        // Create the ImportFile the Importer uses from our CSV
        $importSource = new ImportFile($this->file, ',', '"');

        // Create the bean type we're importing
        $this->importObject = $bean = new $module;

        // Setup needed $_REQUEST data
        $_REQUEST['columncount'] = 2;
        $_REQUEST['colnum_0'] = $nameField;
        $_REQUEST['colnum_1'] = 'email_addresses_non_primary';
        $_REQUEST['import_module'] = $bean->module_dir;

        // Create the Importer and try importing
        $importer = new Importer($importSource, $bean);
        $importer->import();

        // Check if the Bean is created
        $query = "SELECT id FROM $bean->table_name WHERE $nameField = '$name'";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertNotEmpty($row['id'], $module . ' not created');
        // Save Bean id for easier cleanup after test
        $this->cleanId = $row['id'];

        // Check if all of the mails got created and linked properly
        foreach ($expected as $email) {
            list($address, $invalid, $optOut) = $email;
            $this->emails[] = $address;
            // Check if the mail got created
            $query = "SELECT id, invalid_email, opt_out FROM email_addresses WHERE email_address = '$address'";
            $result = $GLOBALS['db']->query($query);
            $row = $GLOBALS['db']->fetchByAssoc($result);

            $this->assertNotEmpty($row, 'Mail not created');
            $this->assertEquals($invalid, $row['invalid_email'], 'Incorrect "invalid" attribute value');
            $this->assertEquals($optOut, $row['opt_out'], 'Incorrect "opt out" attribute value');
            $mailId = $row['id'];

            // Check if the mail is linked
            $query = "SELECT id FROM email_addr_bean_rel WHERE email_address_id = '$mailId' AND bean_module = '$bean->module_dir' AND deleted = 0 AND primary_address = 0";
            $result = $GLOBALS['db']->query($query);
            $row = $GLOBALS['db']->fetchByAssoc($result);

            $this->assertNotEmpty($row, 'Mail not linked');
        }
    }

    public function providerEmailImport()
    {
        $modules = array(
            'Account' => 'name',
            'Contact' => 'last_name',
            'Lead' => 'last_name',
            'Prospect' => 'last_name',
        );

        // keys are CSV values, values are resulting emails and their attributes
        $emails = array(
            // attributes are explicitly specified and imported
            '"testmail1@test.com,0,1;testmail2@test.com,1,0"' => array(
                array('testmail1@test.com', 0, 1),
                array('testmail2@test.com', 1, 0),
            ),
            // attributes are omitted and set to default values
            '"testmail3@test.com;testmail4@test.com"' => array(
                array('testmail3@test.com', 0, 0),
                array('testmail4@test.com', 0, 0),
            ),
        );

        $data = array();
        foreach ($modules as $module => $nameField) {
            foreach ($emails as $csvData => $expected) {
                $data[] = array(
                    $module,
                    $nameField,
                    'Random Guy',
                    array(
                        '"Random Guy",' . $csvData,
                    ),
                    $expected,
                );
            }
        }

        return $data;
    }

    /**
     * Check if emails get updated on import
     *
     * @dataProvider providerEmailUpdate
     */
    public function testEmailUpdate($module, $nameField, $name, $csvDataImport, $csvDataUpdate, $expected)
    {
        $fileCreated = sugar_file_put_contents($this->file, $csvDataImport);
        $this->assertGreaterThan(0, $fileCreated, 'Failed to write to ' . $this->file);

        // Create the ImportFile the Importer uses from our CSV
        $importSource = new ImportFile($this->file, ',', '"');

        // Create the bean type we're importing
        $this->importObject = $bean = new $module;

        // Setup needed $_REQUEST data
        $_REQUEST['columncount'] = 3;
        $_REQUEST['colnum_0'] = 'id';
        $_REQUEST['colnum_1'] = $nameField;
        $_REQUEST['colnum_2'] = 'email';
        $_REQUEST['import_module'] = $bean->module_dir;

        // Create the Importer and try importing
        $importer = new Importer($importSource, $bean);
        $importer->import();

        // Check if the Bean is created
        $query = "SELECT id FROM $bean->table_name WHERE $nameField = '$name'";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertNotEmpty($row['id'], $module . ' not created');
        // Save Bean id for easier cleanup after test
        $this->cleanId = $row['id'];

        // Now update
        $_REQUEST['import_type'] = 'update';
        $fileCreated = sugar_file_put_contents($this->file, $csvDataUpdate);
        $this->assertGreaterThan(0, $fileCreated, 'Failed to write to ' . $this->file);

        // Create the bean type we're importing
        $this->importObject = $bean = new $module;

        // Create the Importer and try importing
        $importer = new Importer($importSource, $bean);
        $importer->import();

        $this->emails[] = $expected;
        $bean->retrieve($this->cleanId);
        $this->assertEquals($expected, $bean->email[0]['email_address']);

    }

    /**
     * @return array The import data provided for the test.
     *   Generates data for different modules
     *   Each row has a csv string with columns: module, required name field,
     *   name field vlaue, CSV data to create a record, CSV data to update the created record,
     *   e-mail the updated record should contain
     */
    public function providerEmailUpdate()
    {
        $modules = array(
            'Account' => 'name',
            'Contact' => 'last_name',
            'Lead' => 'last_name',
            'Prospect' => 'last_name',
        );

        $data = array();
        foreach ($modules as $module => $nameField) {
            $data[] = array(
                $module,
                $nameField,
                'Random Guy',
                array(
                    '"import_email_update","Random Guy","old.primary@email.com"',
                ),
                array(
                    '"import_email_update","Random Guy","new.primary@email.com"',
                ),
                'new.primary@email.com',
            );
        }

        return $data;
    }
}
