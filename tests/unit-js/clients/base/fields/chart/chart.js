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
describe('Base.Field.Chart', function() {
    var field, app;
    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base','chart', 'chart', 'detail');
    });

    afterEach(function() {
        app = undefined;
        field = undefined;
    });

    describe('getChartConfig()', function() {
        var rawChartData;
        beforeEach(function() {
            rawChartData = {};
            rawChartData.properties = [];
            rawChartData.values = [0, 1, 2];
            sinon.stub(field, 'bindDataChange', function() {});
            sinon.stub(field, 'generateD3Chart', function() {});
        });

        afterEach(function() {
            rawChartData = undefined;
            field.bindDataChange.restore();
            field.generateD3Chart.restore();
        });

        it('should return proper chart config -- pie chart', function() {
            rawChartData.properties.push({type: 'pie chart'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.pieType).toEqual('basic');
            expect(cfg.chartType).toEqual('pieChart');
        });

        it('should return proper chart config -- line chart', function() {
            rawChartData.properties.push({type: 'line chart'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.lineType).toEqual('basic');
            expect(cfg.chartType).toEqual('lineChart');
        });

        it('should return proper chart config -- funnel chart 3D', function() {
            rawChartData.properties.push({type: 'funnel chart 3D'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.funnelType).toEqual('basic');
            expect(cfg.chartType).toEqual('funnelChart');
        });

        it('should return proper chart config -- gauge chart', function() {
            rawChartData.properties.push({type: 'gauge chart'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.gaugeType).toEqual('basic');
            expect(cfg.chartType).toEqual('gaugeChart');
        });

        it('should return proper chart config -- stacked group by chart', function() {
            rawChartData.properties.push({type: 'stacked group by chart'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.orientation).toEqual('vertical');
            expect(cfg.barType).toEqual('stacked');
            expect(cfg.chartType).toEqual('barChart');
        });

        it('should return proper chart config -- group by chart', function() {
            rawChartData.properties.push({type: 'group by chart'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.orientation).toEqual('vertical');
            expect(cfg.barType).toEqual('grouped');
            expect(cfg.chartType).toEqual('barChart');
        });

        it('should return proper chart config -- bar chart', function() {
            rawChartData.properties.push({type: 'bar chart'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.orientation).toEqual('vertical');
            expect(cfg.barType).toEqual('basic');
            expect(cfg.chartType).toEqual('barChart');
        });

        it('should return proper chart config -- horizontal group by chart', function() {
            rawChartData.properties.push({type: 'horizontal group by chart'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.orientation).toEqual('horizontal');
            expect(cfg.barType).toEqual('stacked');
            expect(cfg.chartType).toEqual('barChart');
        });

        it('should return proper chart config -- horizontal bar chart', function() {
            rawChartData.properties.push({type: 'horizontal bar chart'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.orientation).toEqual('horizontal');
            expect(cfg.barType).toEqual('basic');
            expect(cfg.chartType).toEqual('barChart');
        });

        it('should return proper chart config -- horizontal', function() {
            rawChartData.properties.push({type: 'horizontal'});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.orientation).toEqual('horizontal');
            expect(cfg.barType).toEqual('basic');
            expect(cfg.chartType).toEqual('barChart');
        });

        it('should return proper chart config -- default', function() {
            rawChartData.properties.push({type: ''});
            field.model.set({rawChartData: rawChartData});

            var cfg = field.getChartConfig();
            expect(cfg.orientation).toEqual('vertical');
            expect(cfg.barType).toEqual('stacked');
            expect(cfg.chartType).toEqual('barChart');
        });
    });
});
