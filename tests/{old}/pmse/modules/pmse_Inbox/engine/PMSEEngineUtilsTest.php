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

class PMSEEngineUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PMSEEngineUtils
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = ProcessManager\Factory::getPMSEObject('PMSEEngineUtils');
        $GLOBALS['timedate'] = '';
        $_REQUEST['leads_email_widget_id'] = 2;
        $_REQUEST['leads0emailAddress0'] = 'test1@test.com';
        $_REQUEST['leads0emailAddress1'] = 'test2@test.com'; 
        $_REQUEST['leads0emailAddress2'] = 'test3@test.com';   
        $_REQUEST['leads0emailAddress3'] = '';   
        $_REQUEST['leads0emailAddress4'] = '';         
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($_REQUEST);
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestTaskUtilities::removeAllCreatedTasks();
    }

    /**
     * @covers PMSEEngineUtils::getKeyFields
     */
    public function testGetKeyFields()
    {
        $result = $this->object->getKeyFields('/_id$/', array('pro_id'=>'2', 'pro_name'=>'test'));
        $this->assertContains('pro_id', $result);
    }

    /**
     * @covers PMSEEngineUtils::sanitizeKeyFields
     */
    public function testSanitizeKeyFields()
    {
        $inputArray   = array();
        $inputArray[] = array('input' => array ('act_id'=>1, 'field_one'=>'test one', 'field_two'=>'test two' ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        $inputArray[] = array('input' => array ('evn_id'=>1, 'field_one'=>'test one', 'field_two'=>'test two', 'art_id' => 2 ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        $inputArray[] = array('input' => array ('gat_id'=>1, 'field_one'=>'test one', 'field_two'=>'test two', 'dia_id' => 3 ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        $inputArray[] = array('input' => array ('flo_id'=>1, 'field_one'=>'test one', 'field_two'=>'test two', 'prj_id' => 4 ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        foreach ($inputArray as $item) {
            $this->assertEquals($item['output'],$this->object->sanitizeKeyFields($item['input']));
        }
    }

    /**
     * @covers PMSEEngineUtils::sanitizeBoundFields
     */
    public function testSanitizeBoundFields()
    {
        $inputArray   = array();
        $inputArray[] = array('input' => array ('bou_element'=>1, 'field_one'=>'test one', 'field_two'=>'test two' ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        $inputArray[] = array('input' => array ('bou_element_type'=>1, 'field_one'=>'test one', 'field_two'=>'test two', 'bou_element' => 2 ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        $inputArray[] = array('input' => array ('bou_rel_position'=>1, 'field_one'=>'test one', 'field_two'=>'test two', 'bou_uid' => 3 ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        $inputArray[] = array('input' => array ('bou_size_identical'=>1, 'field_one'=>'test one', 'field_two'=>'test two', 'bou_rel_position' => 4 ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        $inputArray[] = array('input' => array ('bou_uid'=>1, 'field_one'=>'test one', 'field_two'=>'test two', 'bou_element_type' => 4 ), 'output' => array('field_one'=>'test one', 'field_two'=>'test two') );
        $inputArray[] = array('input' => array ('bou_element'=>1, 'bou_size_identical'=>1, 'bou_rel_position'=>1, 'bou_element_type' => 4, 'bou_uid'=>1 ), 'output' => array() );
        foreach ($inputArray as $item){
            $this->assertEquals($item['output'],$this->object->sanitizeBoundFields($item['input']));
        }
    }

    /**
     * @covers PMSEEngineUtils::sanitizeFields
     */
    public function testSanitizeFields()
    {
        $fields = array(
            'bou_element' => '123456',
            'bou_element_type' => '123456',
            'bou_rel_position' => '123456',
            'bou_size_identical' => '123456',
            'bou_uid' => '123456',
            'bou_id' => 1,
            'bou_name' => 'test'
        );
        $result = $this->object->sanitizeFields($fields);
        $this->assertEquals(array("bou_name"=>"test"), $result);
    }

    /**
     * @covers PMSEEngineUtils::generateUniqueID
     */
    public function testGenerateUniqueID()
    {
        for ($j=1;$j<=20;$j++) {
            $currentVar = $this->object->generateUniqueID();
            $this->assertEquals(32, strlen($currentVar));
        }
    }

    /**
     * @covers PMSEEngineUtils::simpleEncode
     */
    public function testSimpleEncode()
    {
        $arr = array(
            '1-1' => 'zxz',
            '2-6' => 'wxf',
            '3-5' => 'cxp',
            '4-3' => 'qxc',
            '5-1' => 'pxz'
        );
        foreach ($arr as $key => $value) {
            $result = $this->object->simpleEncode($key);
            $this->assertEquals($value, $result);
        }
    }

    /**
     * @covers PMSEEngineUtils::simpleDecode
     */
    public function testSimpleDecode()
    {
        $arr = array(
            'zxz' => '1-1',
            'wxf' => '2-6',
            'cxp' => '3-5',
            'qxc' => '4-3',
            'pxz' => '5-1'
        );
        foreach ($arr as $key => $value) {
            $result = $this->object->simpleDecode($key);
            $this->assertEquals($value, $result);
        }
    }

    /**
     * @covers PMSEEngineUtils::uploadPublicFile
     * @todo   Implement testUploadPublicFile().
     */
    public function testUploadPublicFile()
    {  
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers PMSEEngineUtils::reservedWordsSql
     */
    public function testReservedWordsSql()
    {
        $reservedWordsSqlTest = array("ACCESSIBLE", "ACTION", "ADD", "ALL", "ALTER", "ANALYZE", "AND", "ANY", "AS", "ASC", "ASENSITIVE", "AUTHORIZATION", "BACKUP", "BEFORE", "BEGIN", "BETWEEN", "BIGINT", "BINARY", "BIT", "BLOB", "BOTH", "BREAK", "BROWSE", "BULK", "BY", "CALL", "CASCADE", "CASE", "CHANGE", "CHAR", "CHARACTER", "CHECK", "CHECKPOINT", "CLOSE", "CLUSTERED", "COALESCE", "COLLATE", "COLUMN", "COMMIT", "COMPUTE", "CONDITION", "CONSTRAINT", "CONTAINS", "CONTAINSTABLE", "CONTINUE", "CONVERT", "CREATE", "CROSS", "CURRENT", "CURRENT_DATE", "CURRENT_TIME", "CURRENT_TIMESTAMP", "CURRENT_USER", "CURSOR", "DATABASE", "DATABASES", "DATE", "DAY_HOUR", "DAY_MICROSECOND", "DAY_MINUTE", "DAY_SECOND", "DBCC", "DEALLOCATE", "DEC", "DECIMAL", "DECLARE", "DEFAULT", "DELAYED", "DELETE", "DENY", "DESC", "DESCRIBE", "DETERMINISTIC", "DISK", "DISTINCT", "DISTINCTROW",
                        "DISTRIBUTED", "DIV", "DOUBLE", "DROP", "DUAL", "DUMMY", "DUMP", "EACH", "ELSE", "ELSEIF", "ENCLOSED", "END", "ENUM", "ERRLVL", "ESCAPE", "ESCAPED", "EXCEPT", "EXEC", "EXECUTE", "EXISTS", "EXIT", "EXPLAIN", "FALSE", "FETCH", "FILE", "FILLFACTOR", "FLOAT", "FLOAT4", "FLOAT8", "FOR", "FORCE", "FOREIGN", "FREETEXT", "FREETEXTTABLE", "FROM", "FULL", "FULLTEXT", "FUNCTION", "GENERAL", "GOTO", "GRANT", "GROUP", "HAVING", "HIGH_PRIORITY", "HOLDLOCK", "HOUR_MICROSECOND", "HOUR_MINUTE", "HOUR_SECOND", "IDENTITY", "IDENTITYCOL", "IDENTITY_INSERT", "IF", "IGNORE", "IGNORE_SERVER_IDS", "IN", "INDEX", "INFILE", "INNER", "INOUT", "INSENSITIVE", "INSERT", "INT", "INT1", "INT2", "INT3", "INT4", "INT8", "INTEGER", "INTERSECT", "INTERVAL", "INTO", "IS", "ITERATE", "JOIN", "KEY", "KEYS", "KILL", "LEADING", "LEAVE", "LEFT", "LIKE", "LIMIT", "LINEAR", "LINENO", "LINES",
                        "LOAD", "LOCALTIME", "LOCALTIMESTAMP", "LOCK", "LONG", "LONGBLOB", "LONGTEXT", "LOOP", "LOW_PRIORITY", "MASTER_HEARTBEAT_PERIOD", "MASTER_SSL_VERIFY_SERVER_CERT", "MATCH", "MAXVALUE", "MEDIUMBLOB", "MEDIUMINT", "MEDIUMTEXT", "MIDDLEINT", "MINUTE_MICROSECOND", "MINUTE_SECOND", "MOD", "MODIFIES", "NATIONAL", "NATURAL", "NO", "NOCHECK", "NONCLUSTERED", "NOT", "NO_WRITE_TO_BINLOG", "NULL", "NULLIF", "NUMERIC", "OF", "OFF", "OFFSETS", "ON", "OPEN", "OPENDATASOURCE", "OPENQUERY", "OPENROWSET", "OPENXML", "OPTIMIZE", "OPTION", "OPTIONALLY", "OR", "ORDER", "OUT", "OUTER", "OUTFILE", "OVER", "PERCENT", "PLAN", "PRECISION", "PRIMARY", "PRINT", "PROC", "PROCEDURE", "PUBLIC", "PURGE", "RAISERROR", "RANGE", "READ", "READS", "READTEXT", "READ_WRITE", "REAL", "RECONFIGURE", "REFERENCES", "REGEXP", "RELEASE", "RENAME", "REPEAT", "REPLACE",
                        "REPLICATION", "REQUIRE", "RESIGNAL", "RESTORE", "RESTRICT", "RETURN", "REVOKE", "RIGHT", "RLIKE", "ROLLBACK", "ROWCOUNT", "ROWGUIDCOL", "RULE", "SAVE", "SCHEMA", "SCHEMAS", "SECOND_MICROSECOND", "SELECT", "SENSITIVE", "SEPARATOR", "SESSION_USER", "SET", "SETUSER", "SHOW", "SHUTDOWN", "SIGNAL", "SLOW", "SMALLINT", "SOME", "SPATIAL", "SPECIFIC", "SQL", "SQLEXCEPTION", "SQLSTATE", "SQLWARNING", "SQL_BIG_RESULT", "SQL_CALC_FOUND_ROWS", "SQL_SMALL_RESULT", "SSL", "STARTING", "STATISTICS", "STRAIGHT_JOIN", "SYSTEM_USER", "TABLE", "TERMINATED", "TEXT", "TEXTSIZE", "THEN", "TIME", "TIMESTAMP", "TINYBLOB", "TINYINT", "TINYTEXT", "TO", "TOP", "TRAILING", "TRAN", "TRANSACTION", "TRIGGER", "TRUE", "TRUNCATE", "TSEQUAL", "UNDO", "UNION", "UNIQUE", "UNLOCK", "UNSIGNED", "UPDATE", "UPDATETEXT", "USAGE", "USE", "USER", "USING", "UTC_DATE", "UTC_TIME",
                        "UTC_TIMESTAMP", "VALUES", "VARBINARY", "VARCHAR", "VARCHARACTER", "VARYING", "VIEW", "WAITFOR", "WHEN", "WHERE", "WHILE", "WITH", "WRITE", "WRITETEXT", "XOR", "YEAR_MONTH", "ZEROFILL");
         $getReservedWordsSqlTest = $this->object->reservedWordsSql();
         $this->assertEquals($getReservedWordsSqlTest, $reservedWordsSqlTest);
    }

    /**
     * @covers PMSEEngineUtils::transformEntity
     */
    public function testTransformEntity()
    {
        $entityRoutes = array(
            "ROU_UID" => "flo_uid",
            "PRO_UID" => "prj_uid",
            "TAS_UID" => "flo_element_origin",
            "ROU_NEXT_TASK" => "flo_element_dest",
            "ROU_TO_PORT" => "flo_element_dest_port",
            "ROU_FROM_PORT" => "flo_element_origin_port",
            "ROU_EVN_UID" => "flo_element_dest",
            "GAT_UID" => "flo_element_dest",
            "flo_element_dest_type" => "flo_element_dest_type",
            "flo_element_origin_type" => "flo_element_origin_type"
        );
        $entityRoutesTrans = array(
            "flo_uid" => "flo_uid",
            "prj_uid" => "prj_uid",
            "flo_element_origin" => "flo_element_origin",
            "flo_element_dest" => "flo_element_dest",
            "flo_element_dest_port" => "flo_element_dest_port",
            "flo_element_origin_port" => "flo_element_origin_port",
            "flo_element_dest_type" => "bpmnGateway",
            "flo_element_origin_type" => "bpmnActivity"
        ); 
        $entityRoutes_2 = array(
            "ROU_UID" => "flo_uid",
            "PRO_UID" => "prj_uid",
            "TAS_UID" => "flo_element_origin",
            "ROU_NEXT_TASK" => "flo_element_dest",
            "ROU_TO_PORT" => "flo_element_dest_port",
            "ROU_FROM_PORT" => "flo_element_origin_port",
            "ROU_EVN_UID" => "flo_element_dest",
            "flo_element_dest_type" => "flo_element_dest_type",
            "flo_element_origin_type" => "flo_element_origin_type"
        );
        $entityRoutesTrans_2 = array(
            "flo_uid" => "flo_uid",
            "prj_uid" => "prj_uid",
            "flo_element_origin" => "flo_element_origin",
            "flo_element_dest" => "flo_element_dest",
            "flo_element_dest_port" => "flo_element_dest_port",
            "flo_element_origin_port" => "flo_element_origin_port",
            "flo_element_dest_type" => "bpmnEvent",
            "flo_element_origin_type" => "bpmnActivity"
        );         
         $entityRoutes_3 = array(
            "ROU_UID" => "flo_uid",
            "PRO_UID" => "prj_uid",
            "TAS_UID" => "flo_element_origin",
            "ROU_NEXT_TASK" => "flo_element_dest",
            "ROU_TO_PORT" => "flo_element_dest_port",
            "ROU_FROM_PORT" => "flo_element_origin_port",
            "flo_element_dest_type" => "flo_element_dest_type",
            "flo_element_origin_type" => "flo_element_origin_type"
        );
        $entityRoutesTrans_3 = array(
            "flo_uid" => "flo_uid",
            "prj_uid" => "prj_uid",
            "flo_element_origin" => "flo_element_origin",
            "flo_element_dest" => "flo_element_dest",
            "flo_element_dest_port" => "flo_element_dest_port",
            "flo_element_origin_port" => "flo_element_origin_port",
            "flo_element_dest_type" => "bpmnActivity",
            "flo_element_origin_type" => "bpmnActivity"
        );     
        
        $entityGateways = array(
            "GAT_UID" => "gat_uid",
            "PRO_UID" => "prj_uid",
            "GAT_X" => "bou_x",
            "GAT_Y" => "bou_y",
            "GAT_TYPE" => "gat_type"
        );
        $entityGatewaysTrans = array(
            "gat_uid" => "gat_uid",
            "prj_uid" => "prj_uid",
            "bou_x" => "bou_x",
            "bou_y" => "bou_y",
            "gat_type" => "PARALLEL"
        ); 
        $entityTasks = array(
                    'PRO_UID' => 'prj_uid',
                    'TAS_UID' => 'act_uid',
                    'TAS_TITLE' => 'act_name',
                    'TAS_TYPE' => 'NORMAL',
                    'TAS_DURATION' => 'act_duration',
                    'TAS_DURATION_TYPE' => 'act_duration_type',
                    'TAS_POSX' => 'bou_x',
                    'TAS_POSY' => 'bou_y',
                    'TAS_WIDTH' => 'bou_width',
                    'TAS_HEIGHT' => 'bou_height'
        );        
        $entityTasksTrans = array(
                    'prj_uid' => 'prj_uid',
                    'act_uid' => 'act_uid',
                    'act_name' => 'act_name',
                    'act_type' => 'TASK',
                    'act_task_type' => 'USERTASK',           
                    'act_duration' => 'act_duration',
                    'act_duration_type' => 'act_duration_type',
                    'bou_x' => 'bou_x',
                    'bou_y' => 'bou_y',
                    'bou_width' => 'bou_width',
                    'bou_height' => 'bou_height'
        );        
        $resultRoutes = $this->object->transformEntity("routes", $entityRoutes);
        $this->assertEquals($entityRoutesTrans, $resultRoutes); 
        $resultRoutes_2 = $this->object->transformEntity("routes", $entityRoutes_2);
        $this->assertEquals($entityRoutesTrans_2, $resultRoutes_2); 
        $resultRoutes_3 = $this->object->transformEntity("routes", $entityRoutes_3);
        $this->assertEquals($entityRoutesTrans_3, $resultRoutes_3);        
        $resultGateways = $this->object->transformEntity("gateways", $entityGateways);       
        $this->assertEquals($entityGatewaysTrans, $resultGateways); 
        $resultTasks = $this->object->transformEntity("tasks", $entityTasks);       
        $this->assertEquals($entityTasksTrans, $resultTasks);          
    }

    /**
     * @covers PMSEEngineUtils::getEntityDictionary
     */
    public function testGetEntityDictionary()
    {
        $entityDictionaryProcess = array(
            "PRO_UID" => "prj_uid",
            "PRO_TITLE" => "prj_name",
            "PRO_UPDATE_DATE" => "prj_update_date",
            "PRO_CREATE_DATE" => "prj_create_date",
            "PRO_CREATE_USER" => "prj_author",
            "PRO_DESCRIPTION" => "prj_description",
        );
        $entityDictionaryGateways = array(
            "GAT_UID" => "gat_uid",
            "PRO_UID" => "prj_uid",
            "GAT_X" => "bou_x",
            "GAT_Y" => "bou_y",
            "GAT_TYPE" => "gat_type"
        );
        $entityDictionaryTasks = array(
            'PRO_UID' => 'prj_uid',
            'TAS_UID' => 'act_uid',
            'TAS_TITLE' => 'act_name',
            'TAS_TYPE' => 'act_type',
            'TAS_DURATION' => 'act_duration',
            'TAS_DURATION_TYPE' => 'act_duration_type',
            'TAS_POSX' => 'bou_x',
            'TAS_POSY' => 'bou_y',
            'TAS_WIDTH' => 'bou_width',
            'TAS_HEIGHT' => 'bou_height'
        ); 
        $entityDictionaryRoutes = array(
            "ROU_UID" => "flo_uid",
            "PRO_UID" => "prj_uid",
            "TAS_UID" => "flo_element_origin",
            "ROU_NEXT_TASK" => "flo_element_dest",
            "ROU_TO_PORT" => "flo_element_dest_port",
            "ROU_FROM_PORT" => "flo_element_origin_port",
            "ROU_EVN_UID" => "flo_element_dest",
            "GAT_UID" => "flo_element_dest",
            "flo_element_dest_type" => "flo_element_dest_type",
            "flo_element_origin_type" => "flo_element_origin_type"
        ); 
        $entityDefault = array();        
        $resultProcess = $this->object->getEntityDictionary("process");
        $this->assertEquals($entityDictionaryProcess, $resultProcess); 
        $resultGateways = $this->object->getEntityDictionary("gateways");       
        $this->assertEquals($entityDictionaryGateways, $resultGateways); 
        $resultTasks = $this->object->getEntityDictionary("tasks");       
        $this->assertEquals($entityDictionaryTasks, $resultTasks); 
        $resultRoutes= $this->object->getEntityDictionary("routes");       
        $this->assertEquals($entityDictionaryRoutes, $resultRoutes);  
        $resultEnds= $this->object->getEntityDictionary("ends");       
        $this->assertEquals($entityDefault, $resultEnds);        
    }

    /**
     * @covers PMSEEngineUtils::isValidStudioField
     */
    public function testIsValidStudioField()
    {
        $array_test1 = array(
            'studio' => 'visible',
            'source' => '',
            'type' => '',
            'dbType' => ''
        ); 
        $array_test2 = array(
            'studio' => 'hidden',
            'source' => '',
            'type' => '',
            'dbType' => ''
        ); 
         $array_test3 = array(
            'source' => 'custom_fields',
            'type' => 'title',
            'dbType' => ""
        );  
        $array_test4 = array(
            'studio' => array('editField' => true),
            'source' => '',
            'type' => '',
            'dbType' => ''
        );  
        $array_test5 = array(
            'studio' => array('required' => true),
            'source' => '',
            'type' => '',
            'dbType' => ''
        ); 
        $array_test6 = array(
            'source' => 'c_f',            
            'type' => '',
            'dbType' => ''
        );  
        $array_test7 = array(
            'studio' => 'display',
            'source' => '',
            'type' => '',
            'dbType' => ''
        );  
        $array_test8 = array(
            'studio' => 'display',           
            'source' => 'c_f',            
            'type' => '',
            'dbType' => ''
        );
        $array_test9 = array(
            'studio' => array(),
            'source' => 'c_f',            
            'type' => '',
            'dbType' => ''
        );
        $array_test10 = array(
            'studio' => array(),
            'source' => 'db',            
            'type' => 'id',
            'dbType' => ''
        );

        $result_test1 = $this->object->isValidStudioField($array_test1);
        $this->assertTrue($result_test1);
        $result_test2 = $this->object->isValidStudioField($array_test2);
        $this->assertFalse($result_test2);   
        $result_test3 = $this->object->isValidStudioField($array_test3);
        $this->assertTrue($result_test3);  
        $result_test4 = $this->object->isValidStudioField($array_test4);
        $this->assertTrue($result_test4);
        $result_test5 = $this->object->isValidStudioField($array_test5);
        $this->assertTrue($result_test5);
        $result_test6 = $this->object->isValidStudioField($array_test6);
        $this->assertFalse($result_test6); 
        $result_test7 = $this->object->isValidStudioField($array_test7);
        $this->assertTrue($result_test7);  
        $result_test8 = $this->object->isValidStudioField($array_test8);
        $this->assertFalse($result_test8);         
        $result_test9 = $this->object->isValidStudioField($array_test9);
        $this->assertFalse($result_test9);
        $result_test10 = $this->object->isValidStudioField($array_test10);
        $this->assertFalse($result_test10);
    }

    /**
     * @covers PMSEEngineUtils::isValidDefinitionField
     */
    public function testIsValidDefinitionField()
    {
        $array_test1 = array(
            'studio' =>  'visible',
            'source' => '',
            'type' => '',
            'dbType' => ''
        ); 
        $array_test2 = array(
            'studio' => array('visible' => 'visible'),
            'source' => '',
            'type' => '',
            'dbType' => ''
        ); 
        $array_test3 = array(
            'studio' => array('visible' => 'hidden'),
            'source' => '',
            'type' => '',
            'dbType' => ''
        );   
        $array_test4 = array(
            'source' => '',
            'type' => '',
            'dbType' => ''
        );   
        $array_test5 = array(
            'studio' => array(),
            'source' => '',
            'type' => '',
            'dbType' => ''
        );

        $result_test1 = $this->object->isValidDefinitionField($array_test1);
        $this->assertTrue($result_test1);
        $result_test2 = $this->object->isValidDefinitionField($array_test2);
        $this->assertEquals('visible', $result_test2);   
        $result_test3 = $this->object->isValidDefinitionField($array_test3, 'visible');
        $this->assertFalse($result_test3); 
        $result_test4 = $this->object->isValidDefinitionField($array_test4, 'visible');
        $this->assertFalse($result_test4);         
        $result_test5 = $this->object->isValidDefinitionField($array_test5, 'visible');
        $this->assertFalse($result_test5);
    }

    /**
     * @covers PMSEEngineUtils::getEntityUid
     */
    public function testGetEntityUid()
    {
        $arr = array(
            'bpmnActivity' => 'act_uid',
            'bpmnGateway' => 'gat_uid',
            'bpmnEvent' => 'evn_uid',
            'bpmnFlow' => 'flo_uid',
            'bpmnLaneset' => 'lns_uid',
            'bpmnLane' => 'lan_uid',
            'bpmnData' => 'dat_uid',
            'bpmnParticipant' => 'par_uid',
            'bpmnArtifact' => 'art_uid'             
            );       
        foreach ($arr as $key => $value) {
            $result = $this->object->getEntityUid($key);
            $this->assertEquals($value, $result);
        }
    }

    /**
     * @covers PMSEEngineUtils::getElementUid
     * @todo   Implement testGetElementUid().
     */
    public function testGetElementUid()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers PMSEEngineUtils::lowerFirstCharCase
     */
    public function testLowerFirstCharCase()
    {
        $arr = array(
            'One' => 'one',
            'Two' => 'two',
            'Three' => 'three',
            'Four' => 'four',
            'Five' => 'five'
        );
        foreach ($arr as $key => $value) {
            $result = $this->object->lowerFirstCharCase($key);
            $this->assertEquals($value, $result);
        }
    }

    /**
     * @covers PMSEEngineUtils::getPrimaryEmailKeyFromREQUEST
     */
    public function testGetPrimaryEmailKeyFromREQUEST()
    {
        $obj_bean = new stdClass();
        $obj_bean->module_dir = 'leads';
        $result = $this->object->getPrimaryEmailKeyFromREQUEST($obj_bean);
        $this->assertEquals($_REQUEST['leads_email_widget_id'], 0); 
        $this->assertEquals($_REQUEST['emailAddressWidget'], 1); 
        $this->assertEquals($_REQUEST['useEmailWidget'], true); 
        $this->assertEquals($_REQUEST['leads0emailAddressPrimaryFlag'], 'leads0emailAddress0'); 
        $this->assertEquals($_REQUEST['leads0emailAddressVerifiedFlag0'], true);         
        $this->assertEquals('leads0emailAddress0', $result);  
        $result = $this->object->getPrimaryEmailKeyFromREQUEST($obj_bean); 
        $this->assertEquals('leads0emailAddress0', $result);
        unset($_REQUEST);        
        $_REQUEST['leadsemailAddressPrimaryFlag'] = '2'; 
        $this->assertEquals('leads0emailAddress0', $result); 
        unset($_REQUEST);        
        $_REQUEST['leads0emailAddressPrimaryFlag'] = '2'; 
        $this->assertEquals('leads0emailAddress0', $result);         
    }

    /**
     * @covers PMSEEngineUtils::updateEmails
     */
    public function testUpdateEmails()
    {
        $obj_bean = new stdClass();
        $obj_bean->module_dir = 'leads';
        $obj_bean->id = 1;
        $obj_bean->emailAddress = new EmailAddress();
        $result = $this->object->updateEmails($obj_bean, "test2@test.com");
        $this->assertEquals($_REQUEST['leads_email_widget_id'], 0);
        $this->assertEquals($_REQUEST['emailAddressWidget'], 1);
        $this->assertEquals($_REQUEST['useEmailWidget'], true);
        $this->assertEquals($_REQUEST['leads0emailAddressPrimaryFlag'], 'leads0emailAddressaddress1');
        $this->assertEquals($_REQUEST['leads0emailAddressaddress1'], 'test2@test.com');
        $this->assertEquals($_REQUEST['leads0emailAddressIdaddress1'], '1');
        $this->assertEquals($_REQUEST['leads0emailAddressVerifiedFlagaddress1'], true);
        $this->assertEquals($_REQUEST['leads0emailAddressVerifiedValueaddress1'], 'test1@test.com');
        $obj_bean->id = 2;
        unset($_REQUEST);
        $result = $this->object->updateEmails($obj_bean, "test2@test.com");
        $this->assertEquals($_REQUEST['leads_email_widget_id'], 0);
        $this->assertEquals($_REQUEST['emailAddressWidget'], 1);
        $this->assertEquals($_REQUEST['useEmailWidget'], true);
        $this->assertEquals($_REQUEST['leads0emailAddressaddress1'], 'test1@test.com');
        $this->assertEquals($_REQUEST['leads0emailAddressIdaddress1'], '2');
        $this->assertEquals($_REQUEST['leads0emailAddressVerifiedFlagaddress1'], true);
        $this->assertEquals($_REQUEST['leads0emailAddressVerifiedValueaddress1'], 'test1@test.com');
        $obj_bean->id = 3;
        unset($_REQUEST);
        $result = $this->object->updateEmails($obj_bean, "test2@test.com");
        $this->assertEquals($_REQUEST['leads_email_widget_id'], 0);
        $this->assertEquals($_REQUEST['emailAddressWidget'], 1);
        $this->assertEquals($_REQUEST['useEmailWidget'], true);
    }

    /**
     * @covers PMSEEngineUtils::processExpectedTime
     */
    public function testProcessExpectedTime()
    {
        $case_time_test1 = new stdClass();
        $case_time_test1->cas_task_start_date = '2013-12-25 14:30:00';
        $case_time_test1->cas_delegate_date = '0000-00-00 00:00:00'; 
        
        $case_time_test2 = new stdClass();
        $case_time_test2->cas_task_start_date = '2013-11-11 09:43:05';
        $case_time_test2->cas_delegate_date = '0000-00-00 00:00:00';         
       
        $case_time_test3 = new stdClass();
        $case_time_test3->cas_task_start_date = '2013-10-02 04:50:11';
        $case_time_test3->cas_delegate_date = '0000-00-00 00:00:00';  
        
        $obj_time_test1 = new stdClass();
        $obj_time_test1->time = '2';
        $obj_time_test1->unit = 'day';
  
        $obj_time_test2 = new stdClass();
        $obj_time_test2->time = '10';
        $obj_time_test2->unit = 'minute';

        $obj_time_test3 = new stdClass();
        $obj_time_test3->time = '1';
        $obj_time_test3->unit = 'hour';
        
        $result_test1 = $this->object->processExpectedTime($obj_time_test1, $case_time_test1);
        $this->assertEquals('2013-12-27 14:30:00', date("Y-m-d H:i:s", $result_test1));
        $result_test2 = $this->object->processExpectedTime($obj_time_test2, $case_time_test2);
        $this->assertEquals('2013-11-11 09:53:05', date("Y-m-d H:i:s", $result_test2));     
        $result_test3 = $this->object->processExpectedTime($obj_time_test3, $case_time_test3);
        $this->assertEquals('2013-10-02 05:50:11', date("Y-m-d H:i:s", $result_test3));        

    }
    
    public function testGetBPMInboxStatus()
    {
        $result_test1 = $this->object->getBPMInboxStatus('12345');
        $this->assertEquals(true, $result_test1);
//        $result_test2 = $this->object->getBPMInboxStatus('12345');
//        $this->assertEquals(true, $result_test2);     
    }
    
    public function testValidateUniqueUid()
    {
        $bean = new stdClass();
        $bean->object_name = 'Leads';
        $bean->id = '12345';
        
        $result_test1 = $this->object->validateUniqueUid($bean,'id');
        $this->assertEquals(true, $result_test1);
    }
    
    public function testUnsetCommonFields()
    {
        $bean = array();
        $bean['object_name'] = 'Leads';
        $bean['id'] = '12345';
        
        $result_test1 = $this->object->unsetCommonFields($bean);
        $this->assertEquals(array('object_name' => 'Leads'), $result_test1);
    }
    
    public function testGetAllFieldsBean()
    {
        $result_test1 = $this->object->getAllFieldsBean('Leads');
        $this->assertEquals('OK', $result_test1);
        $result_test2 = $this->object->getAllFieldsBean('');
        $this->assertEquals('', $result_test2);
    }

    /**
     * @covers PMSEEngineUtils::makeCacheKey
     */
    public function testMakeCacheKey_NoId()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('load_relationship'))
            ->getMock();
        $result_test = PMSEEngineUtils::makeCacheKey('Tasks', $beanMock, 'cases');
        $this->assertNull($result_test);
    }

    /**
     * @covers PMSEEngineUtils::makeCacheKey
     */
    public function testMakeCacheKey()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('load_relationship'))
            ->getMock();
        $beanMock->name = 'MakeCacheKey Test';
        $beanMock->id = '12345678';
        $result_test = PMSEEngineUtils::makeCacheKey('Tasks', $beanMock, 'cases');
        $this->assertEquals('pmse_Tasks_12345678_cases_parent_bean', $result_test);
    }

    /**
     * @covers PMSEEngineUtils::getParentBean
     */
    public function testGetParentBean_NoCache()
    {
        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 2,
            'bpmn_id' => '9283jd9238j3d',
            'cas_sugar_module' => 'Tasks',
            'evn_module'=>'Cases',
            'rel_process_module' => 'Cases',
            'rel_element_module' => 'Tasks',
            'cas_sugar_object_id' => '893u2d89qj2398d',
            'rel_element_relationship' => 'tasks',
        );

        // Build the cases_tasks relationship
        $case = SugarTestCaseUtilities::createCase('', ['name' => 'GetLinkedBeans Test Case']);
        $task = SugarTestTaskUtilities::createTask('', ['name' => 'GetLinkedBeans Test Task']);
        $case->tasks->add($task);

        $result = PMSEEngineUtils::getParentBean($flowData, $task, false);
        $this->assertEquals(1, count($result));
        $this->assertEquals($result->name, 'GetLinkedBeans Test Case');
    }

    /**
     * @covers PMSEEngineUtils::getParentBean
     */
    public function testGetParentBean_WithCache()
    {
        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 2,
            'bpmn_id' => '9283jd9238j3d',
            'cas_sugar_module' => 'Tasks',
            'evn_module'=>'Cases',
            'rel_process_module' => 'Cases',
            'rel_element_module' => 'Tasks',
            'cas_sugar_object_id' => '893u2d89qj2398d',
            'rel_element_relationship' => 'tasks',
        );

        // Build the cases_tasks relationship
        $case = SugarTestCaseUtilities::createCase('', ['name' => 'GetLinkedBeans Test Case']);
        $task = SugarTestTaskUtilities::createTask('', ['name' => 'GetLinkedBeans Test Task']);
        $case->tasks->add($task);

        // By default getParentBean with cache enabled
        $result = PMSEEngineUtils::getParentBean($flowData, $task);
        $this->assertEquals(1, count($result));
        $this->assertEquals($result->name, 'GetLinkedBeans Test Case');
    }

}
