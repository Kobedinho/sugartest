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
import BaseView from './base-view';
import {seedbed} from '@sugarcrm/seedbed';

/**
 * @class ListItemView
 * @extends BaseView
 */
export default class ListItemView extends BaseView {

    public id: string;
    public index: number;
    public current: boolean;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: 'tr[name*="{{id}}"]',
            listItem: {
                listItemName: 'a[href*="{{id}}"]',
                listItemGrip: '.menu-container-grip',
                buttons: {
                    preview: '.fa.fa-eye',
                }
            },
            buttons: {
                addRow: '.addBtn'
            }
        });

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }

    /**
     * Click on list view item list element (name in most cases)
     *
     * @returns {*}
     */
    public async clickListItem() {

        let selector = this.$('listItem.listItemName', {id: this.id});
        let rowSelector = this.$();

        return seedbed.client
            .execSync('scrollToSelector', [rowSelector])
            .click(selector);
    }

    public async clickPreviewButton() {

        let selector = this.$('listItem.buttons.preview', {id: this.id});
        let rowSelector = this.$();

        return seedbed.client
            .execSync('scrollToSelector', [rowSelector])
            .click(selector);
    }

}
