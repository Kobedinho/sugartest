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
describe('Base.Field.Dashletaction', function() {

    var app, field, view, moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadHandlebarsTemplate('dashletaction', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });
    using('Available params', [
        'stringparm',
        ['Array', 'Params'],
        {
            'objecttype': 'Param',
            'foo': 'boo'
        }
    ], function(expectedParams) {
        describe('Test different type of params: ' + expectedParams, function() {
            beforeEach(function() {
                field = SugarTest.createField('base', 'dashletaction', 'dashletaction', 'detail', {
                    'type': 'dashletaction',
                    'action': 'test',
                    'params': expectedParams,
                    'acl_action': 'view'
                });
                _.extend(field.view, {
                    test: function(evt, params) {}
                });
            });

            afterEach(function() {
                field.dispose();
                field = null;
            });

            it('should hide action if the user doesn\'t have access', function() {
                field.model = app.data.createBean(moduleName);
                sinon.collection.stub(app.acl, 'hasAccessToModel', function() {
                    return false;
                });
                field.render();
                expect(field.isHidden).toBeTruthy();
            });

            it('should be able to execute the parent view actions', function() {
                var actualParams,
                    viewStub = sinon.collection.stub(field.view, 'test', function(evt, params) {
                        actualParams = params;
                    });
                field.render();
                field.$('[data-dashletaction]').click();
                expect(viewStub).toHaveBeenCalled();
                expect(actualParams).toBe(expectedParams);
            });
        });
    });
});
