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
describe('Emails.Fields.EmailactionPaneltop', function() {
    var app, field, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField({
            client: 'base',
            name: 'paneltop',
            type: 'emailaction-paneltop',
            viewName: 'detail',
            module: 'Emails',
            loadFromModule: true
        });
        field.plugins = ['EmailClientLaunch'];
        SugarTest.loadPlugin('EmailClientLaunch');
        SugarTest.app.plugins.attach(field, 'field');

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        field = null;
    });

    describe('launching the email client', function() {
        describe('getting options from the field', function() {
            var bean, $el;

            beforeEach(function() {
                bean = app.data.createBean('Contacts', {id: '123', name: 'Foo', email: 'foo@bar.com'});
                field.context.set('model', bean);

                $el = $('<a href="#" data-action="email" data-placement="bottom">' + bean.get('email') + '</a>');
            });

            it('should return the email options', function() {
                var actual;

                field.emailOptions = {
                    to_addresses: [bean.toJSON()],
                    related: bean
                };

                actual = field._retrieveEmailOptions($el);
                expect(actual.to_addresses).toBeDefined();
                expect(actual.related).toBeDefined();
            });

            it('should return an empty object', function() {
                expect(field._retrieveEmailOptionsFromLink($el)).toEqual({});
            });
        });
    });

    describe('closing the email client', function() {
        it('should trigger paneltop:refresh events on the context', function() {
            field.context.parent = undefined;
            sandbox.spy(field.context, 'trigger');

            field.trigger('emailclient:close');

            expect(field.context.trigger.callCount).toBe(2);
            expect(field.context.trigger.getCall(0).args[0]).toEqual('panel-top:refresh');
            expect(field.context.trigger.getCall(0).args[1]).toEqual('emails');
            expect(field.context.trigger.getCall(1).args[0]).toEqual('panel-top:refresh');
            expect(field.context.trigger.getCall(1).args[1]).toEqual('archived_emails');
        });

        it('should trigger the events on the parent context', function() {
            field.context.parent = {
                trigger: sandbox.spy()
            };

            field.trigger('emailclient:close');

            expect(field.context.parent.trigger.callCount).toBe(2);
        });
    });
});
