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
'use strict';

import * as path from 'path';
const ciConfig = require('./ci-config.js');
import {CIUtils, Utils, ci} from '@sugarcrm/seedbed';
const chalk = Utils.chalk;
import * as Bluebird from 'bluebird';
const fs = Bluebird.promisifyAll(require('fs-extra'));

let zipResults = async() => {
    await CIUtils.zipCiResults(
        {
            testsOutputFolders: ciConfig.resultsConfig,
            resultsFolder: ciConfig.resultsFolder
        });

    console.log(`zip artifacts: ${chalk.green('success')}`);
};

export default async () => {
        /*create results folder*/
        fs.emptyDirSync(ciConfig.resultsFolder);

        /*start CI procedure*/
        try {
            try {

                await ci(ciConfig);

            } finally {

                await zipResults();
                await fs.removeAsync(path.resolve(ciConfig.resultsFolder));
            }
        } catch (error) {
            console.log(`${error.logs || error.stack || ''}`);
            process.exit(1);
        }

    };


/*handle errors*/
process.on('unhandledRejection', (error, p) => {
    console.log(`Unhandled Rejection at: Promise ${p}
${error.stack || error}`);

    zipResults()
        .then(() => {
            process.exit(1);
        });
});
