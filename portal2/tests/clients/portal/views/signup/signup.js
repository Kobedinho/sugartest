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
describe('Portal Signup View', function() {

    var view;
    var app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('signup', 'view', 'portal');
        SugarTest.testMetadata.addViewDefinition('signup', {
            panels: [
                {
                    fields: [
                        {
                            name: 'first_name'
                        },
                        {
                            name: 'last_name'
                        },
                        {
                            name: 'country',
                            type: 'enum',
                            options: 'countries_dom'
                        },
                        {
                            name: 'state',
                            type: 'enum',
                            options: 'states_dom'
                        }
                    ]
                }
            ]
        });
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        view = SugarTest.createView('portal','Signup', 'signup');
        app = SUGAR.App;
        sinon.collection.stub(app.metadata, 'getLogoUrl', function() {
            return '#';
        });
        view.render();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        sinon.collection.restore();
    });

    describe('Declare Sign Up Bean', function() {

        it('should have declared a Bean with the fields metadata', function() {
            expect(view.model.fields).toBeDefined();
            expect(_.size(view.model.fields)).toBeGreaterThan(0);
            expect(_.size(view.model.fields.first_name)).toBeDefined();
            expect(_.size(view.model.fields.last_name)).toBeDefined();
        });
    });

    describe('signup', function() {
        var stateField;
        beforeEach(function() {
            stateField = view.getField('state');
        });

        it('should show state field', function() {
            sinon.collection.spy(stateField, 'show');

            view.model.set('country', 'USA');
            view.toggleStateField();
            expect(stateField.show).toHaveBeenCalled();
        });

        it('should hide state field', function() {
            sinon.collection.spy(stateField, 'hide');

            view.model.set('country', 'MEXICO');
            view.toggleStateField();
            expect(stateField.hide).toHaveBeenCalled();
        });
    });
});
