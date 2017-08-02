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


class SugarTestKBContentUtilities
{

    protected static $_createdKbContentIds = array();
    protected static $_createdKbDocumentIds = array();
    protected static $_createdKbArticleIds = array();

    private function __construct() {}

    public static function createBean($values = array(), $save = true)
    {
        $defaults = array(
            'name' => 'SugarKBContent' . time(),
        );

        $values = array_merge($defaults, $values);
        $bean = new KBContentMock();
        $bean->populateFromRow($values);
        if ($save) {
            self::saveBean($bean);
        }
        return $bean;
    }

    /**
     * Separate save method.
     *
     * @param KBContentMock $bean
     */
    public static function saveBean(KBContentMock $bean)
    {
        $bean->save();
        DBManagerFactory::getInstance()->commit();
        self::$_createdKbContentIds[] = $bean->id;
        self::$_createdKbArticleIds[] = $bean->kbarticle_id;
        self::$_createdKbDocumentIds[] = $bean->kbdocument_id;
    }

    public static function removeAllCreatedBeans()
    {
        $db = DBManagerFactory::getInstance();

        $db->query('DELETE FROM kbdocuments WHERE id IN (' . static::getPreparedIdsString(self::$_createdKbDocumentIds) . ')');
        $db->query('DELETE FROM kbarticles WHERE id IN (' . static::getPreparedIdsString(self::$_createdKbArticleIds) . ')');

        $conditions = static::getPreparedIdsString(self::$_createdKbContentIds);
        $db->query('DELETE FROM kbcontents WHERE id IN (' . $conditions . ')');
        $db->query('DELETE FROM kbcontents_audit WHERE parent_id IN (' . $conditions . ')');

        self::$_createdKbDocumentIds = array();
        self::$_createdKbArticleIds = array();
        self::$_createdKbContentIds = array();
    }

    /**
     * Prepare special string of quoted unique ids
     * to use in 'IN' part of db request.
     *
     * @param array $ids
     * @return string
     */
    protected static function getPreparedIdsString(array $ids)
    {
        $db = DBManagerFactory::getInstance();
        return implode(',', array_map(array($db, 'quoted'), array_unique($ids)));
    }
}

class KBContentMock extends KBContent
{
    public $updatedCategories = array();

    public function resetActiveRevision()
    {
        $this->resetActiveRev();
    }

    public function updateCategoryExternalVisibility($categoryId)
    {
        $this->updatedCategories[] = $categoryId;
        parent::updateCategoryExternalVisibility($categoryId);
    }
}
