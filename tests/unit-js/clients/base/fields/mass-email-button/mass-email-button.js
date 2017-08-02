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
describe('Base.Fields.MassEmailButton', function() {
    var app, module, field, context, massCollection, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        module = 'Contacts';

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('mass-email-button', 'field', 'base', 'list-header');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'mass-email-button');
        SugarTest.testMetadata.set();
        SugarTest.loadPlugin('EmailClientLaunch');

        context = app.context.getContext();
        massCollection = app.data.createBeanCollection(module);
        context.set({
            mass_collection: massCollection
        });
        context.prepare();

        field = SugarTest.createField({
            name: 'mass_email_button',
            type: 'mass-email-button',
            viewName: 'list-header',
            context: context
        });
    });

    afterEach(function() {
        sandbox.restore();
        SugarTest.testMetadata.dispose();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should add recipients to mailto for external mail client', function() {
        var email1 = 'foo1@bar.com',
            email2 = 'foo2@bar.com',
            bean1 = app.data.createBean(module, {email: [{email_address: email1}]}),
            bean2 = app.data.createBean(module, {email: [{email_address: email2}]});

        sandbox.stub(field, 'useSugarEmailClient', function() {
            return false;
        });
        massCollection.add(bean1);
        massCollection.add(bean2);
        expect(field.$('a').attr('href')).toEqual('mailto:' + email1 + ',' + email2);
    });

    it('should add recipients to mailto for internal mail client', function() {
        var email1 = 'foo1@bar.com',
            email2 = 'foo2@bar.com',
            bean1 = app.data.createBean(module, {email: [{email_address: email1}]}),
            bean2 = app.data.createBean(module, {email: [{email_address: email2}]}),
            drawerOpenOptions;

        app.drawer = {
            open: sandbox.stub()
        };
        useSugarEmailClientStub = sinon.stub(field, 'useSugarEmailClient', function() {
            return true;
        });
        massCollection.add(bean1);
        massCollection.add(bean2);
        field.$('a').click();
        drawerOpenOptions = app.drawer.open.lastCall.args[0];
        expect(drawerOpenOptions.context.prepopulate.to_addresses).toEqual([
            {bean: bean1, email: 'foo1@bar.com'},
            {bean: bean2, email: 'foo2@bar.com'}
        ]);
        app.drawer = null;
    });
});
