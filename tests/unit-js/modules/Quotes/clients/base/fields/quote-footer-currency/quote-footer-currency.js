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
describe('Quotes.Base.Fields.QuoteFooterCurrency', function() {
    var app;
    var layout;
    var view;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');

        var def = {
            name: 'shipping',
            type: 'quote-footer-currency'
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'record', {});
        view = SugarTest.createView('base', 'Quotes', 'record', null, null, true, layout);
        field = SugarTest.createField({
            name: 'shipping',
            type: 'quote-footer-currency',
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

    describe('initialize', function() {
        var initOptions;

        beforeEach(function() {
            initOptions = {
                context: {
                    isCreate: function() {
                        return false;
                    }
                }
            };
            sinon.collection.stub(field.model, 'addValidationTask', function() {});
        });

        it('should add model validation task', function() {
            field.initialize(initOptions);

            expect(field.model.addValidationTask).toHaveBeenCalled();
        });

        it('should trigger quotes:editableFields:add to add this field to the record', function() {
            sinon.collection.stub(field.context, 'trigger', function() {});
            field.initialize(initOptions);

            expect(field.context.trigger).toHaveBeenCalledWith('quotes:editableFields:add', field);
        });

        describe('on create view', function() {
            beforeEach(function() {
                initOptions = {
                    context: {
                        isCreate: function() {
                            return true;
                        }
                    }
                };

                field.context = {
                    trigger: sinon.collection.spy()
                };
            });

            afterEach(function() {
                delete field.context;
            });

            it('should not add click events', function() {
                field.events = {};
                field.initialize(initOptions);

                expect(field.events['click .currency-field']).toBeUndefined();
            });

            it('should set options viewName to edit', function() {
                field.initialize(initOptions);

                expect(initOptions.viewName).toBe('edit');
            });

            it('should set action to edit', function() {
                field.initialize(initOptions);

                expect(field.action).toBe('edit');
            });
        });

        describe('on record view', function() {
            beforeEach(function() {
                initOptions = {
                    context: {
                        isCreate: function() {
                            return false;
                        }
                    }
                };

                sinon.collection.stub(field.context, 'trigger', function() {});
            });

            afterEach(function() {
                delete field.context;
            });

            it('should add click events', function() {
                field.events = {};
                field.initialize(initOptions);

                expect(field.events['click .currency-field']).toBeDefined();
            });

            it('should set options viewName to detail', function() {
                field.initialize(initOptions);

                expect(initOptions.viewName).toBe('detail');
            });

            it('should set action to edit', function() {
                field.initialize(initOptions);

                expect(field.action).toBe('detail');
            });
        });
    });

    describe('_toggleFieldToEdit', function() {
        var record;
        var recordContextTriggerSpy;

        beforeEach(function() {
            recordContextTriggerSpy = sinon.collection.spy();
            record = {
                context: {
                    trigger: recordContextTriggerSpy
                }
            };
            sinon.collection.stub(field, 'closestComponent', function() {
                return record;
            });
        });

        describe('when $el is in edit', function() {

            beforeEach(function() {
                field.$el = $('<div class="edit"></div>');
            });

            it('should not trigger the handleEdit event', function() {
                field._toggleFieldToEdit({});

                expect(recordContextTriggerSpy).not.toHaveBeenCalledWith('editable:handleEdit');
            });
        });

        describe('when $el is in detail', function() {
            beforeEach(function() {
                field.$el = $('<div class="detail"></div>');
            });

            it('should trigger the handleEdit event', function() {
                field._toggleFieldToEdit({});

                expect(recordContextTriggerSpy).toHaveBeenCalledWith('editable:handleEdit');
            });
        });
    });

    describe('_doValidateIsNumeric', function() {
        var callback = sinon.collection.stub();
        var errors = [];

        beforeEach(function() {
            sinon.collection.stub(app.lang, 'get', function() {
                return 'foo';
            });
        });

        it('should call the callback without errors ', function() {
            sinon.collection.stub(field.model, 'get', function() {
                return 1;
            });
            field._doValidateIsNumeric([], [], callback);

            expect(errors.shipping).toBeUndefined();
        });

        it('should call the callback with one error', function() {
            sinon.collection.stub(field.model, 'get', function() {
                return 'foo';
            });
            errors[field.name] = 'foo';
            field._doValidateIsNumeric([], [], callback);

            expect(errors.shipping).toBeDefined();
        });
    });

    describe('_dispose', function() {
        it('should call app.utils.formatNumberLocale ', function() {
            sinon.collection.stub(field.model, 'removeValidationTask', function() {});
            field._dispose();

            expect(field.model.removeValidationTask).toHaveBeenCalled();
        });
    });
});
