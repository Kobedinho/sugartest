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

const _ = require('lodash');
const path = require('path');
const fs = require('fs-extra');
const utils = require('./utils/cli.js');

const CUKES_PATH = __dirname;
const CI_RESULTS_FOLDER = path.resolve(CUKES_PATH, 'results', 'ci-results');
const CUKES_STDOUT_PATH = path.join(CI_RESULTS_FOLDER, 'cukes.stdout');
const CUKES_STDERR_PATH = path.join(CI_RESULTS_FOLDER, 'cukes.stderr');

var tasks = [];

tasks.push({
    name: 'runSugarScenarios',
    type: 'tests',
    features: CUKES_PATH + '/features',
    proceedNextTaskOnFailure: true,
    app: 'Sugar',
    args: _.concat(process.argv, [
        '--sp', utils.getArgument('-u', '--url'),
        '--cfg', CUKES_PATH + '/' + utils.getConfigFile(),
        '-t', '@crud',
        '-p', 'false'
    ]),
    extendsArgv: true
});


module.exports = {

    // store ci-results.zip
    resultsFolder: CI_RESULTS_FOLDER,

    // output log files for stdout and stderr
    outs: {
        stdout: CUKES_STDOUT_PATH,
        stderr: CUKES_STDERR_PATH
    },


    // copy results failures into ci-results folder and archive content
    resultsConfig: [
        {
            src: path.join(CUKES_PATH, 'results_failures'),
            dest: path.join(CI_RESULTS_FOLDER)
        }
    ],

    tasks: tasks
};
