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

include_once 'clients/base/api/CollectionApi/CollectionDefinition/CollectionDefinitionInterface.php';

class RelateCollectionApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $module = 'Meetings';

    /**
     * @var string
     */
    private $collectionName = 'invitees';

    /**
     * @var SugarBean
     */
    private $bean;

    /**
     * @var User
     */
    private $user;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user', array(true));
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = SugarTestMeetingUtilities::createMeeting('', $this->user);
    }

    protected function tearDown()
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    /**
     * Test case to check issue TR-14198.
     */
    public function testGetCollectionWithOnlyViewOwnerPermission()
    {
        $aclData['module']['list']['aclaccess'] = ACL_ALLOW_ALL;
        $aclData['module']['view']['aclaccess'] = ACL_ALLOW_OWNER;
        ACLAction::setACLData($GLOBALS['current_user']->id, $this->module, $aclData);

        $this->bean->assigned_user_id = $this->user->id;
        $this->bean->save();
        BeanFactory::unregisterBean($this->module, $this->bean->id);

        $serviceBaseMock = $this->createMock('ServiceBase');
        $args = array(
            'collection_name' => $this->collectionName,
            'module' => $this->module,
            'record' => $this->bean->id,
            'order_by' => array(),
            'offset' => array(),
            'max_num' => 20,
        );

        $relateCollectionApi = $this->createPartialMock('RelateCollectionApi', array(
            'normalizeArguments',
            'getSortSpec',
            'getAdditionalSortFields',
            'getData',
            'cleanData',
            'extractErrors',
            'buildResponse',
        ));

        $relateCollectionApi->expects($this->once())->method('getSortSpec')->willReturn(array());
        $relateCollectionApi->expects($this->once())->method('getAdditionalSortFields')->willReturn(array());
        $relateCollectionApi->expects($this->once())->method('getData')->willReturn(array(
            array(
                'records' => array(),
                'next_offset' => 0,
            ),
        ));

        $relateCollectionApi->expects($this->once())->method('cleanData')->willReturn(array());
        $relateCollectionApi->expects($this->once())->method('extractErrors')->willReturn(array());

        $relateCollectionApi->expects($this->once())
            ->method('normalizeArguments')
            ->with($args, $this->isInstanceOf('RelateCollectionDefinition'))
            ->willReturn($args);

        $relateCollectionApi->getCollection($serviceBaseMock, $args);
    }
}
