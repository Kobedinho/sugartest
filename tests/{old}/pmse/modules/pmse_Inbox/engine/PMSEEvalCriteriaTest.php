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

class PMSEEvalCriteriaTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PMSEEvalCriteria
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = ProcessManager\Factory::getPMSEObject('PMSEEvalCriteria');
        // The default timezone is set to phoenix because the server could
        // have a different timezone that triggers failures with the tests 
        // already defined values.
        date_default_timezone_set("America/Phoenix"); 
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * Generated from @assert ('[{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expValue":"(","expType":"GROUP","expLabel":"("},{"expDirection":"after","expModule":"Leads","expField":"website","currentValue":"","expOperator":"equals","expValue":"","expType":"MODULE","expFieldType":"","expLabel":"Website == \"\""},{"expValue":"OR","expType":"LOGIC","expLabel":"OR"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expModule":null,"expField":"38748400252160f8bedad05063298920","expFieldType":"","currentValue":"Approve","expOperator":"equals","expValue":"Approve","expType":"CONTROL","expLabel":"comer == Approve"},{"expValue":")","expType":"GROUP","expLabel":")"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"account_name","currentValue":"TRUE","expOperator":"equals","expFieldType":"","expValue":"TRUE","expType":"MODULE","expLabel":"Account Name: == \"TRUE\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"assistant","currentValue":"FALSE","expOperator":"major_equals_than","expValue":"FALSE","expFieldType":"","expType":"MODULE","expLabel":"Assistant >= \"FALSE\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"(","expType":"GROUP","expLabel":"("},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"account_name","currentValue":"APROVED","expOperator":"equals","expValue":"APROVED","expType":"MODULE","expFieldType":"","expLabel":"Account Name: == \"APROVED\""},{"expValue":")","expType":"GROUP","expLabel":")"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expModule":null,"expField":"15","currentValue":"","expOperator":"equals","expValue":"","expType":"BUSINESS_RULES","expFieldType":"","expLabel":"Action # 1 == \"\""}]') == 0.
     *
     * @covers PMSEEvalCriteria::expresions
     */
    public function testExpresions()
    {
        $this->assertEquals(
                0
                , $this->object->expresions(json_decode('[{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expValue":"(","expType":"GROUP","expLabel":"("},{"expDirection":"after","expModule":"Leads","expField":"website","currentValue":"","expOperator":"equals","expValue":"","expType":"MODULE","expFieldType":"","expLabel":"Website == \"\""},{"expValue":"OR","expType":"LOGIC","expLabel":"OR"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expModule":null,"expField":"38748400252160f8bedad05063298920","expFieldType":"","currentValue":"Approve","expOperator":"equals","expValue":"Approve","expType":"CONTROL","expLabel":"comer == Approve"},{"expValue":")","expType":"GROUP","expLabel":")"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"account_name","currentValue":"TRUE","expOperator":"equals","expFieldType":"","expValue":"TRUE","expType":"MODULE","expLabel":"Account Name: == \"TRUE\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"assistant","currentValue":"FALSE","expOperator":"major_equals_than","expValue":"FALSE","expFieldType":"","expType":"MODULE","expLabel":"Assistant >= \"FALSE\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"(","expType":"GROUP","expLabel":"("},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"account_name","currentValue":"APROVED","expOperator":"equals","expValue":"APROVED","expType":"MODULE","expFieldType":"","expLabel":"Account Name: == \"APROVED\""},{"expValue":")","expType":"GROUP","expLabel":")"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expModule":null,"expField":"15","currentValue":"","expOperator":"equals","expValue":"","expType":"BUSINESS_RULES","expFieldType":"","expLabel":"Action # 1 == \"\""}]'))
        );
    }

    public function testExpresionsUsers()
    {
        $this->assertEquals(1, $this->object->expresions(json_decode('[{"expDirection":"after","expFieldType":"TextField","expModule":"Leads","expField":"account_name","expOperator":"equals","expValue":"Pepe","expType":"MODULE","expLabel":"Account Name == &Pepe&","currentValue":"Pepe"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expModule":null,"expField":"current_user","expFieldType":"","expOperator":"equals","expValue":"is_admin","expType":"USER_ADMIN","expLabel":"Current user is admin","currentValue":"is_admin"}]')));
        $this->assertEquals(1, $this->object->expresions(json_decode('[{"expDirection":"after","expFieldType":"TextField","expModule":"Leads","expField":"account_name","expOperator":"equals","expValue":"Pepe","expType":"MODULE","expLabel":"Account Name == &Pepe&","currentValue":"Pepe"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expModule":null,"expField":"current_user","expFieldType":"","expOperator":"equals","expValue":"is_admin","expType":"USER_ROLE","expLabel":"Current user has role Administrator","currentValue":"is_admin"}]')));
        $this->assertEquals(1, $this->object->expresions(json_decode('[{"expDirection":"after","expFieldType":"TextField","expModule":"Leads","expField":"account_name","expOperator":"equals","expValue":"Pepe","expType":"MODULE","expLabel":"Account Name == &Pepe&","currentValue":"Pepe"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expModule":null,"expField":"current_user","expFieldType":"","expOperator":"equals","expValue":"1","expType":"USER_IDENTITY","expLabel":"Current user ==  Administrator","currentValue":"1"}]')));
    }
    /**
     * Generated from @assert ('') == 1.
     *
     * @covers PMSEEvalCriteria::expresions
     */
    public function testExpresionsEmty()
    {
        $this->assertEquals(1, $this->object->expresions(''));
    }

    /**
     * Generated from @assert ('[{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expValue":"(","expType":"GROUP","expLabel":"("},{"expDirection":"after","expModule":"Leads","expField":"website","currentValue":"","expOperator":"equals","expValue":"","expType":"MODULE","expFieldType":"","expLabel":"Website == \"\""},{"expValue":"OR","expType":"LOGIC","expLabel":"OR"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expModule":null,"expField":"38748400252160f8bedad05063298920","currentValue":"Approve","expOperator":"equals","expValue":"Approve","expType":"CONTROL","expFieldType":"","expLabel":"comer == Approve"},{"expValue":")","expType":"GROUP","expLabel":")"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"account_name","currentValue":"TRUE","expOperator":"equals","expValue":"TRUE","expType":"MODULE","expFieldType":"","expLabel":"Account Name: == \"TRUE\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"assistant","currentValue":"FALSE","expOperator":"major_equals_than","expValue":"FALSE","expType":"MODULE","expFieldType":"","expLabel":"Assistant >= \"FALSE\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"(","expType":"GROUP","expLabel":"("},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"account_name","currentValue":"APROVED","expOperator":"equals","expValue":"APROVED","expType":"MODULE","expFieldType":"","expLabel":"Account Name: == \"APROVED\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expModule":null,"expField":"15","currentValue":"","expOperator":"equals","expValue":"","expType":"BUSINESS_RULES","expFieldType":"","expLabel":"Action # 1 == \"\""}]') == 0.
     * Cuando falta algun parentisis no devuelve false o cero
     * @covers PMSEEvalCriteria::expresions
     */
    public function testExpresionsErrorExpresions()
    {
        $this->assertEquals(0, $this->object->expresions(json_decode('[{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expValue":"(","expType":"GROUP","expLabel":"("},{"expDirection":"after","expModule":"Leads","expField":"website","currentValue":"","expOperator":"equals","expValue":"","expType":"MODULE","expFieldType":"","expLabel":"Website == \"\""},{"expValue":"OR","expType":"LOGIC","expLabel":"OR"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expModule":null,"expField":"38748400252160f8bedad05063298920","currentValue":"Approve","expOperator":"equals","expValue":"Approve","expType":"CONTROL","expFieldType":"","expLabel":"comer == Approve"},{"expValue":")","expType":"GROUP","expLabel":")"},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"account_name","currentValue":"TRUE","expOperator":"equals","expValue":"TRUE","expType":"MODULE","expFieldType":"","expLabel":"Account Name: == \"TRUE\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"assistant","currentValue":"FALSE","expOperator":"major_equals_than","expValue":"FALSE","expType":"MODULE","expFieldType":"","expLabel":"Assistant >= \"FALSE\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expValue":"(","expType":"GROUP","expLabel":"("},{"expValue":"NOT","expType":"LOGIC","expLabel":"NOT"},{"expDirection":"after","expModule":"Leads","expField":"account_name","currentValue":"APROVED","expOperator":"equals","expValue":"APROVED","expType":"MODULE","expFieldType":"","expLabel":"Account Name: == \"APROVED\""},{"expValue":"AND","expType":"LOGIC","expLabel":"AND"},{"expModule":null,"expField":"15","currentValue":"","expOperator":"equals","expValue":"","expType":"BUSINESS_RULES","expFieldType":"","expLabel":"Action # 1 == \"\""}]')));
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evaluation
     */
    public function testEvaluationsRecursive()
    {
        $array = array('(', '1', 'AND', '1', ')', 'OR', '1');
        $this->assertEquals(1, $this->object->evaluationsRecursive($array));
    }
    
    public function testEvaluationsRecursiveGroup()
    {
        $array = array('(', '1', 'AND', '1', 'OR', '1');
        $this->assertEquals(0, $this->object->evaluationsRecursive($array));
    }
    
    public function testEvaluationsRecursiveGroups()
    {
        $array = array('(', '(', '1', 'AND', '1', ')', 'OR', '1', ')');
        $this->assertEquals(1, $this->object->evaluationsRecursive($array));
    }
    
    public function testEvaluationsRecursiveOneElement()
    {
        $array = array('1');
        $this->assertEquals(1, $this->object->evaluationsRecursive($array));
    }
    
    public function testVerifyGroups()
    {
        $array = array('(', '1', 'AND', '1', ')', 'OR', '1');
        $this->object->verifyGroups($array);
        $array = $this->object->arrayGroups;
        $this->assertContainsOnly("integer",$array[0]['(']);
        $this->assertContainsOnly("integer",$array[0][')']);
        $this->assertEquals(0,$array[0]['('][0]);
        $this->assertEquals(4,$array[0][')'][0]);
    }

    public function testVerifyEqualsGroups()
    {
        $this->object->arrayGroups = array(
            array('(', ')'),
            );
        $this->assertEquals(true, $this->object->verifyEqualsGroups());
        $this->object->arrayGroups = array(
            array('('),
            );
        $this->assertEquals(false, $this->object->verifyEqualsGroups());
    }
    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evaluation
     */
    public function testEvaluationAritmetic()
    {
        $array = array('1', '/', '0');
        $this->assertEquals(
                0
                , $this->object->evaluation($array)
        );
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evaluation
     */
    public function testEvaluationRelations()
    {
        $array = array('0', '==', '1');
        $this->assertEquals(
                0
                , $this->object->evaluation($array)
        );
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evaluation
     */
    public function testEvaluationLogic()
    {
        $array = array('0', 'AND', '1','OR','NOT','0');
        $this->assertEquals(1, $this->object->evaluation($array));
    }

    
    public function testRouteFunctionOperator()
    {
        $this->assertEquals(12, $this->object->routeFunctionOperator('evalAritmetic',6, '+', 6));
        $this->assertEquals(1, $this->object->routeFunctionOperator('evalRelations',12, '>', 6));
        $this->assertEquals(0, $this->object->routeFunctionOperator('evalLogic',0, 'AND', 0));
        $this->assertEquals(0, $this->object->routeFunctionOperator('default',0, '?', 0));
    }
    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evalAritmetic
     */
    public function testEvalAritmeticNumeric()
    {
        $this->assertEquals(12, $this->object->evalAritmetic(6, '+', 6));
        $this->assertEquals(0, $this->object->evalAritmetic(6, '-', 6));
        $this->assertEquals(36, $this->object->evalAritmetic(6, 'x', 6));
        $this->assertEquals(1, $this->object->evalAritmetic(6, '/', 6));
        $this->assertEquals(0, $this->object->evalAritmetic(6, '/', 0));
        $this->assertEquals(0, $this->object->evalAritmetic(6, '?', 6));
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evalRelations
     */
    public function testEvalRelationsTrueNumeric()
    {
        $this->assertEquals(1, $this->object->evalRelations(12, '>', 6));
        $this->assertEquals(1, $this->object->evalRelations(12, '>=', 12));
        $this->assertEquals(1, $this->object->evalRelations(6, '<', 12));
        $this->assertEquals(1, $this->object->evalRelations(12, '<=', 12));
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evalRelations
     */
    public function testEvalRelationsFalseNumeric()
    {
        $this->assertEquals(0, $this->object->evalRelations(12, '>', 24));
        $this->assertEquals(0, $this->object->evalRelations(23, '>=', 24));
        $this->assertEquals(0, $this->object->evalRelations(24, '<', 12));
        $this->assertEquals(0, $this->object->evalRelations(24, '<=', 23));
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evalRelations
     */
    public function testEvalRelationsTrueLiteral()
    {
        $this->assertEquals(1, $this->object->evalRelations('aa', '==', 'aa'));
        $this->assertEquals(1, $this->object->evalRelations('aa', '!=', 'ba'));
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evalRelations
     */
    public function testEvalRelationsFalseLiteral()
    {
        $this->assertEquals(0, $this->object->evalRelations('ab', '==', 'ac'));
        $this->assertEquals(0, $this->object->evalRelations('ab', '!=', 'ab'));
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evalRelations
     */
    public function testEvalRelationsInexistent()
    {
        $this->assertEquals(0, $this->object->evalRelations('ab', '?', 'ac'));
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evalLogic
     */
    public function testEvalLogicFalse()
    {
        $this->assertEquals(0, $this->object->evalLogic(0, 'AND', 0));
        $this->assertEquals(0, $this->object->evalLogic(0, 'OR', 0));
        $this->assertEquals(0, $this->object->evalLogic(0, 'OTHERS', 0));
    }

    /**
     * Generated from @assert ($value1, $relacion, $value2) == 0.
     * @covers PMSEEvalCriteria::evalLogic
     */
    public function testEvalLogicTrue()
    {
        $this->assertEquals(1, $this->object->evalLogic(1, 'AND', 1));
        $this->assertEquals(1, $this->object->evalLogic(1, 'OR', 1));
        $this->assertEquals(0, $this->object->evalLogic(1, 'NOT'));
    }
    
    public function testTypeData()
    {        
        $this->assertEquals('Holas', $this->object->typeData('Holas','address'));
        $this->assertEquals(true, $this->object->typeData(true,'bool'));
        $this->assertEquals(1381388400, $this->object->typeData('10/10/2013','date'));
        $this->assertEquals(12345, $this->object->typeData(12345,'enum'));
        $this->assertEquals(12.34, $this->object->typeData(12.34,'float'));
        $this->assertEquals(123456, $this->object->typeData(123456,'integer'));
        $this->assertEquals(12, $this->object->typeData(12,'decimal'));
        $this->assertEquals('None', $this->object->typeData('None','currency'));
        $this->assertEquals('OTHERS', $this->object->typeData('OTHERS','encrypt'));
    }

    public function testLogicSimbol()
    {
        $this->assertEquals('&&', $this->object->logicSimbol('AND'));
        $this->assertEquals('||', $this->object->logicSimbol('OR'));
        $this->assertEquals('!', $this->object->logicSimbol('NOT'));
        $this->assertEquals('ES', $this->object->logicSimbol('OTHRES'));
    }

}
