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

class ProjectTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function tearDownAfterClass()
    {
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();

        parent::tearDownAfterClass();
    }

    public function testRemoval()
    {
        $project = SugarTestProjectUtilities::createProject();
        $task = SugarTestProjectTaskUtilities::createProjectTask(array(
            'project_id' => $project->id,
        ));

        $project->mark_deleted($project->id);

        $this->assertNull(BeanFactory::retrieveBean($task->module_name, $task->id));
    }
}
