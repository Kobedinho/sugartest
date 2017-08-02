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
describe('Archive Email View', function () {
    var view,
        setMainButtonsDisabledStub,
        userId = '1234567890',
        userName = 'Johnny Appleseed',
        userIdBefore,
        userNameBefore;

    beforeEach(function() {
        var metadata = {
            fields: {
                name: {
                    name: "name",
                    vname: "LBL_NAME",
                    type: "varchar",
                    len: 255,
                    comment: "Name of this bean"
                }
            },
            favoritesEnabled: true,
            views: [],
            layouts: [],
            _hash: "bc6fc50d9d0d3064f5d522d9e15968fa"
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'compose', 'Emails');
        SugarTest.loadComponent('base', 'view', 'archive-email', 'Emails');
        SugarTest.testMetadata.updateModuleMetadata('Emails', metadata);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        var context = SugarTest.app.context.getContext();
        context.set({
            module: 'Emails',
            create: true
        });
        context.prepare();

        SugarTest.app.drawer = { on: $.noop, off: $.noop, getHeight: $.noop, close: $.noop };
        userIdBefore = SugarTest.app.user.id;
        SugarTest.app.user.id = userId;
        userNameBefore = SugarTest.app.user.attributes.full_name;
        SugarTest.app.user.attributes.full_name = userName;

        view = SugarTest.createView('base', 'Emails', 'archive-email', null, context, true);
        setMainButtonsDisabledStub = sinon.stub(view, 'setMainButtonsDisabled');
    });

    afterEach(function() {
        setMainButtonsDisabledStub.restore();
        view.dispose();
        SugarTest.app.drawer = undefined;
        SugarTest.app.user.id = userIdBefore;
        SugarTest.app.user.attributes.full_name = userNameBefore;
        SugarTest.testMetadata.dispose();
        SugarTest.app.cache.cutAll();
        SugarTest.app.view.reset();
        Handlebars.templates = {};
    });

    it('should prepopulate the current user on assigned to field if not already set', function() {
        expect(view.model.get('assigned_user_id')).toEqual(userId);
        expect(view.model.get('assigned_user_name')).toEqual(userName);
    });

    describe('archive', function () {
        it('should call the archive email api if the validation passes', function () {
            var apiCallStub = sinon.stub(SugarTest.app.api, 'call'),
                validationStub = sinon.stub(view.model, 'doValidate', function(fields, callback) {
                    callback(true);
                });

            view.archive();

            expect(apiCallStub.calledOnce).toBe(true);

            apiCallStub.restore();
            validationStub.restore();
        });

        it('should not call the archive email api if the validation fails', function () {
            var apiCallStub = sinon.stub(SugarTest.app.api, 'call'),
                validationStub = sinon.stub(view.model, 'doValidate', function(fields, callback) {
                    callback(false);
                });

            view.archive();

            expect(apiCallStub.called).toBe(false);

            apiCallStub.restore();
            validationStub.restore();
        });

        it('should call the correct api', function () {
            var apiCallStub = sinon.stub(SugarTest.app.api, 'call'),
                validationStub = sinon.stub(view.model, 'doValidate', function(fields, callback) {
                    callback(true);
                });

            view.archive();

            expect(apiCallStub.getCall(0).args[1]).toMatch('Mail/archive');

            apiCallStub.restore();
            validationStub.restore();
        });

        it('should send date_sent, from_address, and status to the server', function () {
            var apiCallStub = sinon.stub(SugarTest.app.api, 'call'),
                validationStub = sinon.stub(view.model, 'doValidate', function(fields, callback) {
                    callback(true);
                });

            view.model.set({
                'date_sent': 'foo',
                'from_address': 'bar'
            });
            view.archive();

            expect(apiCallStub.getCall(0).args[2].get('date_sent')).toBe('foo');
            expect(apiCallStub.getCall(0).args[2].get('from_address')).toBe('bar');
            expect(apiCallStub.getCall(0).args[2].get('status')).toBe('archive');

            apiCallStub.restore();
            validationStub.restore();
        });

        it('should first disable the archive button and then enable it back when validation fails', function () {
            var apiCallStub = sinon.stub(SugarTest.app.api, 'call'),
                validationStub = sinon.stub(view.model, 'doValidate', function(fields, callback) {
                    callback(false);
                });

            view.archive();

            expect(setMainButtonsDisabledStub.calledTwice).toBe(true);
            expect(setMainButtonsDisabledStub.getCall(0).args[0]).toBe(true);
            expect(setMainButtonsDisabledStub.getCall(1).args[0]).toBe(false);

            apiCallStub.restore();
            validationStub.restore();
        });
    });
});
