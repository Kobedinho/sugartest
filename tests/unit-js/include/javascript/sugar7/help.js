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
describe('Sugar7 Help Extension', function () {
    var app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        sinon.collection.stub(app.metadata, 'getModuleNames').returns([
            'Accounts',
            'Bugs',
            'Cases',
            'Contacts',
            'RevenueLineItems'
        ]);
    });

    afterEach(function () {
        app.help.clearModuleLabelMap();
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
    });

    describe('help.get', function() {
        it('should return the correct language strings', function() {
            var helpText = app.help.get('Accounts', 'Record');

            expect(helpText.body).toEqual('Accounts Help Record Body');
        });

        it('should return undefined for body', function() {
            var helpText = app.help.get('Accounts', 'Compose');

            expect(helpText.body).toBeUndefined();
        });

        it('should fall back to defaults when not found in module', function() {
            var helpText = app.help.get('RevenueLineItems', 'Record');

            expect(helpText.body).toEqual("Default Help Record Body");
        });
    });

    describe('help.get module substitution', function() {
        it('should return the correct language strings for current module', function() {
            var helpText = app.help.get('Accounts', 'Records');

            expect(helpText.body).toEqual('Account Help Records Body');
        });

        it('should return the correct language strings with other module names', function() {
            var helpText = app.help.get('Accounts', 'Create');

            expect(helpText.body).toEqual('My Revenue Line Items');
        });
    });

    describe('clearModuleLabelMap', function() {
        it('should set moduleLabelMap to undefined', function() {
            // should be undefined to start with
            expect(app.help._moduleLabelMap).toBeUndefined();
            var helpText = app.help.get('Accounts', 'Create');
            // we should have something after the initial call was made
            expect(app.help._moduleLabelMap).not.toBeUndefined();
            app.help.clearModuleLabelMap();

            // should be set back to undfined now
            expect(app.help._moduleLabelMap).toBeUndefined();
        });
    });
});
