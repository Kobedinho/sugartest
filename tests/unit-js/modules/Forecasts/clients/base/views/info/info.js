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

describe('Forecasts.Base.Views.Info', function() {
    var view, layout, moduleName = 'Forecasts', sbox = sinon.sandbox.create();

    beforeEach(function() {

        var viewMeta = {
            datapoints: [
                {
                    name: 'quota',
                    label: 'LBL_QUOTA',
                    type: 'quotapoint'
                },
                {
                    name: 'worst_case',
                    label: 'LBL_WORST',
                    type: 'datapoint'
                }
            ]
        };

        layout = SugarTest.createLayout('base', 'ForecastWorksheets', 'list', null, null);
        view = SugarTest.createView('base', moduleName, 'info', viewMeta, null, true, layout, true);
    });

    afterEach(function() {
        view = null;
        layout = null;
        sbox.restore();
    });

    describe('when resetSelection is called', function() {
        beforeEach(function() {
            view.fields = [{
                name: 'selectedTimePeriod',
                render: function() {
                },
                dispose: function() {
                }
            }];
            sbox.spy(view.fields[0], 'render');
            sbox.stub(view.tpModel, 'set', function() {
            });
            sbox.stub(view, 'dispose', function() {
            });

            view.resetSelection();
        });

        it('should have called render', function() {
            expect(view.fields[0].render).toHaveBeenCalled();
        });

        it('should have called set on tpModel', function() {
            expect(view.tpModel.set).toHaveBeenCalled();
        });
    });

    describe('tpModel is changed', function() {
        var tpMapValues = {
            start: '2014-01-01',
            end: '2014-03-31'
        };
        beforeEach(function() {
            sbox.stub(view.context, 'trigger', function(event, model, object) {
            });
            sbox.stub(view, 'getField', function() {
                return {
                    tpTooltipMap: {
                        'test_1': tpMapValues
                    }
                };
            });
        });

        it('will trigger event with model and object', function() {
            var m = new Backbone.Model({selectedTimePeriod: 'test_1'});
            view.tpModel.trigger('change', m);

            expect(view.context.trigger).toHaveBeenCalled();
            expect(view.context.trigger).toHaveBeenCalledWith('forecasts:timeperiod:changed', m, tpMapValues);
        });
    });
});
