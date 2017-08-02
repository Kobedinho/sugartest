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


class SugarTestNoteUtilities
{
    /**
     * @var array
     */
    private static $createdNotes = array();

    /**
     * Creates a new Note with optional default values.
     * @param string $id
     * @param array $noteValues
     * @return mixed
     */
    public static function createNote($id = '', $noteValues = array())
    {
        $time = mt_rand();
        $note = \BeanFactory::newBean('Notes');

        $noteValues = array_merge(array(
            'name' => "TestNote_{$time}",
        ), $noteValues);

        foreach ($noteValues as $property => $value) {
            $note->$property = $value;
        }

        if (!empty($id)) {
            $note->new_with_id = true;
            $note->id = $id;
        }
        $note->save();
        $GLOBALS['db']->commit();
        self::$createdNotes[] = $note;
        return $note;
    }

    /**
     * Creates a set of Notes.
     * @param array $ids
     */
    public static function setCreatedNotes(array $ids)
    {
        foreach ($ids as $id) {
            $note = \BeanFactory::newBean('Note');
            $note->id = $id;
            self::$createdNotes[] = $note;
        }
    }

    /**
     * Cleans created Notes.
     */
    public static function removeAllCreatedNotes()
    {
        $ids = self::getCreatedNoteIds();
        $GLOBALS['db']->query('DELETE FROM notes WHERE id IN (\'' . implode("', '", $ids) . '\')');
        foreach ($ids as $id) {
            if (file_exists('upload://' . $id)) {
                unlink('upload://' . $id);
            }
        }
    }

    /**
     * Returns ids of all created Notes.
     * @return array
     */
    public static function getCreatedNoteIds()
    {
        $ids = array();
        foreach (self::$createdNotes as $note) {
            $ids[] = $note->id;
        }
        return $ids;
    }
}
