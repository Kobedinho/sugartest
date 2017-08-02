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
describe('Base.Field.CollectionCount', function() {
    var app, field, template,
        module = 'Bugs',
        fieldName = 'foo';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate('collection-count', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();
        var fieldDef = {};
        field = SugarTest.createField('base', fieldName, 'collection-count', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.collection.restore();
    });

    describe('updateCount', function() {

        beforeEach(function() {
            sinon.collection.stub(app.lang, 'get', function(key) {
                return key;
            });
            field.collection = app.data.createBeanCollection(module);
        });

        using('different collection properties', [
            {
                length: 0,
                next_offset: -1,
                expected: '',
                dataFetched: false
            },
            {
                length: 20,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT',
                dataFetched: false
            },
            {
                length: 0,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT',
                dataFetched: true
            },
            {
                length: 5,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT',
                dataFetched: true
            },
            {
                length: 20,
                next_offset: 20,
                expected: 'TPL_LIST_HEADER_COUNT_TOTAL',
                dataFetched: true
            },
            // If options are passed to updateCount, they will take precedence
            // over the collection's properties.
            {
                length: 20,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT_TOTAL',
                options: {
                    length: 50,
                    hasMore: true
                },
                dataFetched: true
            },
            {
                length: 20,
                next_offset: 20,
                expected: 'TPL_LIST_HEADER_COUNT',
                options: {
                    length: 50,
                    hasMore: false
                },
                dataFetched: true
            }
        ], function(provider) {
            it('should display a proper count representation', function() {
                provider = provider || {};
                field.collection.length = provider.length;
                field.collection.next_offset = provider.next_offset;
                field.collection.dataFetched = provider.dataFetched;

                field.updateCount(provider.options);
                expect(field.countLabel.toString()).toBe(provider.expected);
            });
        });

        it('should display the total cached count', function() {
            field.collection.length = 20;
            field.collection.total = 500;
            field.collection.dataFetched = true;

            field.updateCount();
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT_TOTAL');
        });
    });

    describe('paginate', function() {
        it('should fetch the total count when paginating', function() {
            sinon.collection.stub(app.BeanCollection.prototype, 'fetchTotal');
            sinon.collection.stub(app.alert);

            field.context.trigger('paginate');
            expect(app.BeanCollection.prototype.fetchTotal).toHaveBeenCalled();
        });
    });

    describe('reset', function() {
        it('should keep the counts in sync with the collection', function() {
            sinon.collection.spy(field, 'updateCount');

            field.collection.length = 20;
            field.collection.total = 500;
            field.collection.dataFetched = true;

            field.collection.trigger('reset');

            expect(field.updateCount.calledOnce).toBe(true);
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT_TOTAL');

            field.collection.length = 20;
            field.collection.total = null;
            field.collection.next_offset = -1;

            field.collection.trigger('reset');

            expect(field.updateCount.calledTwice).toBe(true);
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT');
        });
    });

    describe('refresh:count', function() {
        using('different collection properties and options', [
            {
                length: 20,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT_TOTAL',
                hasAmount: true,
                options: {
                    length: 50,
                    hasMore: true
                }
            },
            {
                length: 20,
                next_offset: 20,
                expected: 'TPL_LIST_HEADER_COUNT',
                hasAmount: true,
                options: {
                    length: 50,
                    hasMore: false
                }
            }
        ], function(provider) {
            it('should update the count field with passed-in options, not collection properties', function() {
                sinon.collection.spy(field, 'updateCount');

                field.collection.length = provider.length;
                field.collection.next_offset = provider.next_offset;
                field.collection.dataFetched = true;

                field.context.trigger('refresh:count', provider.hasAmount, provider.options);

                expect(field.updateCount.called).toBe(true);
                expect(field.countLabel.toString()).toBe(provider.expected);
            });
        });
    });
});
