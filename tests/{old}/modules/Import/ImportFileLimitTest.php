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


class ImportFileLimitTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_fileSample1;
    protected $_fileSample2;
    protected $_fileSample3;
    protected $_fileSample4;

    protected $_fileLineCount1 = 555;
    protected $_fileLineCount2 = 111;
    protected $_fileLineCount3 = 2;
    protected $_fileLineCount4 = 0;

    public function setUp()
    {
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_fileSample1 = SugarTestImportUtilities::createFile( $this->_fileLineCount1, 3 );
        $this->_fileSample2 = SugarTestImportUtilities::createFile( $this->_fileLineCount2, 3 );
        $this->_fileSample3 = SugarTestImportUtilities::createFile( $this->_fileLineCount3, 3 );
        $this->_fileSample4 = SugarTestImportUtilities::createFile( $this->_fileLineCount4, 3 );
    }

    public function tearDown()
    {
        SugarTestImportUtilities::removeAllCreatedFiles();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testGetFileRowCount()
    {
        $if1 = new ImportFile($this->_fileSample1, ',', "\"", FALSE);
        $if2 = new ImportFile($this->_fileSample2, ',', "\"", FALSE);
        $if3 = new ImportFile($this->_fileSample3, ',', "\"", FALSE);
        $if4 = new ImportFile($this->_fileSample4, ',', "\"", FALSE);

        $this->assertEquals($this->_fileLineCount1, $if1->getNumberOfLinesInfile() );
        $this->assertEquals($this->_fileLineCount2, $if2->getNumberOfLinesInfile() );
        $this->assertEquals($this->_fileLineCount3, $if3->getNumberOfLinesInfile() );
        $this->assertEquals($this->_fileLineCount4, $if4->getNumberOfLinesInfile() );
    }
}

