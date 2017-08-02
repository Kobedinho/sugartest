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
 * @ticket 49505
 */
class Bug49505Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    private $_createdBeans = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        foreach ($this->_createdBeans as $bean) {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }

        SugarTestHelper::tearDown();
    }

    public function testGetRelatedListFunctionWithLink2Class()
    {
        $focusModule = 'Accounts';
        $linkedModules = array(
            'Bugs', // many-to-many
            'Contacts' // one-to-many
        );

        $focus = BeanFactory::newBean($focusModule);
        $focus->name = "bug49505";
        $focus->save();
        $this->_createdBeans[] = $focus;

        foreach ($linkedModules as $v) {

            $linkedBean = BeanFactory::newBean($v);
            $linkedBean->name = "bug49505";
            $linkedBean->save();
            $this->_createdBeans[] = $linkedBean;

            $link = new Link2(strtolower($v), $focus);
            $link->add(array($linkedBean));

            // get relation from 'Link2' class
            $link2List = $focus->get_related_list($linkedBean, strtolower($v));

            // get relation for 'get_related_list' function from Link class
            $focus->field_defs[strtolower($v)]['link_class'] = 'Link';
            $focus->field_defs[strtolower($v)]['link_file'] = 'data/Link.php';
            $linkList = $focus->get_related_list($linkedBean, strtolower($v));

            unset($focus->field_defs[strtolower($v)]['link_class']);
            unset($focus->field_defs[strtolower($v)]['link_file']);

            $this->assertEquals($linkedBean->id, $linkList['list'][0]->id);
            $this->assertEquals($linkedBean->id, $link2List['list'][0]->id);
        }
    }

}
