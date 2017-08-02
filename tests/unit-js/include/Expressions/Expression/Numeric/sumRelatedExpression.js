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
describe('SumRelatedExpression Function', function () {
    var app, dm, sinonSandbox, meta, model;

    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model =  isCollection ? new modelOrCollection.model() : modelOrCollection;
        var context = context || app.context.getContext({
            url: "someurl",
            module: model.module,
            model: model
        });
        var view = SugarTest.createComponent("View", {
            context: context,
            type: "edit",
            module: model.module
        });
        return new SUGAR.expressions.SidecarExpressionContext(view, model, isCollection ? modelOrCollection : false);
    }

    var initializeContexts = function() {
        getSLContext(model).initialize(meta.modules.Quotes.dependencies);
        getSLContext(model.get('bundles')).initialize(meta.modules.ProductBundles.dependencies)
        model.get('bundles').each(function(bundle){
            var prods = bundle.getRelatedCollection('products');
            getSLContext(prods).initialize(meta.modules.Products.dependencies || []);
        });
    }

    var seedServer = function(){
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith(
            "GET",
            /.*\/rest\/v10\/ExpressionEngine\/quote_id_1\/related.*/, //new RegExp(".*/rest/v10/ExpressionEngine/.*"),
            [
                200,
                {"Content-Type": "application/json"},
                JSON.stringify({
                    "product_bundles": {
                        "rollupSum": {
                            "new_sub": "4050.000000",
                            "new_sub_values": {"bundle_id_1": "4050.000000"}
                        },
                        "rollupCurrencySum": {
                            "new_sub": "4050.000000",
                            "new_sub_values": {"bundle_id_1": "4050.000000"}
                        }
                    }
                })
            ]
        );
        SugarTest.server.respondWith(
            "GET",
            /.*\/rest\/v10\/ExpressionEngine\/bundle_id_1\/related.*/, //new RegExp(".*/rest/v10/ExpressionEngine/.*"),
            [
                200,
                {"Content-Type": "application/json"},
                JSON.stringify({
                    "products": {
                        "rollupCurrencySum": {
                            "deal_calc": "0.000000",
                            "deal_calc_values": {
                                "prod_tk_1000": "0.000000",
                                "prod_tk_m30": "0.000000"
                            },
                            "subtotal": "4050.000000",
                            "subtotal_values": {
                                "prod_tk_1000": "800.000000",
                                "prod_tk_m30": "3250.000000"
                            },
                        },
                        "rollupSum": {
                            "deal_calc": "0.000000",
                            "deal_calc_values": {
                                "prod_tk_1000": "0.000000",
                                "prod_tk_m30": "0.000000"
                            },
                            "subtotal": "4050.000000",
                            "subtotal_values": {
                                "prod_tk_1000": "800.000000",
                                "prod_tk_m30": "3250.000000"
                            },
                        }
                    }
                })
            ]
        );
    }

    beforeEach(function () {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture("nested-collections-metadata");
        app.metadata.set(meta)
        dm = app.data;
        dm.reset();
        dm.declareModels();
        sinon.stub(app.currency, "convertAmount", function(val, from, to) {
            from = !from || from == "-99" ? 1 : parseFloat(from);
            to = !to || to == "-99" ? 1 : parseFloat(to);

            return (parseFloat(val) / from) * to;
        });
        model = dm.createBean("Quotes", SugarTest.loadFixture("quote"));
        seedServer();
        initializeContexts();
    });

    afterEach(function () {
        app.currency.convertAmount.restore();
        sinonSandbox.restore();
    });

    describe('Related Collection Handling', function () {
        it('should return the set of backing values for a rollup calculation', function () {
            SugarTest.server.respond();
            expect(model.get('bundles').length).toBe(1);
            model.get('bundles').add(dm.createBean("ProductBundles", {
                "id" : "pb_123",
                "name" : "test",
                "new_sub" : 12.5
            }));

            expect(model.get('bundles').length).toBe(2);
            expect(parseFloat(model.get('new_sub'))).toBe(4062.5);
        });

        it('should rollup values when a model in the child collection changes', function () {
            SugarTest.server.respond();
            var pg = model.get('bundles').at(0);
            var items = pg.get('product_bundle_items');
            var prods = pg.getRelatedCollection('products');

            expect(items.length).toBe(3);
            expect(prods.length).toBe(2);
            expect(parseFloat(pg.get('new_sub'))).toBe(4050);
            expect(parseFloat(pg.get('deal_tot'))).toBe(0);
            expect(parseFloat(model.get('new_sub'))).toBe(4050);

            prods.at(0).set('discount_amount', 10);

            expect(parseFloat(pg.get('new_sub'))).toBe(3970);
            expect(parseFloat(pg.get('deal_tot'))).toBe(80);
            expect(parseFloat(model.get('new_sub'))).toBe(3970);
             //Verify discount applied correctly
            expect(parseFloat(prods.at(0).get("deal_calc"))).toBe(prods.at(0).get("subtotal") * .1);

            //Now change the quanitity of the product
            prods.at(0).set('quantity', 3);
            //Verify quantity works
            expect(parseFloat(prods.at(0).get("subtotal"))).toBe(prods.at(0).get("discount_price") * 3);
            //Verify discount calcualted again with new quantity
            expect(parseFloat(prods.at(0).get("deal_calc"))).toBe(prods.at(0).get("subtotal") * .1);

            //Verify this rolled up to the group and quote
            expect(parseFloat(pg.get('new_sub'))).toBe(5410);
            expect(parseFloat(pg.get('deal_tot'))).toBe(240);
            expect(parseFloat(model.get('new_sub'))).toBe(5410);
        });

        it('should rollup values when a model is added to the child collection', function () {
            var pg = model.get('bundles').at(0);
            var items = pg.get('product_bundle_items');
            var prods = pg.getRelatedCollection('products');

            SugarTest.server.respond();

            prods.add({
                name: "foo",
                discount_price: 100,
                quantity: 10,
                discount_amount: 15
            });

            expect(items.length).toBe(4);
            expect(prods.length).toBe(3);

            expect(parseFloat(pg.get('new_sub'))).toBe(4900);
            expect(parseFloat(pg.get('deal_tot'))).toBe(150);
            expect(parseFloat(model.get('new_sub'))).toBe(4900);
        });


        it('rollups across different currencies should convert to base', function () {
            var pg = model.get('bundles').at(0);
            var prods = pg.getRelatedCollection('products');

            SugarTest.server.respond();

            expect(parseFloat(pg.get('new_sub'))).toBe(4050);

            //100 of a curreny foo where 0.8 foo's to the dollar, so $125`
            //For the sake of testing using id as rate (only testing interaction with app.currency, which is mocked)
            prods.add({
                name: "foo",
                discount_price: 100,
                quantity: 1,
                discount_amount: 10,
                currency_id: '0.8',
            });

            expect(parseFloat(pg.get('new_sub'))).toBe(4162.5);
            expect(parseFloat(pg.get('deal_tot'))).toBe(12.5);
            expect(parseFloat(model.get('new_sub'))).toBe(4162.5);
        });

    });
});
