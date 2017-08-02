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

use Sugarcrm\Sugarcrm\ProcessManager;

class PMSEImporterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PMSEImporter
     */
    protected $object;
    protected $bean;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $row1 = new stdClass();
        $row2 = new stdClass();
        $row3 = new stdClass();
        $row4 = new stdClass();
        $row5 = new stdClass();
        $row1->name = "Test";
        $row2->name = "Test (1)";
        $row3->name = "Email Template";
        $row4->name = "Email Template (1)";
        $row5->name = "Email Template (2)";

        $this->object = ProcessManager\Factory::getPMSEObject('PMSEImporter');
        $this->bean = $this->createPartialMock('pmse_EmailsTemplates', array('get_full_list', 'save', 'in_save'));
        $this->bean->table_name = 'pmse_emails_templates';
        $this->bean->expects($this->any())
            ->method('get_full_list')
//            ->with($this->equalTo('Nombre'));
            ->will($this->returnValue(array(
                    "0" => $row1,
                    "1" => $row2,
                    "2" => $row3,
                    "3" => $row4,
                    "4" => $row5,
                )
            ));
        
        $this->bean->expects($this->any())
            ->method('save')
//            ->with($this->equalTo('Nombre'));
            ->will($this->returnValue('1'
            ));

        $this->object->setBean($this->bean);
        $this->object->setName('name');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers PMSEImporter::saveProjectData
     * @todo   Implement testSaveProjectData().
     */
    public function testSaveProjectData()
    {
        $GLOBALS['current_user'] = new stdClass();
        $GLOBALS['current_user']->id = '';
        $project = array("name" => "Email Templates");
        $this->bean->in_save = false;
        $result = $this->object->saveProjectData($project);
        $this->assertEquals(1,$result);

        $this->bean->in_save = true;
        $result = $this->object->saveProjectData($project);
        $this->assertFalse($result);
    }

    /**
     * @covers PMSEImporter::getNameWithSuffix
     * @todo   Implement testGetNameWithSuffix().
     */
    public function testGetNameWhitSuffix()
    {
        $names = array (
            "Test" => "Test (3)",
            "Test (1)" => "Test (1) (3)",
            "Email Template" => "Email Template (3)",
            "Email Template (1)" => "Email Template (1) (3)",
            "Email Template (2)" => "Email Template (2) (2)",
            "Emails Templates" => "Emails Templates"
        );

        foreach ($names as $key => $value) {
            $result = $this->object->getNameWithSuffix($key);
            $this->assertEquals($value, $result);
        }
    }
}
