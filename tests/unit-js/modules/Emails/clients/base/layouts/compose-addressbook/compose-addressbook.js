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
describe('Emails.Base.Layout.ComposeAddressbook', function() {
    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', 'Emails', 'compose-addressbook', null, null, true);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
        layout.dispose();
    });

    describe('sync', function() {
        var apiCallStub, parseOptionsStub;

        beforeEach(function() {
            apiCallStub = sinon.stub(app.api, "call");
            parseOptionsStub = sinon.stub(app.data, 'parseOptionsForSync', function(method, model, options) {
                return {
                    params: {
                        module_list: options.module_list.join(',')
                    }
                }
            });
        });

        afterEach(function() {
            apiCallStub.restore();
            parseOptionsStub.restore();
        });

        it('Should search for emails through the Mail API', function() {
            layout.collection.sync('read', layout.collection);
            expect(apiCallStub.calledOnce).toBe(true);
            expect(apiCallStub.args[0][1].indexOf('Mail/recipients/find')).not.toBe(-1);
        });

        it('Should search for emails in all allow modules when options.module_list is empty.', function() {
            layout.collection.sync('read', layout.collection);
            expect(apiCallStub.calledOnce).toBe(true);
            expect(apiCallStub.args[0][1].indexOf('module_list=all')).not.toBe(-1);
        });

        it('Should remove all modules that are not allowed.', function() {
            layout.collection.sync('read', layout.collection, {
                module_list: ['Home','Contacts','TargetList','Calls','Accounts']
            });
            expect(apiCallStub.calledOnce).toBe(true);
            expect(apiCallStub.args[0][1].indexOf('module_list=Accounts%2CContacts')).not.toBe(-1);
        });
    });
});
