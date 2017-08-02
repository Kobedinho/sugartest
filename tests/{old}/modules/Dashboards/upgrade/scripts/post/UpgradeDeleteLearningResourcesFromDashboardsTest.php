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

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'modules/Dashboards/upgrade/scripts/post/7_DeleteLearningResourcesFromDashboards.php';

class UpgradeDeleteLearningResourcesFromDashboardsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestDeleteLearningResources
     */
    public function testDeleteLearningResources($metadata, $expect)
    {
        $script = $this->getMockBuilder('\SugarUpgradeDeleteLearningResourcesFromDashboards')
            ->setMethods(array('__call'))
            ->disableOriginalConstructor()
            ->getMock();
        $metadata = json_decode($metadata);
        $script->deleteLearningResources($metadata);
        $metadata = json_encode($metadata);
        $this->assertEquals($expect, $metadata);
    }

    public function providerTestDeleteLearningResources()
    {
        return array(
            // [ Learning Resources, Twitter, Dashable List ],
            // [ Pipeline, Top 10 Sales ]
            // ->
            // [ Twitter, Dashable List ],
            // [ Pipeline, Top 10 Sales ]
            array(
                '{"components":[{"rows":[[{"view":{"type":"learning-resources",'.
                '"label":"LBL_LEARNING_RESOURCES_NAME"},"width":12}],[{"view":{"type":'.
                '"twitter","label":"LBL_DASHLET_RECENT_TWEETS_SUGARCRM_NAME","twitter":'.
                '"sugarcrm","limit":20},"width":12}],[{"view":{"type":"dashablelist",'.
                '"label":"TPL_DASHLET_MY_MODULE","display_columns":["full_name",'.
                '"account_name","phone_work","title"],"limit":15},"context":{'.
                '"module":"Contacts"},"width":12}]],"width":4},{"rows":[[{"view":{'.
                '"type":"forecast-pipeline","label":"LBL_DASHLET_PIPLINE_NAME",'.
                '"visibility":"user"},"context":{"module":"Forecasts"},"width":12}],'.
                '[{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME",'.
                '"filter_duration":"current","visibility":"user"},"width":12}]],"width":8}]}',
                '{"components":[{"rows":[[{"view":{"type":'.
                '"twitter","label":"LBL_DASHLET_RECENT_TWEETS_SUGARCRM_NAME","twitter":'.
                '"sugarcrm","limit":20},"width":12}],[{"view":{"type":"dashablelist",'.
                '"label":"TPL_DASHLET_MY_MODULE","display_columns":["full_name",'.
                '"account_name","phone_work","title"],"limit":15},"context":{'.
                '"module":"Contacts"},"width":12}]],"width":4},{"rows":[[{"view":{'.
                '"type":"forecast-pipeline","label":"LBL_DASHLET_PIPLINE_NAME",'.
                '"visibility":"user"},"context":{"module":"Forecasts"},"width":12}],'.
                '[{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME",'.
                '"filter_duration":"current","visibility":"user"},"width":12}]],"width":8}]}',
            ),
            // [ Learning Resources, History ]
            // ->
            // [ History ]
            array(
                '{"components":[{"rows":[[{"width":6,"view":{"label":'.
                '"Learning Resources","type":"learning-resources",'.
                '"module":null}},{"width":6,"view":{"limit":"10","filter":"7",'.
                '"visibility":"user","label":"History","type":"history",'.
                '"module":null,"template":"tabbed-dashlet"}}]],"width":12}]}',
                '{"components":[{"rows":[[{"width":6,"view":{"limit":"10","filter":"7",'.
                '"visibility":"user","label":"History","type":"history",'.
                '"module":null,"template":"tabbed-dashlet"}}]],"width":12}]}',
            ),
            // [ Active Tasks ],
            // [ Active Tasks, Learning Resources, Active Tasks ],
            // [ Active Tasks, Active Tasks ]
            // ->
            // [ Active Tasks ],
            // [ Active Tasks, Active Tasks ],
            // [ Active Tasks, Active Tasks ]
            array(
                '{"components":[{"rows":[[{"width":6,"view":{"limit":10,'.
                '"visibility":"user","label":"Active Tasks","type":"active-tasks",'.
                '"module":null,"template":"tabbed-dashlet"}}],[{"width":4,"view":'.
                '{"limit":10,"visibility":"user","label":"Active Tasks","type":"active-tasks",'.
                '"module":null,"template":"tabbed-dashlet"}},{"width":4,"view":{'.
                '"label":"Learning Resources","type":"learning-resources","module":null}},'.
                '{"width":4,"view":{"limit":10,"visibility":"user","label":"Active Tasks",'.
                '"type":"active-tasks","module":null,"template":"tabbed-dashlet"}}],[{'.
                '"width":4,"view":{"limit":10,"visibility":"user","label":"Active Tasks",'.
                '"type":"active-tasks","module":null,"template":"tabbed-dashlet"}},{"width":4,'.
                '"view":{"limit":10,"visibility":"user","label":"Active Tasks","type":"active-tasks",'.
                '"module":null,"template":"tabbed-dashlet"}}]],"width":12}]}',
                '{"components":[{"rows":[[{"width":6,"view":{"limit":10,'.
                '"visibility":"user","label":"Active Tasks","type":"active-tasks",'.
                '"module":null,"template":"tabbed-dashlet"}}],[{"width":4,"view":'.
                '{"limit":10,"visibility":"user","label":"Active Tasks","type":"active-tasks",'.
                '"module":null,"template":"tabbed-dashlet"}},'.
                '{"width":4,"view":{"limit":10,"visibility":"user","label":"Active Tasks",'.
                '"type":"active-tasks","module":null,"template":"tabbed-dashlet"}}],[{'.
                '"width":4,"view":{"limit":10,"visibility":"user","label":"Active Tasks",'.
                '"type":"active-tasks","module":null,"template":"tabbed-dashlet"}},{"width":4,'.
                '"view":{"limit":10,"visibility":"user","label":"Active Tasks","type":"active-tasks",'.
                '"module":null,"template":"tabbed-dashlet"}}]],"width":12}]}',
            ),
            // [ Learning Resources, Activity Stream ],
            // [ Learning Resources ],
            // [ Inactive Tasks, Learning Resources, Active Tasks ]
            // ->
            // [ Activity Stream ],
            // [ Inactive Tasks, Active Tasks ]
            array(
                '{"components":[{"rows":[[{"width":6,"view":{"label":'.
                '"Learning Resources","type":"learning-resources","module":null}},'.
                '{"width":6,"context":{"module":"Activities"},"view":{"module":"Activities",'.
                '"limit":5,"label":"My Activity Stream","type":"activitystream-dashlet",'.
                '"auto_refresh":0,"currentFilterId":"all_records"}}],[{"width":12,"view":'.
                '{"label":"Learning Resources","type":"learning-resources","module":null}}],'.
                '[{"width":4,"view":{"limit":10,"visibility":"user","label":"Inactive Tasks",'.
                '"type":"inactive-tasks","module":null,"template":"tabbed-dashlet"}},{"width":4,'.
                '"view":{"label":"Learning Resources","type":"learning-resources","module":null}},'.
                '{"width":4,"view":{"limit":10,"visibility":"user","label":"Active Tasks",'.
                '"type":"active-tasks","module":null,"template":"tabbed-dashlet"}}]],"width":12}]}',
                '{"components":[{"rows":[['.
                '{"width":6,"context":{"module":"Activities"},"view":{"module":"Activities",'.
                '"limit":5,"label":"My Activity Stream","type":"activitystream-dashlet",'.
                '"auto_refresh":0,"currentFilterId":"all_records"}}],'.
                '[{"width":4,"view":{"limit":10,"visibility":"user","label":"Inactive Tasks",'.
                '"type":"inactive-tasks","module":null,"template":"tabbed-dashlet"}},'.
                '{"width":4,"view":{"limit":10,"visibility":"user","label":"Active Tasks",'.
                '"type":"active-tasks","module":null,"template":"tabbed-dashlet"}}]],"width":12}]}',
            ),
            // [ Learning Resources, Learning Resources ]
            // ->
            // (This space intentionally left blank)
            array(
                '{"components":[{"rows":[[{"width":6,"view":{"label":'.
                '"Learning Resources","type":"learning-resources",'.
                '"module":null}},{"width":6,"view":{"label":'.
                '"Learning Resources","type":"learning-resources",'.
                '"module":null}}]],"width":12}]}',
                '{"components":[{"rows":[],"width":12}]}',
            ),
            // [ Learning Resources ],
            // [ Learning Resources, Learning Resources ],
            // [ Learning Resources, Learning Resources, Learning Resources ],
            // [ Active Tasks ],
            // [ Inactive Tasks, Learning Resources ],
            // [ Learning Resources, Activity Stream, Learning Resources ]
            // ->
            // [ Active Tasks ],
            // [ Inactive Tasks ],
            // [ Activity Stream ]
            array(
                '{"components":[{"rows":[[{"width":12,"view":{"label":'.
                '"LearningResources","type":"learning-resources","module":'.
                'null}}],[{"width":6,"view":{"label":"LearningResources",'.
                '"type":"learning-resources","module":null}},{"width":6,'.
                '"view":{"label":"LearningResources","type":'.
                '"learning-resources","module":null}}],[{"width":4,"view":'.
                '{"label":"LearningResources","type":"learning-resources",'.
                '"module":null}},{"width":4,"view":{"label":'.
                '"LearningResources","type":"learning-resources","module":'.
                'null}},{"width":4,"view":{"label":"LearningResources","type":'.
                '"learning-resources","module":null}}],[{"width":12,"view":'.
                '{"limit":10,"visibility":"user","label":"ActiveTasks","type":'.
                '"active-tasks","module":null,"template":"tabbed-dashlet"}}],'.
                '[{"width":6,"view":{"limit":10,"visibility":"user","label":'.
                '"InactiveTasks","type":"inactive-tasks","module":null,'.
                '"template":"tabbed-dashlet"}},{"width":6,"view":{"label":'.
                '"LearningResources","type":"learning-resources","module":'.
                'null}}],[{"width":4,"view":{"label":"LearningResources",'.
                '"type":"learning-resources","module":null}},{"width":4,'.
                '"context":{"module":"Activities"},"view":{"module":'.
                '"Activities","limit":5,"label":"MyActivityStream","type":'.
                '"activitystream-dashlet","auto_refresh":0,"currentFilterId":'.
                '"all_records"}},{"width":4,"view":{"label":'.
                '"LearningResources","type":"learning-resources","module":'.
                'null}}]],"width":12}]}',
                '{"components":[{"rows":[[{"width":12,"view":{"limit":10,'.
                '"visibility":"user","label":"ActiveTasks","type":'.
                '"active-tasks","module":null,"template":"tabbed-dashlet"}}],'.
                '[{"width":6,"view":{"limit":10,"visibility":"user","label":'.
                '"InactiveTasks","type":"inactive-tasks","module":null,'.
                '"template":"tabbed-dashlet"}}],[{"width":4,"context":{'.
                '"module":"Activities"},"view":{"module":"Activities","limit":'.
                '5,"label":"MyActivityStream","type":"activitystream-dashlet",'.
                '"auto_refresh":0,"currentFilterId":"all_records"}}]],"width":'.
                '12}]}',
            ),
        );
    }
}
