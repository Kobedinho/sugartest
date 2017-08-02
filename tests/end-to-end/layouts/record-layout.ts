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
import HeaderView from '../views/header-view';
import {BaseView} from '@sugarcrm/seedbed';
import RecordView from '../views/record-view';

/**
 * Represents a Detail/Record page layout.
 *
 * @class RecordLayout
 * @extends BaseView
 */
export default class RecordLayout extends BaseView {

    public HeaderView: HeaderView;
    private type: string;
    public RecordView: RecordView;
    public defaultView: RecordView;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.main-pane'
        });

        this.type = 'record';

        this.defaultView = this.RecordView = this.createComponent<RecordView>(RecordView, {
            module: options.module,
            default: true
        });

        this.HeaderView = this.createComponent<HeaderView>(HeaderView, {
            module: options.module,
        });

    }
}
