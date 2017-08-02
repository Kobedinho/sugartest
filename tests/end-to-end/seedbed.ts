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

import {seedbed} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import LoginLayout from './layouts/login-layout';
import RecordLayout from './layouts/record-layout';
import ListLayout from './layouts/list-layout';
import PreviewLayout from './layouts/preview-layout';

/*runs as soon as log in page is loaded and metadata that is available at that moment saved*/
seedbed.addAsyncHandler(seedbed.events.BEFORE_INIT, async() => {

    seedbed.defineComponent('Login', LoginLayout, {module: 'Login'});

    await seedbed.api.updatePreferences({
        preferences: seedbed.config.users.default.defaultPreferences,
    });
});

// is called after cukes init, one time
seedbed.addAsyncHandler(seedbed.events.AFTER_INIT, () => {

    /*cache drawers for modules*/
    _.each(seedbed.meta.modules, (module, moduleName) => {

        seedbed.defineComponent(`${moduleName}List`, ListLayout, { module : moduleName });

        // If module supports "RecordLayout" let's pre-create it
        if (module.views && module.views.record) {
            seedbed.defineComponent(`${moduleName}Record`, RecordLayout, { module : moduleName});
        }
    });

});

/**
 * After login we need to define layouts
 * based on cached records test created
 */
seedbed.addAsyncHandler(seedbed.events.LOGIN, () => {
    seedbed.cachedRecords.iterate((record, recordAlias) => {

        if (record.module) {

            // Define Detail Layout for cached record
            seedbed.defineComponent(`${recordAlias}Record`, RecordLayout, {
                module: record.module,
                id: record.id
            });

            seedbed.components[`${record.module}List`].ListView.createListItem(record);
        }

    }, this);

});

// is called after waitForApp, each time
seedbed.addAsyncHandler(seedbed.events.SYNC, (clientInfo) => {

    /*we need this logic only for offline mode*/
    let item: any = _.last(clientInfo.create);

    if (item) {
        /*find record info for created record*/
        let recordInfo: any = _.find(seedbed.scenario.recordsInfo, (record: any) => {
            /*
             We need to make sure we find correct record to be updated
             Why need this fix: Sugar do POST requests on Dashboards to create them, if not available (for new installs)
             Those POST requests are pushed to clientInfo.create and assigned to wrong seedbed.scenario.recordsInfo[] elements
             */
            return !record.recordId && item._module && item._module === record.module;
        });

        if (recordInfo && !seedbed.cachedRecords.contains(recordInfo.uid)) {

            seedbed.cachedRecords.push(
                recordInfo.uid,
                {
                    input: recordInfo.input,
                    id: item.id,
                    module: recordInfo.module
                }
            );

            seedbed.defineComponent(`${recordInfo.uid}Record`, RecordLayout, {
                module: recordInfo.module,
                id: item.id
            });

        }
    }
});

seedbed.addAsyncHandler(seedbed.events.RESPONSE, (data, req, res) => {

    let url = req.url,
        responseData;

    /*Cache Activities records when Activities stream is loaded*/
    if ((parseInt(res.statusCode, 10) === 200) &&
        _.includes(['POST', 'PUT'], req.method) &&
        !/(oauth2|bulk|filter)/.test(url)) {

        responseData = JSON.parse(data.buffer.toString());

        let responseRecord = responseData.related_record || responseData;

        if (_.includes(['POST'], req.method) && url.indexOf('/file/filename') === -1) {

            /*find record info for created record*/
            let recordInfo: any = _.find(seedbed.scenario.recordsInfo, (record: any) => {
                return responseRecord && responseRecord.id && responseRecord.id === record.recordId;
            });

            /*save record in cachedRecords by uid*/
            if (recordInfo && recordInfo.uid) {

                let record = seedbed.cachedRecords.push(recordInfo.uid, {
                    input: recordInfo.input,
                    id: responseRecord.id,
                    module: recordInfo.module
                });

                seedbed.defineComponent(`${recordInfo.uid}Record`, RecordLayout, {
                    module: record.module,
                    id: record.id,
                });

                seedbed.defineComponent(`${recordInfo.uid}Preview`, PreviewLayout, {
                    module: record.module,
                    id: record.id,
                });

            }
        }
    }
});
