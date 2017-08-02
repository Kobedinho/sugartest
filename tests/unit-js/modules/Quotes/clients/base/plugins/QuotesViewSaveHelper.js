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
describe('Quotes.Base.Plugins.QuotesViewSaveHelper', function() {
    var app;
    var component;
    var view;
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

        context = app.context.getContext();
        model = app.data.createBean('Quotes');
        context.set('model', model);
        view = SugarTest.createView('base', 'Quotes', 'record', null, context, true, null, true);
        view.calculatedFields = [
            'subtotal',
            'subtotal_usdollar',
            'shipping',
            'shipping_usdollar',
            'deal_tot',
            'deal_tot_usdollar',
            'new_sub',
            'new_sub_usdollar',
            'tax',
            'tax_usdollar',
            'total',
            'total_usdollar'
        ];
        view.noEditFields = [];
    });

    afterEach(function() {
        sinon.collection.restore();
        if (component) {
            component.dispose();
            component = null;
        }
        app.cache.cutAll();
        app = null;
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

    describe('hasUnsavedQuoteChanges()', function() {
        var tmpRow;
        var callReturn;
        var bundles;
        var items;
        beforeEach(function() {
            tmpRow = {
                id: 1234,
                name: 'test',
                total: '100',
                bundles: [{
                    id: 1233,
                    name: 'bundle_1',
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

        afterEach(function() {
            tmpRow = null;
            callReturn = null;
            bundles = null;
            items = null;
        });

        it('should reset the noEditFields variable', function() {
            var existingValues = view.noEditFields;
            view.hasUnsavedChanges();
            expect(view.noEditFields).toBe(existingValues);
        });

        it('should call super with hasUnsavedChanges', function() {
            sinon.collection.stub(view, '_super', function() {});
            callReturn = view.hasUnsavedChanges();
            expect(view._super).toHaveBeenCalledWith('hasUnsavedChanges');
            expect(callReturn).toBeFalsy();
        });

        it('should find no changes', function() {
            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should ignore changes on the total field', function() {
            model.set('total', '125.00');
            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should find the change on the bundle', function() {
            bundles = model.get('bundles').at(0);
            bundles.set('name', 'bundle_123');

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeTruthy();
        });

        it('should not find the change on the bundle', function() {
            bundles = model.get('bundles').at(0);
            bundles.set('name', 'bundle_123');
            view.type = 'create';

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should find the change on the item in the bundle', function() {
            bundles = model.get('bundles').at(0);
            items = bundles.get('product_bundle_items').at(0);
            items.set('name', 'item_123');

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeTruthy();
        });

        it('should find a change when an item is added to a group and is still new', function() {
            bundles = model.get('bundles').at(0);
            bundles.get('product_bundle_items').add(app.data.createBean('Products'));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeTruthy();
        });

        it('should find a change when an item is added to a group and is not new', function() {
            bundles = model.get('bundles').at(0);
            bundles.get('product_bundle_items').add(app.data.createBean('Products', {id: 'my_new_id'}));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should not find nay change when an item is removed from a group', function() {
            bundles = model.get('bundles').at(0).get('product_bundle_items');
            bundles.remove(bundles.at(0));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should not find any change when a group is added', function() {
            bundles = model.get('bundles');
            bundles.add(app.data.createBean('ProductBundles'));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should not find any change when a group is removed', function() {
            bundles = model.get('bundles');
            bundles.remove(bundles.at(0));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });
    });
});
