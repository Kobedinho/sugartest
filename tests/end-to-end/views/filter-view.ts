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
/*
 Represents Filter view PageObject on ListView Layout.
 */

import {seedbed} from '@sugarcrm/seedbed';
import BaseView from './base-view';

/**
 * @class FilterView
 * @extends BaseView
 */
export default class FilterView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.search-filter',
            searchField: '.search-name'
        });
    }

    /**
     * Set Search field name with "value"
     *
     * @param value
     * @returns {*}
     */
    public async setSearchField (value) {
        let locator = this.$('searchField');

        return seedbed.client
            .waitForVisible(locator)
            .setValue(locator, value);
    }
}
