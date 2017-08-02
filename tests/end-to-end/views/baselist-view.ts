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
 Represents a base list that is a parent for home, recents, list views.
 */

import BaseView from './base-view';
import * as _ from 'lodash';
import ListItemView from './list-item-view';

/**
 * @class BaseListView
 * @extends BaseView
 */
export default class BaseListView extends BaseView {

    public listItems: ListItemView [] = [];

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            noRecordsFound: '.no-records-found',
                pullToRefreshStartPosition: '.items article:nth-child(1)',

                contextMenu: {
                $: '.menu-container.on',
                    edit: '.edit-item i',
                    delete: '.delete-item i',
                    unlink: '.unlink-item i',
                    grip: '.grip',
                    follow: '.icon-check-circle-o',
                    unfollow: '.icon-check-circle'
            }
        });

    }

    public getListItem (conditions) {
        let keys = _.keys(conditions);

        if (keys.length !== 1 || !_.includes(['id', 'index', 'current'], keys[0])) {
            return null;
        } else {
            let listItems = _.filter(this.listItems, conditions),
                listViewItem = listItems.length ? listItems[0] : null;

            if (!listViewItem) {
                listViewItem = this.createListItem(conditions);
            }
            return listViewItem;
        }
    }

    public createListItem (conditions) {

        if (!(conditions || conditions.id)) {
            return null;
        }

        let listViewItem = this.createComponent<ListItemView>(ListItemView, {
            id: conditions.id,
            module: this.module,
        });

        this.listItems.push(listViewItem);

        return listViewItem;
    }
}
