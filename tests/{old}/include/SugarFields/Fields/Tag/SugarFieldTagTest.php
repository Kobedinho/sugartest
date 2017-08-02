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
 * Test SugarFieldTag class
 */
class SugarFieldTagTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Fixtures
     * @var SugarBean
     */
    protected $contact;
    protected $tag1;
    protected $tag2;

    public static function setUpBeforeClass()
    {
        VardefManager::loadVardef('Tags', 'Tag', true);
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        // create fixtures
        $this->contact = SugarTestContactUtilities::createContact();
        $this->tag1 = SugarTestTagUtilities::createTag();
        $this->tag2 = SugarTestTagUtilities::createTag();
    }

    public function tearDown()
    {
        SugarTestTagUtilities::deleteM2MRelationships('Contacts', $this->contact->id);
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTagUtilities::removeAllCreatedTags();

        SugarTestHelper::tearDown();
        parent::tearDown();
    }


    /**
     * Api format integration test.
     *
     * Create contact and several tags for that contact.
     * Call apiFormatField for tags_link.
     * Expect tags data will be present after apiFormatField
     */
    public function testApiFormatFieldIntegration()
    {

        // Link tag1 and tag2 to contact
        $relName = 'tag_link';
        $this->contact->load_relationship($relName);
        $this->contact->$relName->add($this->tag1);
        $this->contact->$relName->add($this->tag2);

        //Clean up any hanging related records.
        SugarRelationship::resaveRelatedBeans();

        // This forces a re-retrieval of the bean from the database
        BeanFactory::unregisterBean($this->contact);

        // call api format for tags field
        $tags = new SugarFieldTag('tag');

        $data = array();

        $tags->apiFormatField($data, $this->contact, array(), $relName, array('link' => $relName));

        // expect tags data
        $this->assertArrayHasKey($relName, $data);
        $this->assertCount(2, $data[$relName]);
    }

    /**
     * Integration test on field save
     */
    public function testApiSaveIntegration()
    {
        // Link tag1 to contact
        $relName = 'tag_link';
        $this->contact->load_relationship($relName);
        $this->contact->$relName->add($this->tag1);

        //Clean up any hanging related records.
        SugarRelationship::resaveRelatedBeans();

        // This forces a re-retrieval of the bean from the database
        BeanFactory::unregisterBean($this->contact);

        // call api format for tags field
        $tags = new SugarFieldTag('tag');

        // create new Name Tag
        $newTagName = SugarTestTagUtilities::createNewTagName();

        $params['tag'] = array(
            // add new tag which exists in system
            array(
                'id' => $this->tag2->id,
                'name' => $this->tag2->name,
            ),
            // add new tag which does not exist in system
            $newTagName,
        );

        $tags->apiSave($this->contact, $params, 'tag', array('link' => $relName));

        // refresh contact bean
        BeanFactory::unregisterBean($this->contact);
        $contact = BeanFactory::getBean('Contacts', $this->contact->id);
        $contact->load_relationship($relName);
        $tags = $contact->$relName->getBeans();

        // check results
        $this->assertCount(2, $tags);
        $this->assertArrayHasKey($this->tag2->id, $tags);
        $this->assertArrayNotHasKey($this->tag1->id, $tags);

        // verify we have our "new" tag
        unset($tags[$this->tag2->id]);
        $new = array_pop($tags);
        $this->assertEquals($newTagName, $new->name);
    }

    /**
     * @dataProvider getChangedValuesProvider
     */
    public function testGetChangedValues(array $initial, array $changed, $expected)
    {
        $tags = new SugarFieldTag('Tag');
        list($addedTags, $removedTags) = $tags->getChangedValues($initial, $changed);
        $this->assertEquals($expected, array($addedTags, $removedTags));


    }

    public function getChangedValuesProvider()
    {
        return array(
            array(
                array('apple' => 'Apple','pear' => 'Pear'),
                array('orange' => 'ORANGE','grape' => 'GrApE'),
                array(array('orange' => 'ORANGE' ,'grape' => 'GrApE'),array('apple' => 'Apple','pear' => 'Pear')),
            ),
            array(
                array('apple' => 'Apple','pear' => 'Pear'),
                array('apple' => 'Apple', 'orange' => 'ORANGE','grape' => 'GrApE'),
                array(array('orange' => 'ORANGE' ,'grape' => 'GrApE'),array('pear' => 'Pear')),
            ),
        );
    }

    public function testGetOriginalTags()
    {

        $tags = new SugarFieldTag('Tag');
        $currRelBeans = array(
            $this->tag1->id => $this->tag1,
            $this->tag2->id => $this->tag2,
        );
        $expected = array(
            strtolower($this->tag1->name) => $this->tag1->name,
            strtolower($this->tag2->name) => $this->tag2->name,
        );
        $originalTags = $tags->getOriginalTags($currRelBeans);
        $this->assertEquals($expected, $originalTags);
    }
}
