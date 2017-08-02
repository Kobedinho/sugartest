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
import ListView from '../views/list-view';
import FilterView from '../views/filter-view';
import HeaderView from '../views/header-view';

/**
 * Represents List page layout.
 *
 * @class ListLayout
 * @extends BaseView
 */
export default class ListLayout extends BaseView {

    public type = 'list';
    public FilterView: FilterView;
    public ListView: ListView;
    public defaultView: ListView;
    public HeaderView: HeaderView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.main-pane:not([style*="display: none"])'
        });

        this.FilterView = this.createComponent<FilterView>(FilterView, { module: options.module });
        this.HeaderView = this.createComponent<HeaderView>(HeaderView);
        this.defaultView = this.ListView = this.createComponent<ListView>(ListView, { module: options.module, default: true });

    }
}
