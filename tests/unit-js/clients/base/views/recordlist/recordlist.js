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
describe('Base.View.RecordList', function() {
    var view, layout, app, meta, moduleName = 'Cases';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('recordlist', 'view', 'base', 'row');
        SugarTest.testMetadata.addViewDefinition('list', {
            'favorite': true,
            'selection': {
                'type': 'multi',
                'actions': []
            },
            'rowactions': {
                'actions': []
            },
            'panels': [
                {
                    'name': 'panel_header',
                    'header': true,
                    'fields': [
                        'name',
                        'case_number',
                        'type',
                        'description',
                        'date_entered',
                        'date_modified',
                        'modified_user_id'
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        view = SugarTest.createView('base', moduleName, 'recordlist', null, null);
        layout = SugarTest.createLayout('base', moduleName, 'list', null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    it('should have `my_favorite` field in the context', function() {
        expect(_.contains(view.context.get('fields'), 'my_favorite')).toBeTruthy();
    });

    describe('adding actions to list view', function() {
        it('should have added favorite field', function() {
            view.render();
            expect(view.leftColumns[0].fields[1]).toEqual({type: 'favorite'});
        });

        it('should not add favorite field to the view if the feature is not enabled', function() {
            view.dispose();

            SugarTest.testMetadata.updateModuleMetadata('Cases', {
                favoritesEnabled: false
            });
            var nofavoriteview = SugarTest.createView('base', 'Cases', 'recordlist', null, null);
            nofavoriteview.render();
            var actualFavoriteField = _.where(nofavoriteview.leftColumns[0].fields, {type: 'favorite'});
            expect(actualFavoriteField.length).toBe(0);
            nofavoriteview.dispose();
        });

        it('should set a data view on the context', function() {
            expect(view.context.get('dataView')).toBe('list');
        });

        it('should have added row actions', function() {
            view.render();
            expect(view.leftColumns[0].fields[2]).toEqual({
                type: 'editablelistbutton',
                label: 'LBL_CANCEL_BUTTON_LABEL',
                name: 'inline-cancel',
                css_class: 'btn-link btn-invisible inline-cancel'
            });
            expect(view.rightColumns[0].fields[1]).toEqual({
                type: 'editablelistbutton',
                label: 'LBL_SAVE_BUTTON_LABEL',
                name: 'inline-save',
                css_class: 'btn-primary'
            });
            expect(view.rightColumns[0].css_class).toEqual('overflow-visible');
        });
    });

    describe('hasUnsavedChanges', function() {

        beforeEach(function() {
            view.collection = new app.data.createBeanCollection('Cases', [
                {
                    id: 1,
                    name: 'First',
                    case_number: 123,
                    description: 'first description'
                },
                {
                    id: 2,
                    name: 'Second',
                    case_number: 123,
                    description: 'second description'
                },
                {
                    id: 3,
                    name: 'Third',
                    case_number: 123,
                    description: 'third description'
                }
            ]);
            view.collection.dataFetched = true;
            view.render();
        });

        it('should warn unsaved changes among the synced attributes', function() {
            var selectedModelId = '1';
            view.toggleRow(selectedModelId, true);
            var model = view.collection.get(selectedModelId);
            model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);
        });

        it('should ignore warning unsaved changes once the edit fields are reverted', function() {
            var selectedModelId = '2';
            view.toggleRow(selectedModelId, true);
            var model = view.collection.get(selectedModelId);
            model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);

            view.toggleRow(selectedModelId, false);
            actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
        });

        it('should inspect unsaved changes on multiple rows', function() {
            var selectedModelId = '3';
            view.toggleRow(selectedModelId, true);
            expect(_.size(view.toggledModels)).toBe(1);

            //set two rows editable
            view.toggleRow('1', true);
            expect(_.size(view.toggledModels)).toBe(2);

            var model = view.collection.get(selectedModelId);
            model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);

            view.toggleRow(selectedModelId, false);
            actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
            expect(_.size(view.toggledModels)).toBe(1);
        });

        it('should warn unsaved changes ONLY IF the changes are editable fields', function() {
            var selectedModelId = '2';
            view.toggleRow(selectedModelId, true);
            var model = view.collection.get(selectedModelId);

            model.setSyncedAttributes({
                name: 'Original',
                case_number: 456,
                description: 'Previous description',
                non_editable: 'system value'
            });

            //un-editable field
            model.set({
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);

            //Changed non-editable field
            model.set({
                non_editable: 'user value'
            });
            actual = view.hasUnsavedChanges();
            var editableFields = _.pluck(view.rowFields[selectedModelId], 'name');
            expect(_.contains(editableFields, 'non_editable')).toBe(false);
            expect(actual).toBe(false);

            //Changed editable field
            model.set({
                description: 'Changed description'
            });
            actual = view.hasUnsavedChanges();
            expect(_.contains(editableFields, 'description')).toBe(true);
            expect(actual).toBe(true);
        });


        describe('Warning delete', function() {
            var alertShowStub, routerStub;

            beforeEach(function() {
                routerStub = sinon.collection.stub(app.router, 'navigate');
                sinon.collection.stub(Backbone.history, 'getFragment');
                alertShowStub = sinon.collection.stub(app.alert, 'show');
            });

            it('should not alert warning message if _modelToDelete is not defined', function() {
                app.routing.triggerBefore('route', {});
                expect(alertShowStub).not.toHaveBeenCalled();
            });

            it('should return true if _modelToDelete is not defined', function() {
                sinon.collection.stub(view, 'warnDelete');
                expect(view.beforeRouteDelete()).toBeTruthy();
            });

            it('should return false if _modelToDelete is defined (to prevent routing to other views)', function() {
                sinon.collection.stub(view, 'warnDelete');
                view._modelToDelete = new Backbone.Model();
                expect(view.beforeRouteDelete()).toBeFalsy();
            });

            it('should redirect the user to the targetUrl', function() {
                var unbindSpy = sinon.collection.spy(view, 'unbindBeforeRouteDelete');
                view._modelToDelete = app.data.createBean(moduleName);
                view._currentUrl = 'Accounts';
                view._targetUrl = 'Contacts';
                view.deleteModel();
                expect(unbindSpy).toHaveBeenCalled();
                expect(routerStub).toHaveBeenCalled();
            });
        });
    });

    describe('_filterMeta', function() {

        beforeEach(function() {
            meta = {
                selection: {
                    actions: [
                        {
                            'name': 'calc_field_button',
                            'type': 'button',
                            'label': 'LBL_UPDATE_CALC_FIELDS',
                            'events': {
                                'click': 'list:updatecalcfields:fire'
                            },
                            'acl_action': 'massupdate'
                        }
                    ]
                }
            };
        });

        using('different values for user developer access and module contains calc fields or not', [
            {
                hasAccess: true,
                fields: [
                    {name: 'foo', calculated: true, formula: '$name'}
                ],
                leave: true
            },
            {
                hasAccess: false,
                fields: [
                    {name: 'foo', calculated: true, formula: '$name'}
                ],
                leave: false
            },
            {
                hasAccess: true,
                fields: [
                    {name: 'foo'}
                ],
                leave: false
            }
        ], function(params) {
            it('should handle the calc_field_button properly', function() {
                sinon.collection.stub(app.acl, 'hasAccess').returns(params.hasAccess);
                var options = {
                    context: {
                        get: function() {
                            return { fields: params.fields };
                        }
                    }
                };
                meta = view._filterMeta(meta, options);
                if (params.leave) {
                    expect(meta.selection.actions).not.toEqual([]);
                } else {
                    expect(meta.selection.actions).toEqual([]);
                }
            });
        });
    });

    describe('_setRowFields', function() {
        var models;

        beforeEach(function() {
            models = [
                new Backbone.Model({ id: _.uniqueId('_setRowFields-model-id-') }),
                new Backbone.Model({ id: _.uniqueId('_setRowFields-model-id-') }),
                new Backbone.Model({ id: _.uniqueId('_setRowFields-model-id-') })
            ];
            _.each(models, function(model) {
                view.fields[_.uniqueId('_setRowFields-field-id-')] = { model: model };
                view.fields[_.uniqueId('_setRowFields-field-id-')] = { model: model };
                view.fields[_.uniqueId('_setRowFields-field-id-')] = { model: model };
                view.fields[_.uniqueId('_setRowFields-field-id-')] = { model: model };
            });
        });

        afterEach(function() {
            view.fields = {};
        });

        it('should store the collection of fields for each row/model', function() {
            expect(_.size(view.rowFields)).toEqual(0);

            view.trigger('render');
            expect(view.rowFields).toBeDefined();

            _.each(models, function(model) {
                expect(view.rowFields[model.id]).toBeDefined();
                expect(view.rowFields[model.id].length).toEqual(4);
            });
        });
    });

    describe('Auto scrolling on fields focus in inline edit mode', function() {
        beforeEach(function() {
            var flexListViewHtml = '<div class="flex-list-view-content"></div>';
            view.$el.append(flexListViewHtml);
            var bordersPosition = {left: 71, right: 600};
            var _getBordersPositionStub = sinon.collection.stub(view, '_getBordersPosition', function() {
                return bordersPosition;
            });

        });
        using('fields hidden to the right, to the left or visible, in rtl or ltr mode' +
            'and different browser rtl scrollTypes',
            [
                {rtl: false, left: 34, right: 138, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: -41},
                {rtl: false, left: 570, right: 650, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: 54},
                {rtl: false, left: 300, right: 380, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: 0},
                {rtl: true, scrollType: 'default', left: 34, right: 138, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: -41},
                {rtl: true, scrollType: 'default', left: 570, right: 650, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: 54},
                {rtl: true, scrollType: 'default', left: 300, right: 380, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: 0},
                {rtl: true, scrollType: 'reverse', left: 34, right: 138, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: 41},
                {rtl: true, scrollType: 'reverse', left: 570, right: 650, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: -54},
                {rtl: true, scrollType: 'reverse', left: 300, right: 380, top: 380, bottom: 408, fieldPadding: 4, expectedScroll: 0}
            ],
            function(params) {
                it('should scroll the panel to the make the focused field visible', function() {
                    if (params.rtl) {
                        app.lang.direction = 'rtl';
                        $.support.rtlScrollType = params.scrollType;
                    }
                    var scrollLeftSpy = sinon.collection.spy($.fn, 'scrollLeft');
                    view.setPanelPosition(params);

                    if (!params.expectedScroll) {
                        expect(scrollLeftSpy).not.toHaveBeenCalled();
                    } else {
                        expect(scrollLeftSpy).toHaveBeenCalledWith(params.expectedScroll);
                    }
                });
            });
    });
});
