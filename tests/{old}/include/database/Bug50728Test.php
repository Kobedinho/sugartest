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

class Bug50728Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    public function tearDown()
    {
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @dataProvider fulltextQueryProvider
     */
    public function testParseFulltextQuery($expected, $query) {
        $this->assertSame($expected, $GLOBALS['db']->parseFulltextQuery($query));
    }

    /**
     * data provider for testParseFulltextQuery
     * @return array - [$expected[$query_terms, $must_terms, $not_terms], $query]
     */
    public function fulltextQueryProvider() {
        return array(
            array(array(array('aa', 'bb'), array(), array()), 'aa - bb'),
            array(array(array('aa', 'bb'), array(), array()), 'aa + bb'),
            array(array(array('aa', 'bb'), array(), array()), 'aa - bb +'),
            array(array(array('aa', 'bb'), array(), array()), 'aa + bb -'),
            array(array(array('aa - bb'), array(), array()), '"aa - bb"'),
            array(array(array('aa + bb'), array(), array()), '"aa + bb"'),
            array(array(array('aa-bb'), array(), array()), 'aa-bb'),
            array(array(array('aa+bb'), array(), array()), 'aa+bb'),
            array(array(array('aa', 'bb'), array(), array('c')), 'aa -c bb'),
            array(array(array('bb'), array('aa'), array('c')), '+aa -c bb'),
        );
    }
}
