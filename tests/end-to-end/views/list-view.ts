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
 Represents List view PageObject.
 */

import BaseListView from './baselist-view';

/**
 * @class SugarCukes.ListView
 * @extends SugarCukes.BaseListView
 */
export default class ListView extends BaseListView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $ : '.flex-list-view:not([style*="display: none"])',
                errorBox: '.error-box',
                unlink: '.icon-minus-circle',
                showMoreBottom: '.show-more-bottom-btn',
                showMoreTop: '.show-more-top-btn',
                list : {
                allRows : 'tr[name*="{{module}}"]',
                    row : 'tr[name*="{{module}}"]:nth-child({{index}})'
            }
        });

    }
}
