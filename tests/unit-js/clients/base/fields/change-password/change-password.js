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
describe('Change Password field', function() {
    var app, field,
        fieldName = 'test_password',
        moduleName = 'Contacts',
        metadata;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'change-password');
        metadata = {
            fields: {
                test_password: {
                    name: 'test_password',
                    type: 'change-password'
                }
            },
            views: [],
            layouts: [],
            _hash: 'bc6fc50d9d0d3064f5d522d9e15968fa'
        };
        app.data.declareModel(moduleName, metadata);
        field = SugarTest.createField('base', fieldName, 'change-password', 'edit', {}, moduleName, app.data.createBean(moduleName));
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
    });

    describe('Field', function() {

        it('should have added confirm_password to app.error.errorName2Keys', function() {
            expect(app.error.errorName2Keys['confirm_password']).toEqual('ERR_REENTER_PASSWORDS');
        });

        it('should set _hasChangePasswordModifs when extending model to make sure we override only once', function() {
            expect(field.model).toBeDefined();
            expect(field.model._hasChangePasswordModifs).toBeTruthy();
        });

        it('should always reset to false after render', function() {
            field.render();
            expect(field.showPasswordFields).toBeFalsy();

            field.showPasswordFields = true;
            field.render();
            expect(field.showPasswordFields).toBeFalsy();
        });

        it('should format the value', function() {
            expect(field.format(true)).toEqual('value_setvalue_set');
            expect(field.format('')).toEqual('');
        });

        it('should unformat the value', function() {
            expect(field.unformat('value_setvalue_set')).toEqual(true);
            expect(field.unformat('test')).toEqual('test');
            expect(field.unformat('')).toEqual('');
        });
    });

    describe('Model', function() {

        it('shoud return an error when passwords don\'t match', function() {
            var data = {};
            data[fieldName] = '123';
            data[fieldName + '_new_password'] = 'abc';
            data[fieldName + '_confirm_password'] = 'abcd';
            field.model.set(data);
            var callback = sinon.stub();
            field.model._doValidatePasswordConfirmation(metadata.fields, {}, callback);

            expect(callback).toHaveBeenCalled();
            expect(callback.args[0]).toBeDefined();
            expect(callback.args[0][2][fieldName]).toBeDefined();
            expect(callback.args[0][2][fieldName].confirm_password).toBeTruthy();
        });

        it('shoud not return an error if passwords match', function() {
            var data = {};
            data[fieldName] = '123';
            data[fieldName + '_new_password'] = 'abc';
            data[fieldName + '_confirm_password'] = 'abc';
            field.model.set(data);
            var callback = sinon.stub();
            field.model._doValidatePasswordConfirmation(metadata.fields, {}, callback);

            expect(callback).toHaveBeenCalled();
            expect(callback.args[0]).toBeDefined();
            expect(callback.args[0][2][fieldName]).toBeUndefined();
        });

        it('shoud delete temporary attributes on revertAttributes', function() {
            var data = {};
            data[fieldName] = '123';
            data[fieldName + '_new_password'] = 'abc';
            data[fieldName + '_confirm_password'] = 'abc';
            field.model.set(data);
            field.model.revertAttributes();
            expect(field.model.get(fieldName + '_new_password')).toBeUndefined();
            expect(field.model.get(fieldName + '_confirm_password')).toBeUndefined();
        });
    });
});
