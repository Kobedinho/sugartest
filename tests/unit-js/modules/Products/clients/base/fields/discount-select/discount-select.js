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
describe('Products.Base.Field.DiscountSelect', function() {
    var app;
    var field;
    var fieldDef;
    var fieldModel;
    var moduleName = 'Products';

    beforeEach(function() {
        app = SugarTest.app;

        fieldModel = app.data.createBean(moduleName, {
            currency_id: '-99'
        });
        fieldDef = {
            name: 'discount-select',
            type: 'discount-select',
            buttons: [{
                name: 'select_discount_amount_button',
                type: 'rowaction',
                label: 'LBL_DISCOUNT_AMOUNT',
                event: 'button:discount_select_change:click'
            }, {
                name: 'select_discount_percent_button',
                type: 'rowaction',
                label: 'LBL_DISCOUNT_PERCENT',
                event: 'button:discount_select_change:click'
            }]
        };

        sinon.collection.stub(app.metadata, 'getCurrency', function() {
            return {
                name: 'US Dollar',
                symbol: '$'
            };
        });

        field = SugarTest.createField('base', 'discount-select', 'discount-select', 'detail',
            fieldDef, moduleName, fieldModel, null, true);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();

        sinon.collection.restore();

        field = null;
        fieldDef = null;
        app = null;
    });

    describe('initialize()', function() {
        it('should call updateCurrencyStrings and set currentCurrency', function() {
            expect(field.currentCurrency.name).toBe('US Dollar');
            expect(field.currentCurrency.symbol).toBe('$');
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.collection.stub(field.context, 'on', function() {});
            sinon.collection.stub(field.model, 'on', function() {});

            field.bindDataChange();
        });

        it('should call field.context on button:discount_select_change:click', function() {
            expect(field.context.on).toHaveBeenCalledWith('button:discount_select_change:click');
        });

        it('should call field.model on change:currency_id', function() {
            expect(field.model.on).toHaveBeenCalledWith('change:currency_id');
        });
    });

    describe('_render()', function() {
        var removeClassStub;
        var textStub;

        beforeEach(function() {
            textStub = sinon.collection.stub();
            removeClassStub = sinon.collection.stub();

            sinon.collection.stub(field, '_super', function() {});
            sinon.collection.stub(field, '$', function() {
                return {
                    removeClass: removeClassStub,
                    text: textStub
                };
            });
            field.caretIcon = 'caretIcon';
            field.currentDropdownSymbol = '$';

            field._render();
        });

        afterEach(function() {
            textStub = null;
            removeClassStub = null;
        });

        it('should call removeClass', function() {
            expect(removeClassStub).toHaveBeenCalledWith(field.caretIcon);
        });

        it('should call field.model on change:currency_id', function() {
            expect(textStub).toHaveBeenCalledWith('$');
        });
    });

    describe('onDiscountChanged()', function() {
        var modelParam;
        var fieldParam;
        var evtParam;

        beforeEach(function() {
            modelParam = {};
            fieldParam = {};
            evtParam = {};
            field.name = 'discount_select';

            sinon.collection.stub(field, 'updateDropdownSymbol', function() {});
        });

        afterEach(function() {
            modelParam = null;
            fieldParam = null;
            evtParam = null;
        });

        it('should do nothing if the model param is not the field model', function() {
            field.onDiscountChanged(modelParam, fieldParam, evtParam);
            expect(field.updateDropdownSymbol).not.toHaveBeenCalled();
        });

        it('should set discount_select true if the user clicked the percent button', function() {
            modelParam = field.model;
            fieldParam.name = 'select_discount_percent_button';
            field.onDiscountChanged(modelParam, fieldParam, evtParam);
            expect(field.model.get('discount_select')).toBeTruthy();
        });

        it('should set discount_select false if the user clicked the amount button', function() {
            modelParam = field.model;
            fieldParam.name = 'select_discount_amount_button';
            field.onDiscountChanged(modelParam, fieldParam, evtParam);
            expect(field.model.get('discount_select')).toBeFalsy();
        });

        it('should call updateDropdownSymbol', function() {
            modelParam = field.model;
            fieldParam.name = 'select_discount_amount_button';
            field.onDiscountChanged(modelParam, fieldParam, evtParam);
            expect(field.updateDropdownSymbol).toHaveBeenCalled();
        });
    });

    describe('updateDropdownSymbol()', function() {
        beforeEach(function() {
            field.name = 'discount_select';
            field.currentDropdownSymbol = 'TEST';
            field.currentCurrency.symbol = 'BATMAN';
            sinon.collection.stub(field, 'render', function() {});
        });

        it('should set currentDropdownSymbol to percent if discount_select is true', function() {
            field.model.set('discount_select', true);
            field.updateDropdownSymbol();
            expect(field.currentDropdownSymbol).toBe('%');
        });

        it('should set currentDropdownSymbol to currency symbol if discount_select is false', function() {
            field.model.set('discount_select', false);
            field.updateDropdownSymbol();
            expect(field.currentDropdownSymbol).toBe('BATMAN');
        });

        it('should call render', function() {
            field.updateDropdownSymbol();
            expect(field.render).toHaveBeenCalled();
        });
    });

    describe('updateCurrencyStrings()', function() {
        var oldAppLangDirection;

        beforeEach(function() {
            sinon.collection.stub(field, 'updateDropdownSymbol', function() {});
            field.def.buttons = [{
                name: 'select_discount_amount_button',
                label: 'TEST'
            }];
            oldAppLangDirection = app.lang.direction;
        });

        it('should set currentCurrency to the latest current currency', function() {
            field.updateCurrencyStrings();
            expect(field.currentCurrency.name).toBe('US Dollar');
            expect(field.currentCurrency.symbol).toBe('$');
        });

        it('should set button label to the current currency', function() {
            app.lang.direction = 'ltr';
            field.updateCurrencyStrings();
            expect(field.def.buttons[0].label).toBe('$ US Dollar');
            app.lang.direction = oldAppLangDirection;
        });

        it('should set button label to the current currency in RTL', function() {
            app.lang.direction = 'rtl';
            field.updateCurrencyStrings();
            expect(field.def.buttons[0].label).toBe('US Dollar $');

            app.lang.direction = oldAppLangDirection;
        });

        it('should call update the label on the dropdown field for select_discount_amount_button', function() {
            app.lang.direction = 'ltr';
            field.dropdownFields = [{
                name: 'select_discount_amount_button',
                label: 'oldLabel'
            }];
            field.updateCurrencyStrings();
            expect(field.dropdownFields[0].label).toBe('$ US Dollar');
        });

        it('should call updateDropdownSymbol', function() {
            field.updateCurrencyStrings();
            expect(field.updateDropdownSymbol).toHaveBeenCalled();
        });
    });
});
