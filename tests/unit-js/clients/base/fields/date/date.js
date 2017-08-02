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
describe('Base.Field.Date', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;

        // FIXME: this should be removed when SC-2395 gets in since new
        // versions are capable of handling translations by themselves
        sinon.collection.stub(app.metadata, 'getStrings', function() {
            return {
                dom_cal_day_long: {0: '', 1: 'Sunday', 2: 'Monday', 3: 'Tuesday', 4: 'Wednesday', 5: 'Thursday', 6: 'Friday', 7: 'Saturday'},
                dom_cal_day_short: {0: '', 1: 'Sun', 2: 'Mon', 3: 'Tue', 4: 'Wed', 5: 'Thu', 6: 'Fri', 7: 'Sat'},
                dom_cal_month_long: {0: '', 1: 'January', 2: 'February', 3: 'March', 4: 'April', 5: 'May', 6: 'June', 7: 'July', 8: 'August', 9: 'September', 10: 'October', 11: 'November', 12: 'December'},
                dom_cal_month_short: {0: '', 1: 'Jan', 2: 'Feb', 3: 'Mar', 4: 'Apr', 5: 'May', 6: 'Jun', 7: 'Jul', 8: 'Aug', 9: 'Sep', 10: 'Oct', 11: 'Nov', 12: 'Dec'}
            };
        });
    });

    afterEach(function() {
        sinon.collection.restore();

        app.cache.cutAll();
        app.view.reset();
    });

    describe('format', function() {
        var field;

        beforeEach(function() {
            sinon.collection.spy(app, 'date');
            sinon.collection.spy(app.date.fn, 'formatUser');

            sinon.collection.stub(app.user, 'getPreference')
                .withArgs('datepref').returns('d/m/Y');

            field = SugarTest.createField('base', 'date', 'date', 'edit');
        });

        afterEach(function() {
            field.dispose();
        });

        it('should format according to user preferences', function() {
            expect(field.format('1984-01-15')).toBe('15/01/1984');
            expect(app.date).toHaveBeenCalledWith('1984-01-15');
            expect(app.date.fn.formatUser).toHaveBeenCalledWith(true);
        });

        it('should return undefined if an invalid date is supplied', function() {
            expect(field.format()).toBeUndefined();
            expect(field.format('1984-01-32')).toBeUndefined();
        });
    });

    describe('unformat', function() {
        var field;

        beforeEach(function() {
            sinon.collection.spy(app, 'date');
            sinon.collection.spy(app.date, 'convertFormat');
            sinon.collection.spy(app.date.fn, 'formatServer');

            sinon.collection.stub(app.user, 'getPreference')
                .withArgs('datepref').returns('d/m/Y');

            field = SugarTest.createField('base', 'date', 'date', 'edit');
        });

        afterEach(function() {
            field.dispose();
        });

        it('should unformat based on user preferences and according to server format', function() {
            expect(field.unformat('15/01/1984')).toBe('1984-01-15');
            expect(app.date.convertFormat).toHaveBeenCalledWith('d/m/Y');
            expect(app.date.lastCall.args[0]).toBe('15/01/1984');
            expect(app.date.lastCall.args[2]).toBe(true);
            expect(app.date.fn.formatServer).toHaveBeenCalledWith(true);
        });

        it('should return undefined if an invalid date is supplied', function() {
            expect(field.unformat()).toBeUndefined();
            expect(field.unformat('32/01/1984')).toBeUndefined();
        });

        it('should return \'\' if an empty string is supplied', function() {
            expect(field.unformat('')).toBe('');
        });
    });

    describe('defaults', function() {
        beforeEach(function() {
            var tomorrow = new Date('Sun Jan 15 1984 19:20:42');

            sinon.collection.spy(app, 'date');

            sinon.collection.stub(app.date, 'parseDisplayDefault')
                .withArgs('every other week').returns(undefined)
                .withArgs('+1 day').returns(tomorrow);

            sinon.collection.stub(app.user, 'getPreference')
                .withArgs('datepref').returns('d/m/Y');
        });

        it('should use default value if model has none', function() {
            var fieldDef = {display_default: '+1 day'},
                field = SugarTest.createField('base', 'date', 'date', 'detail', fieldDef);

            field.render();

            expect(field.value).toBe('15/01/1984');
            expect(field.model.get(field.name)).toBe('1984-01-15');
            expect(field.model.getDefault(field.name)).toBe('1984-01-15');

            field.dispose();
        });

        it('should not use default value if default value is invalid', function() {
            var fieldDef = {display_default: 'every other week'},
                field = SugarTest.createField('base', 'date', 'date', 'detail', fieldDef);

            field.render();

            expect(field.value).toBeNull();
            expect(field.model.get(field.name)).toBeUndefined();

            field.dispose();
        });

        it('should not use default value if model has a value', function() {
            var model = new app.data.createBean('Accounts', {date: '1985-01-26'}),
                fieldDef = {display_default: '+1 day'},
                field = SugarTest.createField('base', 'date', 'date', 'detail', fieldDef, 'Accounts', model);

            field.render();

            expect(field.value).toBe('26/01/1985');
            expect(field.model.get(field.name)).toBe('1985-01-26');

            field.dispose();
        });
    });

    describe('render', function() {
        describe('edit', function() {
            var field;

            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('date', 'field', 'base', 'edit');
                SugarTest.testMetadata.set();

                sinon.collection.stub(app.user, 'getPreference')
                    .withArgs('datepref').returns('d/m/Y');

                field = SugarTest.createField('base', 'date', 'date', 'edit');
            });

            afterEach(function() {
                field.dispose();

                SugarTest.testMetadata.dispose();
                Handlebars.templates = {};
            });

            it('should have date picker defined only in edit mode', function() {
                field.render();

                expect(field.$(field.fieldTag).data('datepicker')).toBeDefined();

                field.dispose();

                field = SugarTest.createField('base', 'date', 'date', 'detail');
                field.render();

                expect(field.$(field.fieldTag).data('datepicker')).toBeUndefined();
            });

            it('should hide the open datepicker', function() {
                var $date;
                var spy = sinon.collection.spy();

                field.render();

                $date = field.$(field.fieldTag);
                $date.datepicker('show');
                $date.datepicker().on('hide', spy);

                // This will cause the field to be rendered in detail mode.
                // This simulates the scenario where the datepicker has been
                // shown and the field is re-rendered without the datepicker
                // losing focus.
                field.setMode('detail');

                expect(spy).toHaveBeenCalled();
            });

            it('should update field value when date value changes through date picker', function() {
                field.render();

                expect(field.$(field.fieldTag).val()).toBe('');
                expect(field.model.get(field.name)).toBeUndefined();

                field.$(field.fieldTag).val('15/01/1984').trigger('hide');

                expect(field.model.get(field.name)).toBe('1984-01-15');
            });

            it('should update field value when date value manually changes', function() {
                field.render();

                expect(field.$(field.fieldTag).val()).toBe('');
                expect(field.model.get(field.name)).toBeUndefined();

                // FIXME: `hide` event is still triggered due to the way the
                // library works, this should be reviewed once SC-2395 gets in
                field.$(field.fieldTag).val('15/01/1984').trigger('hide');

                expect(field.model.get(field.name)).toBe('1984-01-15');
            });

            it('should not set empty value if model value not defined to prevent unsaved changes warning', function() {
                field.render();

                expect(field.$(field.fieldTag).val()).toBe('');
                expect(field.model.get(field.name)).toBeUndefined();

                // FIXME: this should be reviewed once SC-2395 gets in
                field.$(field.fieldTag).trigger('hide');

                expect(field.model.get(field.name)).toBeUndefined();
            });
        });

        describe('massupdate', function() {
            var field;

            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('date', 'field', 'base', 'edit');
                SugarTest.testMetadata.set();

                sinon.collection.stub(app.user, 'getPreference')
                    .withArgs('datepref').returns('d/m/Y');

                field = SugarTest.createField('base', 'date', 'date', 'edit');
            });

            afterEach(function() {
                field.dispose();

                SugarTest.testMetadata.dispose();
                Handlebars.templates = {};
            });

            it('will call _setupDatePicker', function() {
                sinon.collection.spy(field, '_setupDatePicker');

                field.render();
                expect(field._setupDatePicker).toHaveBeenCalled();
            });
        });
    });
});
