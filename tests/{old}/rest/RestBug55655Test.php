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

require_once('tests/{old}/rest/RestTestPortalBase.php');

/**
 * Bug 55655 
 * 
 * Portal user could not add an attachment to an existing note. This tests
 * being able to add an attachment to an existing note but not being able
 * to edit that attachment. 
 */
class RestBug55655Test extends RestTestPortalBase
{
    protected $_testfile1 = 'Bug55655-01.txt';
    protected $_testfile2 = 'Bug55655-02.txt';
    
    public function setUp()
    {
        parent::setUp();
        
        // Create two sample text files for uploading
        sugar_file_put_contents($this->_testfile1, create_guid());
        sugar_file_put_contents($this->_testfile2, create_guid());
    }
    
    public function tearDown()
    {
        unlink($this->_testfile1);
        unlink($this->_testfile2);
        parent::tearDown();
    }
    
    /**
     * @group rest
     */
    public function testAddingNoteAttachmentToBugAsSupportPortal()
    {
        $bugReply = $this->_restCall(
            "Bugs/",
            json_encode(array(
                'name' => 'UNIT TEST CREATE BUG PORTAL USER', 
                'portal_viewable' => '1', 
                'team_id' => '1'
            )),
            'POST'
        );
        
        $this->bugId = $bugReply['reply']['id'];
        
        // Create a note on the bug without an attachment
        $bugNoteReply = $this->_restCall(
            "Bugs/{$this->bugId}/link/notes",
            json_encode(array(
                'name' => 'UNIT TEST BUG NOTE PORTAL USER',
                'portal_flag' => '1'
            )),
            'POST'
        );
        
        $this->noteId = $bugNoteReply['reply']['related_record']['id'];
                
        // Create the attachment
        $post = array('filename' => '@' . $this->_testfile1);
        $restReply = $this->_restCall('Notes/' . $this->noteId . '/file/filename', $post);
        $this->assertArrayHasKey('filename', $restReply['reply'], 'Reply is missing file name key');
        $this->assertNotEmpty($restReply['reply']['filename']['name'], 'File name returned empty');

        // Now get the note to make sure it saved
        $fetch = $this->_restCall('Notes/' . $this->noteId);
        $this->assertNotEmpty($fetch['reply']['id'], 'Note id not returned');
        $this->assertEquals($this->noteId, $fetch['reply']['id'], 'Known note id and fetched note id do not match');
        $this->assertEquals($restReply['reply']['filename']['name'], $fetch['reply']['filename']);
        
        // Now edit this attachment
        $params = array('filename' => $this->_testfile2, 'type' => 'text/plain');
        $restReply = $this->_restCallFilePut('Notes/' . $this->noteId . '/file/filename', $params);
        // This should fail like a savage
        $this->assertArrayHasKey('error', $restReply['reply'], 'There is no error reply in the response');
        $this->assertEquals('not_authorized', $restReply['reply']['error'], 'Error returned is not not_authorized');
        
        // Lastly check the note again
        $fetch = $this->_restCall('Notes/' . $this->noteId);
        $this->assertNotEmpty($fetch['reply']['id'], 'Note id not returned');
        $this->assertEquals($this->noteId, $fetch['reply']['id'], 'Known note id and fetched note id do not match');
        $this->assertArrayHasKey('filename', $fetch['reply'], 'Filename field was not returned in Note fetch');
        $this->assertEquals($fetch['reply']['filename'], $this->_testfile1, 'Filename was changed when it should not have been');
    }
}