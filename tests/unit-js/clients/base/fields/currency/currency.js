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

describe('Base.Fields.Currency', function() {

    var app;
    var model;

    var moduleName;
    var metadata;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        moduleName = 'Opportunities';
        metadata = {
            fields: {
                'amount': {
                    'name': 'amount',
                    'vname': 'LBL_AMOUNT',
                    'type': 'currency',
                    'dbType': 'currency',
                    'comment': 'Unconverted amount of the opportunity',
                    'importable': 'required',
                    'duplicate_merge': '1',
                    'required': true,
                    'options': 'numeric_range_search_dom',
                    'enable_range_search': true,
                    'validation': {
                        'type': 'range',
                        'min': 0
                    }
                },
                'currency_id': {
                    'name': 'currency_id',
                    'type': 'id',
                    'group': 'currency_id',
                    'vname': 'LBL_CURRENCY',
                    'function': 'getCurrencies',
                    'function_bean' : 'Currencies',
                    'reportable': false,
                    'comment': 'Currency used for display purposes'
                },
                'base_rate': {
                    'name': 'base_rate',
                    'vname': 'LBL_CURRENCY_RATE',
                    'type': 'double',
                    'required': true
                }
            },
            views: [],
            layouts: [],
            _hash: 'd7e699e7cf748d05ac311b0165e7591a'
        };

        app = SugarTest.app;

        app.data.declareModel(moduleName, metadata);
        app.user.setPreference('currency_id', '-99');
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        model = app.data.createBean(moduleName, {
            amount: 123456789.12,
            currency_id: '-99',
            base_rate: 1
        });
        model.isCopy = function() {
            return (model.isCopied === true);
        };
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;

        moduleName = null;
        metadata = null;

        SugarTest.testMetadata.dispose();
    });

    describe('_lastCurrencyId', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'edit',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            field._loadTemplate();
        });

        afterEach(function() {
            field = null;
        });

        it('is set after initialize', function() {
            expect(field._lastCurrencyId).toEqual('-99');
        });
    });

    describe('hideCurrencyDropdown', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'edit',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            field._loadTemplate();
        });

        afterEach(function() {
            field = null;
        });

        it('is not changed when setMode is called', function() {
            var initialValue = field.hideCurrencyDropdown;
            field.setMode('list');
            expect(field.hideCurrencyDropdown).toEqual(initialValue);

        });
    });

    describe('setMode', function() {
        var field, sandbox = sinon.sandbox.create();
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'edit',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            field._loadTemplate();
            sandbox.stub(field, '_super');
            sandbox.stub(field, 'getCurrencyField', function() {
                return {
                    setMode: function() {
                    },
                    setElement: function() {
                    }
                };
            });
        });

        afterEach(function() {
            field = null;
            sandbox.restore();
        });

        it('calls getCurrencyField when field.action is edit', function() {
            field.action = 'edit';
            field.setMode('edit');
            expect(field.getCurrencyField).toHaveBeenCalled();
        });

        it('doesnt calls getCurrencyField when field.action not edit', function() {
            field.action = 'disabled';
            field.setMode('edit');
            expect(field.getCurrencyField).not.toHaveBeenCalled();
        });
    });

    describe('EditView', function() {
        var field;

        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'edit',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            field._loadTemplate();
        });

        afterEach(function() {
            field = null;
        });

        it('should make use of app.utils to format the value', function() {

            var formatNumberLocale = sinon.spy(app.utils, 'formatNumberLocale');

            field.format(123456789.98);
            expect(formatNumberLocale.calledOnce).toBeTruthy();

            formatNumberLocale.restore();
        });

        it('should make use of app.utils to unformat the value', function() {

            var unformatNumberStringLocale = sinon.spy(app.utils, 'unformatNumberStringLocale');

            field.unformat('123456789.98');
            expect(unformatNumberStringLocale.calledOnce).toBeTruthy();

            unformatNumberStringLocale.restore();
        });

        using('valid values',
            [['5,000.23', '5000.230000'], ['1,345,567.235', '1345567.235000'], ['.33', '0.330000']],
            function(value, result) {
                it('should unformat valid value', function() {
                    expect(field.unformat(value)).toEqual(result);
                });
            });

        using('invalid values',
            [['abc,123.45', 'abc,123.45'], ['12,45.45.', '12,45.45.'], ['12,345.3a', '12,345.3a']],
            function(value, result) {
                it('should not unformat an invalid value', function() {
                    expect(field.unformat(value)).toEqual(result);
                });
            });

        using('valid values',
            [['5.000,23', '5000.230000'], ['1.345.567,235', '1345567.235000'], [',33', '0.330000']],
            function(value, result) {
                it('should unformat valid value with ., swapped', function() {
                    app.user.setPreference('decimal_separator', ',');
                    app.user.setPreference('number_grouping_separator', '.');
                    expect(field.unformat(value)).toEqual(result);
                });
            });

        using('valid values with precision',
            [['5.000,23', '5000.230000'], ['1.345.567,235', '1345567.235000'], [',33', '0.330000']],
            function(value, result) {
                it('should unformat valid value with ., swapped', function() {
                    field.def = {
                        'precision' : 6
                    };
                    app.user.setPreference('decimal_separator', ',');
                    app.user.setPreference('number_grouping_separator', '.');
                    expect(field.unformat(value)).toEqual(result);
                });
            });

        using('invalid values',
            [['abc.123,45', 'abc.123,45'], ['12.45,45,', '12.45,45,'], ['12.345,3a', '12.345,3a']],
            function(value, result) {
                it('should not unformat an invalid value with ., swapped', function() {
                    app.user.setPreference('decimal_separator', ',');
                    app.user.setPreference('number_grouping_separator', '.');
                    expect(field.unformat(value)).toEqual(result);
                });
            });


        it('should render with currencies selector', function() {

            var currencyRender,
                sandbox = sinon.sandbox.create();
            var getCurrencyField = sandbox.stub(field, 'getCurrencyField', function() {
                var currencyField = SugarTest.createField(
                    'base',
                    'amount',
                    'enum',
                    'edit',
                    {
                        options: {'-99': '$ USD' }
                    },
                    moduleName,
                    model
                );
                currencyRender = sandbox.stub(currencyField, 'render', function() {
                    return null;
                });
                sandbox.stub(currencyField, 'setDisabled');

                return currencyField;
            });

            field.render();
            expect(currencyRender).toHaveBeenCalled();

            sandbox.restore();
        });
    });

    describe('new record', function() {
        var field;

        beforeEach(function() {

            sinon.stub(app.metadata, 'getCurrency', function() {
                return {
                    'conversion_rate': '0.900'
                };
            });

            app.user.setPreference('currency_id', 'abc123');
        });

        afterEach(function() {
            app.metadata.getCurrency.restore();
        });

        it('should make new record default to user preferred currency if record not copied', function() {
            model = app.data.createBean(moduleName, {
                amount: 123456789.12,
                currency_id: '-99'
            });
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            expect(field.model.get('currency_id')).toEqual('abc123');
            expect(field.model.get('base_rate')).toEqual('0.900');
        });

        it('should not make new record default to user preferred currency if record is copied', function() {
            model = app.data.createBean(moduleName, {
                amount: 123456789.12,
                currency_id: '-99',
                base_rate: 1.0
            });
            model.isCopied = true;
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            expect(field.model.get('currency_id')).toEqual('-99');
        });

        it('should leave existing record currency if not new', function() {
            model = app.data.createBean(moduleName, {
                id: 'abcdefg9999999',
                amount: 123456789.12,
                currency_id: '-99',
                base_rate: 1.0
            });
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            expect(field.model.get('currency_id')).toEqual('-99');
        });

        it('should leave existing record currency even if new when ignoreUserPrefCurrency is set', function() {
            model = app.data.createBean(moduleName, {
                id: 'abcdefg9999999',
                amount: 123456789.12,
                currency_id: '-99',
                base_rate: 1.0
            });
            model.ignoreUserPrefCurrency = true;

            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            expect(field.model.get('currency_id')).toEqual('-99');
        });
    });

    describe('detail view', function() {
        var field;

        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
        });

        afterEach(function() {
            field = null;
        });

        it('should make use of app.utils to format the value', function() {

            var formatAmountLocale = sinon.spy(app.currency, 'formatAmountLocale');

            field.format(123456789.98);
            expect(formatAmountLocale.calledOnce).toBeTruthy();

            formatAmountLocale.restore();
        });

        it('should be able to convert to base currency when formatting the value', function() {

            var convertWithRate = sinon.spy(app.currency, 'convertWithRate');

            model = app.data.createBean(moduleName, {
                amount: 900.00,
                currency_id: '12a29c87-a685-dbd1-497f-50abfe93aae6',
                base_rate: 0.9
            });
            field.model = model;
            field.def.convertToBase = true;
            field.format(123456789.98);
            expect(convertWithRate.calledOnce).toBeTruthy();

            convertWithRate.restore();
        });

        it('should make use of app.utils to unformat the value', function() {

            var unformatAmountLocale = sinon.spy(app.currency, 'unformatAmountLocale');

            field.unformat('123456789.98');
            expect(unformatAmountLocale.calledOnce).toBeTruthy();

            unformatAmountLocale.restore();
        });

        it('should show transactional amount on render', function() {

            model = app.data.createBean(moduleName, {
                amount: 123456789.12,
                currency_id: '12a29c87-a685-dbd1-497f-50abfe93aae6',
                base_rate: 0.9
            });
            field.model = model;

            field.def.convertToBase = true;
            field.def.showTransactionalAmount = true;
            field.render();
            expect(field.transactionValue).toEqual('$123,456,789.12');

        });

        it('should force currency_id to base on usdollar field', function() {
            model = app.data.createBean(moduleName, {
                amount: 900.00,
                currency_id: '12a29c87-a685-dbd1-497f-50abfe93aae6',
                base_rate: 0.9
            });
            field.model = model;
            field.def.is_base_currency = true;
            var value = field.format(model.get('amount'));
            expect(value).toEqual('$900.00');

        });


        it('should not show transactional amount on render when converted to base rate', function() {
            //convert the field to push a transactionValue as needed
            model = app.data.createBean(moduleName, {
                amount: 123456789.12,
                currency_id: '12a29c87-a685-dbd1-497f-50abfe93aae6',
                base_rate: 0.9
            });
            field.model = model;

            field.def.convertToBase = true;
            field.def.showTransactionalAmount = true;
            field.render();
            expect(field.transactionValue).toEqual('$123,456,789.12');

            //convert the field back to the default currency and expect the transaction value to change back to ''
            model = app.data.createBean(moduleName, {
                amount: 123456789.12,
                currency_id: '-99',
                base_rate: 1.0
            });

            field.model = model;
            field.render();
            expect(field.transactionValue).toEqual('');
        });

        it('transactional amount should be empty when using the base currency and currency_field not set', function() {
            model = app.data.createBean(moduleName, {
                amount: 123456789.12,
                currency_id: '-99',
                base_rate: 1.0
            });
            field.model = model;

            delete field.def.currency_field;
            field.def.convertToBase = true;
            field.def.showTransactionalAmount = true;
            field.render();
            expect(field.transactionValue).toEqual('');
        });

        describe('when user currency_show_preferred', function() {
            describe('is true', function() {
                beforeEach(function() {
                    app.user.setPreference('currency_id', 'abc123');
                    app.user.setPreference('currency_show_preferred', true);
                });

                afterEach(function() {
                    app.user.setPreference('currency_show_preferred', false);
                });

                it('and record has a different currency transactional amount should be set', function() {
                    model = app.data.createBean(moduleName, {
                        amount: 123456789.12,
                        currency_id: '-99',
                        base_rate: 1.0
                    });
                    field.model = model;

                    field.def.convertToBase = true;
                    field.def.showTransactionalAmount = true;
                    field.render();
                    expect(field.transactionValue).toEqual('$123,456,789.12');
                    expect(field.value).toEqual('€111,111,110.21');
                });
            });
        });

        it('should convert value to different currency', function() {

            var sandbox = sinon.sandbox.create();
            model = app.data.createBean(moduleName, {
                amount: 900.00,
                currency_id: '12a29c87-a685-dbd1-497f-50abfe93aae6',
                base_rate: 0.9
            });
            field.model = model;
            field.bindDataChange();

            sandbox.stub(app.currency, 'unformatAmountLocale', function() {
                return false;
            });
            var convertAmount = sandbox.stub(app.currency, 'convertAmount', function() {
                return false;
            });
/*
            sandbox.stub(app.metadata, 'getCurrency', function() {
                return false;
            });
*/

            field.model.set('currency_id', '-99');
            expect(convertAmount.calledOnce).toBeTruthy();

            sandbox.restore();

        });

        it('should not convert when model is cleared and refreshed with new values', function() {
            var sandbox = sinon.sandbox.create(),
                convertAmountStub,
                beforeAttributes = {
                    amount: 900.00,
                    currency_id: '12a29c87-a685-dbd1-497f-50abfe93aae6',
                    base_rate: 0.9
                },
                afterAttributes = {
                    amount: 100.00,
                    currency_id: '-99',
                    base_rate: 1.0
                };
            model = app.data.createBean(moduleName, beforeAttributes);
            field.model = model;
            field.bindDataChange();

            convertAmountStub = sandbox.stub(app.currency, 'convertAmount');

            field.model.clear();
            expect(convertAmountStub.callCount).toBe(0);
            expect(field.model.has('amount')).toBe(false);
            expect(field.model.has('currency_id')).toBe(false); //currency_id should not be defaulted on clear
            expect(_.isUndefined(field._lastCurrencyId)).toBe(true);

            field.model.set(afterAttributes);
            expect(convertAmountStub.callCount).toBe(0);
            expect(field.model.get('amount')).toBe(afterAttributes.amount);
            expect(field._lastCurrencyId).toBe(afterAttributes.currency_id);

            sandbox.restore();
        });

        it('should be empty for null value', function() {
            field.model = app.data.createBean(moduleName, {
                myfield: null
            });
            field.name = 'myfield';
            field.def.convertToBase = true;
            field.def.showTransactionalAmount = true;
            field.format();

            expect(field.transactionValue).toEqual('');
        });
    });
    describe('_valueChangeHandler', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            field.action = 'detail';
            sinon.stub(field, 'render', function() {});
            sinon.stub(field, 'setCurrencyValue', function() {});
            sinon.stub(app.metadata, 'getCurrency', function() {
                return {
                    'conversion_rate': '0.900'
                };
            });
        });

        afterEach(function() {
            field.render.restore();
            field.setCurrencyValue.restore();
            app.metadata.getCurrency.restore();
            field = null;
        });

        it('should call render', function() {
            field.action = 'detail';
            field._valueChangeHandler({}, '123');
            expect(field.render).toHaveBeenCalled();
            expect(field.setCurrencyValue).not.toHaveBeenCalled();
        });

        it('should call setCurrencyValue', function() {
            var model = {get: $.noop};
            field.action = 'edit';
            field._valueChangeHandler(model, '123');
            expect(field.render).not.toHaveBeenCalled();
            expect(field.setCurrencyValue).toHaveBeenCalled();
        });
    });

    describe('bindDataChange', function() {
        var sandbox, field;
        beforeEach(function() {
            sandbox = sinon.sandbox.create();
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'edit',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model
            );
            field._loadTemplate();
        });
        afterEach(function() {
            sandbox.restore();
            field.dispose();
            field = null;
        });

        describe('when hasEditAccess is false', function() {
            beforeEach(function() {
                sandbox.stub(field.model, 'on');
                field.hasEditAccess = false;
            });

            it('should not add handlers for base_rate or currency_id', function() {
                field.bindDataChange();
                expect(field.model.on.callCount).toEqual(1);
                expect(field.model.on.getCall(0).calledWith('change:amount')).toBeTruthy();
            });
        });

        describe('when hasEditAccess is true', function() {
            beforeEach(function() {
                sandbox.stub(field.model, 'on');
                field.hasEditAccess = true;
            });

            it('should not add handlers for base_rate or currency_id when the view action is list', function() {
                field.view.action = 'list';
                field.bindDataChange();
                expect(field.model.on.callCount).toEqual(1);
                expect(field.model.on.getCall(0).calledWith('change:amount')).toBeTruthy();
            });

            it('should add handlers for base_rate or currency_id', function() {
                field.bindDataChange();
                expect(field.model.on.callCount).toEqual(3);
                expect(field.model.on.getCall(0).calledWith('change:amount')).toBeTruthy();
                expect(field.model.on.getCall(1).calledWith('change:base_rate')).toBeTruthy();
                expect(field.model.on.getCall(2).calledWith('change:currency_id')).toBeTruthy();
            });
        });
    });
});
