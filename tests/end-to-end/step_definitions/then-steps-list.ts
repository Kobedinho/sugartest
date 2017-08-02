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

import * as _ from 'lodash';
import {thenStepsHelper} from '@sugarcrm/seedbed';

const steps = function () {

    /**
     * Step verifies fields visible on a cached list view for the cached record.
     *
     * @example "I verify fields for *Account_A in #AccountsList:"
     */
    this.Then(/^I verify fields for (\*[A-Z](?:\w|\S)*) in (#[A-Z](?:\w|\S)*)$/,
        async(record, view, data) => {

            let listItem = view.getListItem({id: record.id});

            let errors = await listItem.checkFields(data.hashes());

            let message = '';
            _.each(errors, (item) => {
                message += item;
            });

            if (message) {
                throw new Error(message);
            }

        });

    /**
     * Verify record exists on #View
     *
     * @example "I should see *Account_A in #AccountsList"
     */
    this.Then(/^I should (not )?see (\*[A-Z](?:\w|\S)*) in (#[A-Z](?:\w|\S)*)$/,
        async(not, record, view) => {

            let listItem = view.getListItem({id: record.id}, record);

            let value = await listItem.isVisibleView();

            if (_.isEmpty(not) !== value) {
                throw new Error('Expected ' + (not || '') + ' to see list item (' + listItem.$() + ')');
            }

        });

    this.Then(/^I should be redirected to \"(.*)\" route/,
        (expectedRoute: string): Promise<void> =>
            thenStepsHelper.checkUrlHash(expectedRoute));

};

module.exports = steps;
