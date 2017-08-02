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
describe('Quotes.Base.Views.Record', function() {
    var app;
    var view;
    var viewMeta;
    var model;
    var context;
    var quoteFields;
    var bundleFields;
    var productFields;

    beforeEach(function() {
        app = SugarTest.app;

        quoteFields = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');
        bundleFields = SugarTest.loadFixture('product-bundle-fields', '../tests/modules/ProductBundles/fixtures');
        productFields = SugarTest.loadFixture('product-fields', '../tests/modules/Products/fixtures');

        SugarTest.loadFile('../modules/Quotes/clients/base/plugins', 'QuotesViewSaveHelper', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        SugarTest.testMetadata.init();
        SugarTest.seedMetadata(true, './fixtures');
        SugarTest.testMetadata.updateModuleMetadata('ProductBundles', {
            fields: bundleFields
        });
        SugarTest.testMetadata.updateModuleMetadata('Products', {
            fields: productFields
        });
        SugarTest.testMetadata.updateModuleMetadata('Quotes', {
            fields: quoteFields
        });

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        viewMeta = {
            panels: [
                {
                    fields: [
                        {
                            name: 'name'
                        }, {
                            name: 'total',
                            calculated: true
                        }, {
                            name: 'bundles',
                            fields: [
                                'id',
                                'name',
                                {
                                    name: 'product_bundle_items',
                                    fields: [
                                        'name',
                                        'id'
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        };

        sinon.collection.stub(app.data, 'getRelatedModule');
        app.data.getRelatedModule.withArgs('Quotes', 'product_bundles').returns('ProductBundles');
        app.data.getRelatedModule.withArgs('ProductBundles', 'products').returns('Products');
        app.data.getRelatedModule.withArgs('ProductBundles', 'product_bundle_notes').returns('ProductBundleNotes');

        context = app.context.getContext();
        model = app.data.createBean('Quotes');
        context.set('model', model);
        view = SugarTest.createView('base', 'Quotes', 'record', viewMeta, context, true);
        view.hasUnsavedQuoteChanges = $.noop;
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
        context = null;
        model.dispose();
        model = null;
    });

    describe('initialize', function() {
        it('should find all the calculated fields', function() {
            expect(view.calculatedFields).toContain('total');
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view.context, 'on', function() {});
        });

        it('should set listener on context for editable:handleEdit', function() {
            view.bindDataChange();

            expect(view.context.on).toHaveBeenCalledWith('editable:handleEdit');
        });

        it('should set listener on context for quotes:item:toggle', function() {
            view.bindDataChange();

            expect(view.context.on).toHaveBeenCalledWith('quotes:item:toggle');
        });

        it('should set listener on context for quotes:item:toggle:reset', function() {
            view.bindDataChange();

            expect(view.context.on).toHaveBeenCalledWith('quotes:item:toggle:reset');
        });

        it('should set listener on context for quotes:editableFields:add', function() {
            view.bindDataChange();

            expect(view.context.on).toHaveBeenCalledWith('editable:handleEdit');
        });

        it('should add a field to editableFields when the event is called', function() {
            view.context.on.restore();
            view.editableFields = [];
            view.bindDataChange();
            view.context.trigger('quotes:editableFields:add', {
                id: 'myEditableField1'
            });

            expect(view.editableFields[0].id).toBe('myEditableField1');
        });
    });

    describe('_handleItemToggled', function() {
        it('should have an edit count equal to 0', function() {
            view.editCount = 0;
            view.editIds[1] = true;
            view._handleItemToggled(false, 1);
            expect(view.editCount).toBe(0);
        });

        it('should have an edit count equal to 0', function() {
            view.editCount = 1;
            view.editIds[1] = true;
            view._handleItemToggled(false, 1);
            expect(view.editCount).toBe(0);
        });

        it('should have an edit count equal to 1', function() {
            view.editCount = 0;
            view._handleItemToggled(true, 1);
            expect(view.editCount).toBe(1);
        });

        it('should have an edit count equal to 1', function() {
            view.editCount = 1;
            view.editIds[1] = true;
            view._handleItemToggled(true, 1);
            expect(view.editCount).toBe(1);
        });
    });

    describe('saveClicked', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(app.alert, 'show', function() {});
        });

        it('should call _super', function() {
            view.editCount = 0;
            view.saveClicked();
            expect(view._super).toHaveBeenCalled();
        });

        it('should call app.alert.show', function() {
            view.editCount = 1;
            view.saveClicked();
            expect(app.alert.show).toHaveBeenCalled();
        });
    });

    describe('cancelClicked', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view.context, 'trigger', function() {});
        });

        it('should call _super', function() {
            view.cancelClicked();
            expect(view._super).toHaveBeenCalled();
        });

        it('should call context.trigger with list:editrow:fire', function() {
            view.cancelClicked();
            expect(view.context.trigger).toHaveBeenCalledWith('list:editrow:fire');
        });
    });

    describe('_handleEditShippingField()', function() {
        var shipField;
        var event;
        var $targetEl;
        var $targetElParent;

        beforeEach(function() {
            $targetEl = $('<a class="btn"></a>');
            $targetElParent = $('<div class="record-cell" data-name="shipFieldName1"></div>');
            $targetElParent.append($targetEl);
            event = {
                target: $targetEl
            };
            shipField = {
                id: 'shipFieldId1',
                name: 'shipFieldName1'
            };
            view.editableFields = [shipField];
            sinon.collection.stub(view, 'setButtonStates', function() {});
            sinon.collection.stub(view, 'toggleField', function() {});
            view.inlineEditMode = false;

            view._handleEditShippingField(event);
        });

        afterEach(function() {

        });

        it('should set inlineEditMode true', function() {
            expect(view.inlineEditMode).toBeTruthy();
        });

        it('should call setButtonStates', function() {
            expect(view.setButtonStates).toHaveBeenCalledWith('edit');
        });

        it('should call toggleField', function() {
            expect(view.toggleField).toHaveBeenCalledWith(shipField);
        });

        it('should call setButtonStates', function() {
            expect(view.setButtonStates).toHaveBeenCalledWith('edit');
        });
    });
    describe('hasUnsavedChanges()', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'hasUnsavedQuoteChanges', function() {});
        });

        it('should call hasUnsavedQuoteChanges', function() {
            view.hasUnsavedChanges();

            expect(view.hasUnsavedQuoteChanges).toHaveBeenCalled();
        });
    });

    describe('getCustomSaveOptions', function() {
        var attributes;
        var actual;
        beforeEach(function() {
            attributes = {
                currency_id: 'test_1',
                base_rate: '0.90'
            };
            model.setSyncedAttributes(attributes);
            model.set(attributes);
        });

        it('should return an empty object when currency has not changed', function() {
            actual = view.getCustomSaveOptions();
            expect(actual).toEqual({});
        });

        it('should return an object with success when the currency has changed', function() {
            model.set('currency_id', 'test_2');
            actual = view.getCustomSaveOptions({});
            expect(actual).not.toEqual({});
            expect(actual.success).toBeDefined();
        });
    });

    describe('_createBulkBundlesPayload', function() {
        var tmpRow;
        var expected;
        var actual;
        beforeEach(function() {
            sinon.collection.stub(app.api, 'buildURL', function(module, action, attributes) {
                return 'rest/v10/' + module + '/' + attributes.id;
            });
            expected = [{
                url: '/v10/ProductBundles/1233',
                method: 'PUT',
                data: {
                    currency_id: 'test_1',
                    base_rate: '0.90'
                }
            }];
            tmpRow = {
                id: 1234,
                name: 'test',
                total: '100',
                currency_id: 'test_1',
                base_rate: '0.90',
                bundles: [{
                    id: 1233,
                    name: 'bundle_1',
                    currency_id: 'test_1',
                    base_rate: '0.90',
                    _module: 'ProductBundles',
                    product_bundle_items: [{
                        id: 12345,
                        name: 'item_1',
                        _module: 'Products'
                    }]
                }]
            };
            model.setSyncedAttributes(tmpRow);
            model.set(tmpRow);
        });

        it('should return the one bundle', function() {
            actual = view._createBulkBundlesPayload();

            expect(actual).toEqual(expected);
        });

        it('should not return new bundles', function() {
            model.get('bundles').add({
                name: 'bundle_new',
                currency_id: 'test_1',
                base_rate: '0.90',
                _module: 'ProductBundles'
            });
            actual = view._createBulkBundlesPayload();

            expect(actual).toEqual(expected);
        });
    });

    describe('_sendBulkBundlesUpdate', function() {
        var payload;
        var bindReturn;
        beforeEach(function() {
            sinon.collection.stub(app.api, 'buildURL', function() {
                return 'rest/v10/bulk';
            });
            sinon.collection.stub(app.api, 'call', function() {});
        });

        it('should not call the api when bulkSaveRequests is empty', function() {
            view._sendBulkBundlesUpdate([]);
            expect(app.api.buildURL).not.toHaveBeenCalled();
            expect(app.api.call).not.toHaveBeenCalled();
        });

        it('should send the api call', function() {
            payload = [{
                url: '/v10/ProductBundles/1233',
                method: 'PUT',
                data: {
                    currency_id: 'test_1',
                    base_rate: '0.90'
                }
            }];
            bindReturn = $.noop;

            sinon.collection.stub(_, 'bind', function() {
                return bindReturn;
            });

            view._sendBulkBundlesUpdate(payload);

            expect(app.api.buildURL).toHaveBeenCalled();
            expect(_.bind).toHaveBeenCalledWith(view._onBulkBundlesUpdateSuccess, view);
            expect(app.api.call).toHaveBeenCalledWith(
                'create',
                'rest/v10/bulk',
                {
                    requests: payload
                },
                {
                    success: bindReturn
                }
            );
        });
    });

    describe('_onBulkBundlesUpdateSuccess', function() {
        var tmpRow;
        var response;
        var bundle;
        beforeEach(function() {
            tmpRow = {
                id: 1234,
                name: 'test',
                total: '100',
                currency_id: 'test_1',
                base_rate: '0.90',
                bundles: [{
                    id: 1233,
                    name: 'bundle_1',
                    currency_id: 'test_1',
                    base_rate: '0.90',
                    _module: 'ProductBundles',
                    product_bundle_items: [{
                        id: 12345,
                        name: 'item_1',
                        _module: 'Products'
                    }]
                }]
            };
            model.setSyncedAttributes(tmpRow);
            model.set(tmpRow);
            bundle = model.get('bundles').at(0);

            sinon.collection.spy(bundle, 'set');
            sinon.collection.spy(bundle, 'setSyncedAttributes');

            response = [{
                contents: {
                    id: '1233'
                }
            }];
        });

        it('should call set and setSyncedAttributes on the bundle', function() {
            view._onBulkBundlesUpdateSuccess(response);

            expect(bundle.set).toHaveBeenCalledWith(response[0].contents);
            expect(bundle.setSyncedAttributes).toHaveBeenCalledWith(response[0].contents);
        });
    });
});
