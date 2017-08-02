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

include_once 'modules/Categories/Category.php';

/**
 * Test for Categories module
 */
class CategoriesTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * All created bean ids.
     *
     * @var array
     */
    public static $beanIds = array();

    /**
     * Root node
     *
     * @var CategoryMock $root
     */
    public static $root;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));
        $root = SugarTestCategoryUtilities::createRootBean();
        self::$root = $root;
    }

    public function tearDown()
    {
        self::$root = null;
        SugarTestCategoryUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    /**
     * Test retrieve a valid query object using Category::getQuery method.
     */
    public function testGetQuery()
    {
        $bean = new CategoryMock();
        $this->assertInstanceOf('SugarQuery', $bean->getQueryMock());
    }

    /**
     * Test retrieve a valid tree data using Category::getTreeData method.
     */
    public function testGetTreeData()
    {
        $bean = new CategoryMock();
        $this->assertInternalType('array', $bean->getTreeDataMock('test'));
    }

    /**
     * Test update category data using Category::update method.
     */
    public function testUpdate()
    {
        $db = DBManagerFactory::getInstance();
        $expected = 'TestUpdateCategoryName' . mt_rand();

        SugarTestReflection::callProtectedMethod(
            self::$root,
            'update',
            array(
                array('name = ?'),
                ' id = ? ',
                array($expected, self::$root->id),
            )
        );

        $root = BeanFactory::retrieveBean('Categories', self::$root->id, array(
            'use_cache' => false,
        ));

        $this->assertEquals($expected, $root->name);
    }

    /**
     * Test make new root category data using Category::saveAsRoot method.
     */
    public function testSaveAsRoot()
    {
        $bean = new CategoryMock();
        $bean->name = 'SugarCategoryRoot' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean($bean->saveAsRoot());

        $this->assertTrue($bean->lft == 1);
        $this->assertTrue($bean->rgt == 2);
        $this->assertTrue($bean->lvl == 0);
        $this->assertTrue($bean->root == $bean->id);
    }

    /**
     * Test retrieve a valid data using Category::isRoot method.
     */
    public function testIsRoot()
    {
        $bean = new CategoryMock();
        $bean->name = 'SugarCategoryRoot' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean($bean->saveAsRoot());

        $this->assertTrue($bean->isRoot());
    }

    /**
     * Test shifting of node indexes using Category::shiftLeftRight method.
     */
    public function testShiftLeftRight()
    {
        $bean = new CategoryMock();
        $bean->name = 'SugarCategoryRoot' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean($bean->saveAsRoot());

        $bean->shiftLeftRightMock(2, 2);
        $bean = BeanFactory::retrieveBean('Categories', $bean->id, array(
            'use_cache' => false,
        ));

        $this->assertEquals(
            array('1', '4'), array($bean->lft, $bean->rgt)
        );
    }

    /**
     * Test adding new node using Category::addNode method.
     */
    public function testAddNode()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $this->assertTrue($subnode->lvl == 1);
        $this->assertTrue($subnode->lft == 2);
        $this->assertTrue($subnode->rgt == 3);
        $this->assertFalse($subnode->isRoot());
    }

    /**
     * Test throwing an Exception during adding existing node using Category::addNode method.
     */
    public function testAddExistingNodeException()
    {
        $subnode = new CategoryMock();
        $subnode->id = create_guid();
        $this->setExpectedException('Exception');
        self::$root->addNodeMock($subnode, 2, 1);
    }

    /**
     * Test throwing an Exception during adding deleted node using Category::addNode method.
     */
    public function testAddDeletedNodeException()
    {
        $subnode = new CategoryMock();
        $subnode->deleted = 1;
        $this->setExpectedException('Exception');
        self::$root->addNodeMock($subnode, 2, 1);
    }

    /**
     * Test throwing an Exception during adding node to deleted node using Category::addNode method.
     */
    public function testAddNodeToDeletedException()
    {
        self::$root->deleted = 1;
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . mt_rand();

        $this->setExpectedException('Exception');
        self::$root->addNodeMock($subnode, 2, 1);
    }

    /**
     * Test retrieve a valid tree data using Category::getTree method.
     */
    public function testGetTree()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $tree = self::$root->getTree();

        $this->assertInternalType('array', $tree);
        $node = current($tree);
        $this->assertTrue(array_key_exists('children', $node));
        $this->assertTrue(array_key_exists('root', $node));
        $this->assertEquals(self::$root->id, $node['root']);
        $this->assertInternalType('array', $node['children']);
    }

    /**
     * Test retrieve a valid children data using Category::getChildren method.
     */
    public function testGetChildren()
    {
        $this->assertInternalType('array', self::$root->getChildren());
        $this->assertInternalType('array', self::$root->getChildren(1));
    }

    /**
     * Test retrieve a valid next sibling of node using Category::getNextSibling method.
     */
    public function testGetNextSibling()
    {
        $this->assertInternalType('null', self::$root->getNextSibling());

        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $subnode2 = new CategoryMock();
        $subnode2->name = 'SugarCategory' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode2, 2, 1));

        $subnode = BeanFactory::retrieveBean('Categories', $subnode->id, array(
            'use_cache' => false,
        ));

        $subnode2 = BeanFactory::retrieveBean('Categories', $subnode2->id, array(
            'use_cache' => false,
        ));

        $result = $subnode2->getNextSibling();
        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals($subnode->id, $result['id']);
    }

    /**
     * Test retrieve a valid previous sibling of node using Category::getPrevSibling method.
     */
    public function testGetPrevSibling()
    {
        $this->assertInternalType('null', self::$root->getPrevSibling());

        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $subnode2 = new CategoryMock();
        $subnode2->name = 'SugarCategory' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode2, 2, 1));

        $subnode = BeanFactory::retrieveBean('Categories', $subnode->id, array(
            'use_cache' => false,
        ));

        $subnode2 = BeanFactory::retrieveBean('Categories', $subnode2->id, array(
            'use_cache' => false,
        ));

        $result = $subnode->getPrevSibling();
        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals($subnode2->id, $result['id']);
    }

    /**
     * Test retrieve a valid parents of node using Category::getParents method.
     */
    public function testGetParents()
    {
        $this->assertInternalType('array', self::$root->getParents());
        $this->assertInternalType('array', self::$root->getParents(1));
    }

    /**
     * Test retrieve a valid data using Category::isDescendantOf method.
     */
    public function testIsDescendantOf()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $root = BeanFactory::retrieveBean('Categories', self::$root->id, array(
            'use_cache' => false,
        ));

        $this->assertTrue($subnode->isDescendantOf($root));
        $this->assertFalse($root->isDescendantOf($subnode));
    }

    /**
     * Test moving node in tree using Category::moveNode method.
     */
    public function testMoveNode()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . mt_rand();
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $subnode->moveNodeMock(self::$root, 2, 1);
        $root = BeanFactory::retrieveBean('Categories', self::$root->id, array(
            'use_cache' => false,
        ));

        $this->assertEquals($root->id, $subnode->root);
        $this->assertEquals($root->lft + 1, $subnode->lft);
        $this->assertEquals($root->rgt - 1, $subnode->rgt);
    }

    /**
     * Test deleting node using Category::mark_deleted method.
     */
    public function test_mark_deleted()
    {
        $result = self::$root->mark_deleted(self::$root->id);
        $this->assertInternalType('null', $result);
    }

}
