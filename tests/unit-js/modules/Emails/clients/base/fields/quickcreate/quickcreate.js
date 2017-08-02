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
describe("Emails.Fields.Quickcreate", function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField({
            client: 'base',
            name: 'quickcreate',
            type: 'quickcreate',
            viewName: 'detail',
            module: 'Emails',
            loadFromModule: true
        });
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        field = null;
    });

    describe("_retrieveEmailOptionsFromLink", function() {
        it("should return email options to prepopulate on email compose if existing parent model exists on context", function() {
            var bean = app.data.createBean('Contacts'),
                result;

            bean.set({
                id: '123',
                name: 'Foo'
            });
            field.context.set('model', bean);
            result = field._retrieveEmailOptionsFromLink();

            expect(result).toEqual({
                to_addresses: [{bean: bean}],
                related: bean
            });
        });

        it("should return email options to prepopulate on email compose if existing parent model exists on parent context", function() {
            var bean = app.data.createBean('Contacts'),
                parentContext = app.context.getContext(),
                result;

            bean.set({
                id: '123',
                name: 'Foo'
            });
            parentContext.prepare();
            field.context.parent = parentContext;
            field.context.parent.set('model', bean);
            result = field._retrieveEmailOptionsFromLink();

            expect(result).toEqual({
                to_addresses: [{bean: bean}],
                related: bean
            });
        });

        it("should return empty object if parent model does not exist", function() {
            var result;
            field.context.unset('model');
            result = field._retrieveEmailOptionsFromLink();

            expect(result).toEqual({});
        });

        it("should return empty object if parent model has no id, meaning it is not an existing record", function() {
            var bean = app.data.createBean('Contacts'),
                result;

            bean.set({
                name: 'Foo'
            });
            field.context.set('model', bean);
            result = field._retrieveEmailOptionsFromLink();

            expect(result).toEqual({});
        });
    });

});
