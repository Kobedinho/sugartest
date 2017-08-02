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

import {BaseView} from '@sugarcrm/seedbed';
import * as _ from 'lodash';

const thenSteps = function () {

    /**
     * Check whether the cached view is visible
     * Note: for *Edit and *Detail views the opened url is checked to have an id form the cached record
     *
     * @example "I should see #AccountsList view"
     */
    this.Then(/^I should (not )?see (#\S+) view$/,
        async(not, view: BaseView) => {
            let isVisible = await view.isVisibleView();

            if (!not !== isVisible) {
                throw new Error('Expected ' + (not || '') + 'to see "' + view.$() + '" view(layout)');
            }

        });

    /**
     * Verifies fields visible on a cached view for the cached record.
     *
     * @example "I verify fields on #Account_APreview.PreviewView"
     */
    this.Then(/^I verify fields on (#[A-Z](?:\w|\S)*)$/,
        async(view, data) => {

            let errors = await view.checkFields(data.hashes());

            let message = '';
            _.each(errors, (item) => {
                message += item;
            });

            if (message) {
                throw new Error(message);
            }

        });
};

module.exports = thenSteps;
