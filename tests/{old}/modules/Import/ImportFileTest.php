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


class ImportFileTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $unlink = array();

	public function setUp()
	{
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestImportUtilities::removeAllCreatedFiles();
        foreach($this->unlink as $file) {
            @unlink($file);
        }
        SugarTestHelper::tearDown();
    }

    /**
 	 * @ticket 23380
 	 */
	public function testFileImportNoEnclosers()
    {
    	$file = SugarTestImportUtilities::createFile(2,1);
    	$importFile = new ImportFile($file,',','', TRUE, FALSE);
        $row = $importFile->getNextRow();
        $this->assertEquals($row, array('foo00'));
        $row = $importFile->getNextRow();
        $this->assertEquals($row,array('foo10'));
    }

    public function testLoadNonExistantFile()
    {
        $importFile = new ImportFile(ImportCacheFiles::getImportDir().'/thisfileisntthere'.date("YmdHis").'.csv',',','"');
        $this->assertFalse($importFile->fileExists());
    }

    public function testLoadGoodFile()
    {
        $file = SugarTestImportUtilities::createFile(2,1);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);
        $this->assertTrue($importFile->fileExists());
    }

    /**
     * @ticket 39494
     */
    public function testLoadFileWithByteOrderMark()
    {
        $sample_file = 'upload://Bug39494ImportFile.txt';
        copy('tests/{old}/modules/Import/Bug39494ImportFile.txt', $sample_file);
        $this->unlink[] = $sample_file;
        $importFile = new ImportFile($sample_file,"\t",'',false);
        $this->assertTrue($importFile->fileExists());
        $row = $importFile->getNextRow();
        $this->assertEquals($row,array('name','city'));
        $row = $importFile->getNextRow();
        $this->assertEquals($row,array('tester1','wuhan'));
    }

    public function testGetNextRow()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);

        $row = $importFile->getNextRow();
        $this->assertEquals(array("foo00","foo01"),$row);
        $row = $importFile->getNextRow();
        $this->assertEquals(array("foo10","foo11"),$row);
        $row = $importFile->getNextRow();
        $this->assertEquals(array("foo20","foo21"),$row);
    }

    /**
 	 * @ticket 41361
 	 */
    public function testGetNextRowWithEOL()
    {
        $file = SugarTestImportUtilities::createFileWithEOL(1, 1);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);
        $row = $importFile->getNextRow();
        // both \r\n and \n should be properly replaced with PHP_EOL
        $this->assertEquals(array("start0".PHP_EOL."0".PHP_EOL."end"), $row);
    }

    public function testLoadEmptyFile()
    {
        $emptyFile = ImportCacheFiles::getImportDir().'/empty'.date("YmdHis").'.csv';
        file_put_contents($emptyFile,'');
        $this->unlink[] = $emptyFile;

        $importFile = new ImportFile($emptyFile,',','"',false);

        $this->assertFalse($importFile->getNextRow());

        $importFile = new ImportFile($emptyFile,',','',false);

        $this->assertFalse($importFile->getNextRow());
    }

    public function testDeleteFileOnDestroy()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"',true, FALSE);

        unset($importFile);

        $this->assertFalse(is_file($file));
    }

    public function testNotDeleteFileOnDestroy()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"',false);

        unset($importFile);

        $this->assertTrue(is_file($file));
    }

    public function testGetFieldCount()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"',TRUE, FALSE);

        $importFile->getNextRow();
        $this->assertEquals(2,$importFile->getFieldCount());
    }

    public function testMarkRowAsDuplicate()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);

        $row = $importFile->getNextRow();
        $importFile->markRowAsDuplicate();

        $fp = sugar_fopen(ImportCacheFiles::getDuplicateFileName(),'r');
        $duperow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals($row,$duperow);
    }

    public function testWriteError()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);

        $row = $importFile->getNextRow();
        $importFile->writeError('Some Error','field1','foo');

        $fp = sugar_fopen(ImportCacheFiles::getErrorFileName(),'r');
        $errorrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals(array('Some Error','field1','foo',1),$errorrow);

        $fp = sugar_fopen(ImportCacheFiles::getErrorRecordsWithoutErrorFileName(),'r');
        $errorrecordrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals($row,$errorrecordrow);
    }

    public function testWriteErrorRecord()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);

        $row = $importFile->getNextRow();
        $importFile->writeErrorRecord();

        $fp = sugar_fopen(ImportCacheFiles::getErrorRecordsWithoutErrorFileName(),'r');
        $errorrecordrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals($row,$errorrecordrow);
    }

    public function testWriteStatus()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);

        $importFile->getNextRow();
        $importFile->writeError('Some Error','field1','foo');
        $importFile->getNextRow();
        $importFile->markRowAsDuplicate();
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->writeStatus();

        $fp = sugar_fopen(ImportCacheFiles::getStatusFileName(),'r');
        $statusrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals(array(3,1,1,1,0,$file),$statusrow);
    }

    public function testWriteStatusWithTwoErrorsInOneRow()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);

        $row = $importFile->getNextRow();
        $importFile->writeError('Some Error','field1','foo');
        $importFile->writeError('Some Error','field1','foo');
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->writeStatus();

        $fp = sugar_fopen(ImportCacheFiles::getStatusFileName(),'r');
        $statusrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals(array(3,1,0,2,0,$file),$statusrow);

        $fp = sugar_fopen(ImportCacheFiles::getErrorRecordsWithoutErrorFileName(),'r');
        $errorrecordrow = fgetcsv($fp);

        $this->assertEquals($row,$errorrecordrow);

        $this->assertFalse(fgetcsv($fp),'Should be only 1 record in the csv file');
        fclose($fp);

    }

    public function testWriteStatusWithTwoUpdatedRecords()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"', TRUE, FALSE);

        $row = $importFile->getNextRow();
        $importFile->markRowAsImported(false);
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->writeStatus();

        $fp = sugar_fopen(ImportCacheFiles::getStatusFileName(),'r');
        $statusrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals(array(3,0,0,2,1,$file),$statusrow);
    }

    public function testWriteRowToLastImport()
    {
        $file = SugarTestImportUtilities::createFile(3,2);
        $importFile = new ImportFile($file,',','"');
        $record = $importFile->writeRowToLastImport("Tests","Test","TestRunner");

        $query = "SELECT *
                        FROM users_last_import
                        WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'
                            AND import_module = 'Tests'
                            AND bean_type = 'Test'
                            AND bean_id = 'TestRunner'
                            AND id = '$record'
                            AND deleted=0";

		$result = $GLOBALS['db']->query($query);

        $this->assertNotNull($GLOBALS['db']->fetchByAssoc($result));

        $query = "DELETE FROM users_last_import
                        WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'
                            AND import_module = 'Tests'
                            AND bean_type = 'Test'
                            AND bean_id = 'TestRunner'
                            AND id = '$record'
                            AND deleted=0";
        $GLOBALS['db']->query($query);
    }

    public function providerEncodingData()
    {
        return array(
            array('TestCharset.csv', 'UTF-8'),
            array('TestCharset2.csv', 'ISO-8859-1'),
            );
    }

    /**
     * @dataProvider providerEncodingData
     */
    public function testCharsetDetection($file, $encoding) {
        // create the test file
        $sample_file = $GLOBALS['sugar_config']['upload_dir'].'/'.$file;
        copy('tests/{old}/modules/Import/'.$file, $sample_file);
        $this->unlink[] = $sample_file;

        // auto detect charset
        $importFile = new ImportFile($sample_file, ",", '', false, false);
        $this->assertTrue($importFile->fileExists());
        $charset = $importFile->autoDetectCharacterSet();
        $this->assertEquals($encoding, $charset, 'detected char encoding is incorrect.');
    }

    public function providerRowCountData()
    {
        return array(
            array('TestCharset.csv', 2, false),
            array('TestCharset2.csv', 11, true),
            array('TestCharset2.csv', 12, false),
            );
    }

    /**
     * @dataProvider providerRowCountData
     */
    public function testRowCount($file, $count, $hasHeader) {
        // create the test file
        $sample_file = $GLOBALS['sugar_config']['upload_dir'].'/'.$file;
        copy('tests/{old}/modules/Import/'.$file, $sample_file);
        $this->unlink[] = $sample_file;

        $importFile = new ImportFile($sample_file, ",", '', false, false);
        $this->assertTrue($importFile->fileExists());
        $importFile->setHeaderRow($hasHeader);
        $c = $importFile->getTotalRecordCount();
        $this->assertEquals($count, $c, 'incorrect row count.');
    }

    public function providerFieldCountData()
    {
        return array(
            array('TestCharset.csv', 2),
            array('TestCharset2.csv', 5),
            );
    }

    /**
     * @dataProvider providerFieldCountData
     */
    public function testFieldCount($file, $count) {
        // create the test file
        $sample_file = $GLOBALS['sugar_config']['upload_dir'].'/'.$file;
        copy('tests/{old}/modules/Import/'.$file, $sample_file);
        $this->unlink[] = $sample_file;

        $importFile = new ImportFile($sample_file, ",", '"', false, false);
        $this->assertTrue($importFile->fileExists());
        $c = $importFile->getNextRow();
        $c = $importFile->getFieldCount();
        $this->assertEquals($count, $c, 'incorrect row count.');
    }

    public function providerLineCountData()
    {
        return array(
            array('TestCharset.csv', 2),
            array('TestCharset2.csv', 12),
            );
    }

    /**
     * @dataProvider providerLineCountData
     */
    public function testLineCount($file, $count) {
        // create the test file
        $sample_file = $GLOBALS['sugar_config']['upload_dir'].'/'.$file;
        copy('tests/{old}/modules/Import/'.$file, $sample_file);
        $this->unlink[] = $sample_file;

        $importFile = new ImportFile($sample_file, ",", '"', false, false);
        $this->assertTrue($importFile->fileExists());
        $c = $importFile->getNumberOfLinesInfile();
        $this->assertEquals($count, $c, 'incorrect row count.');
    }

    public function providerDateFormatData()
    {
        return array(
            array('TestCharset.csv', 'd/m/Y'),
            array('TestCharset2.csv', 'm/d/Y'),
            );
    }

    /**
     * @dataProvider providerDateFormatData
     */
    public function testDateFormat($file, $format) {
        // create the test file
        $sample_file = $GLOBALS['sugar_config']['upload_dir'].'/'.$file;
        copy('tests/{old}/modules/Import/'.$file, $sample_file);
        $this->unlink[] = $sample_file;

        $importFile = new ImportFile($sample_file, ",", '"', false, false);
        $this->assertTrue($importFile->fileExists());
        $ret = $importFile->autoDetectCSVProperties();
        $this->assertTrue($ret, 'Failed to auto detect properties.');
        $c = $importFile->getDateFormat();
        $this->assertEquals($format, $c, 'incorrect date format.');
    }

    public function providerTimeFormatData()
    {
        return array(
            array('TestCharset.csv', 'h:ia'),
            array('TestCharset2.csv', 'H:i'),
            );
    }

    /**
     * @dataProvider providerTimeFormatData
     */
    public function testTimeFormat($file, $format) {
        // create the test file
        $sample_file = $GLOBALS['sugar_config']['upload_dir'].'/'.$file;
        copy('tests/{old}/modules/Import/'.$file, $sample_file);
        $this->unlink[] = $sample_file;

        $importFile = new ImportFile($sample_file, ",", '"', false, false);
        $this->assertTrue($importFile->fileExists());
        $ret = $importFile->autoDetectCSVProperties();
        $this->assertTrue($ret, 'Failed to auto detect properties.');
        $c = $importFile->getTimeFormat();
        $this->assertEquals($format, $c, 'incorrect time format.');
    }

    /**
     * @ticket 48289
     */
    public function testTabDelimiter() {
        // create the test file
        $sample_file = $GLOBALS['sugar_config']['upload_dir'].'/TestCharset.csv';
        copy('tests/{old}/modules/Import/TestCharset.csv', $sample_file);

        // use '\t' to simulate the bug
        $importFile = new ImportFile($sample_file, '\t', '"', false, false);
        $this->assertTrue($importFile->fileExists());
        $c = $importFile->getNextRow();
        $this->assertTrue(is_array($c), 'incorrect return type.');
        $this->assertEquals(1, count($c), 'incorrect array count.');

        // cleanup
        $this->unlink[] = $sample_file;
    }
}
