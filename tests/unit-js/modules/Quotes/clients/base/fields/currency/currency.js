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
describe('Quotes.Base.Fields.Currency', function() {
    var app;
    var layout;
    var view;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');

        var def = {
            name: 'deal_tot',
            type: 'currency'
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'record', {});
        view = SugarTest.createView('base', 'Quotes', 'record', null, null, true, layout);
        field = SugarTest.createField({
            name: 'deal_tot',
            type: 'currency',
            viewName: 'detail',
            fieldDef: def,
            module: 'Quotes',
            model: view.model,
            loadFromModule: true
        });
        sinon.collection.stub(field, '_super');
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        layout.dispose();
        field.dispose();
        view = null;
        layout = null;
        app = null;
    });

    describe('bindDataChange()', function() {
        it('should set a field change listener for deal_tot_discount_percentage', function() {
            sinon.collection.spy(field.model, 'on');
            field.view.name = 'quote-data-grand-totals-header';
            field.bindDataChange();

            expect(field.model.on).toHaveBeenCalledWith('change:deal_tot_discount_percentage');
        });
    });

    describe('_updateDiscountPercent()', function() {
        it('should leave valuePercent undefined if deal_tot_discount_percentage is undefined', function() {
            field.model.set('deal_tot_discount_percentage', undefined);

            expect(field.valuePercent).toBeUndefined();
        });

        it('should leave valuePercent undefined if field name is not deal_tot', function() {
            field.view.name = 'quote-data-grand-totals-header';
            field.name = 'testField';
            field.model.set('deal_tot_discount_percentage', 10);

            expect(field.valuePercent).toBeUndefined();
        });

        it('should leave valuePercent undefined if field view name is not quote-data-grand-totals-header', function() {
            field.name = 'deal_tot';
            field.model.set('deal_tot_discount_percentage', 10);

            expect(field.valuePercent).toBeUndefined();
        });

        describe('when field name is deal_tot and field view name is header view', function() {
            var oldLangDir;
            var percent;
            beforeEach(function() {
                sinon.collection.stub(app.user, 'getPreference', function() {
                    return 2;
                });
                field.name = 'deal_tot';
                field.view.name = 'quote-data-grand-totals-header';
                field.bindDataChange();
                oldLangDir = app.lang.direction;
            });

            afterEach(function() {
                app.lang.direction = oldLangDir;
                oldLangDir = null;
            });

            it('should set valuePercent using deal_tot_discount_percentage in LTR', function() {
                app.lang.direction = 'ltr';
                field.model.set('deal_tot_discount_percentage', 10);
                percent = app.utils.formatNumber(10, false, app.user.getPreference('decimal_separator'));

                expect(field.valuePercent).toBe(percent + '%');
            });

            it('should set valuePercent using deal_tot_discount_percentage in RTL', function() {
                app.lang.direction = 'rtl';
                field.model.set('deal_tot_discount_percentage', 10);
                percent = app.utils.formatNumber(10, false, app.user.getPreference('decimal_separator'));

                expect(field.valuePercent).toBe('%' + percent);
            });
        });
    });
});
