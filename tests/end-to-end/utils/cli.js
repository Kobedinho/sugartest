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
const Cukes = require('@sugarcrm/seedbed');
const seedbedUtils = Cukes.Utils;

var cliHelper = {

    /**
     * Get configuration file name depends on CLI arguments
     *
     * @returns {string}
     */
    getConfigFile : function () {
        var file = 'config-ci.js';

        if (seedbedUtils.isTrue(this.getArgument('--dev', '--dev'))) {
            file = 'config.js';
        } else if (seedbedUtils.isTrue(this.getArgument('--tidbit', '--tidbit'))) {
            file = 'config-tidbit.js';
        }

        return file;
    },

    /**
     * Get CLI Argument value
     *
     * @param argShort
     * @param argFull
     * @returns {*}
     */
    getArgument : function(argShort, argFull) {
        var index = 0, result;

        _.find(process.argv, function (arg) {
            if (arg.indexOf(argShort) !== -1 || arg.indexOf(argFull) !== -1) {
                return true;
            }
            index++;
        });

        if (index > 0) {
            result = process.argv[index + 1];
        }

        return result;
    }
};

module.exports = cliHelper;
