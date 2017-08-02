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
describe("Base.View.List", function () {
    var view, layout, app, loadDataStub;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.testMetadata.addViewDefinition("list", {
            "panels":[
                {
                    "name":"panel_header",
                    "header":true,
                    "fields":["name", "case_number", "type", "created_by", "date_entered", "date_modified", "modified_user_id"]
                }
            ],
            last_state: {
                id: 'record-list'
            }
        }, "Cases");
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Cases", "list", null, null);
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
        app = SUGAR.App;
        loadDataStub = sinon.collection.stub(view.context, 'loadData');
    });
    afterEach(function () {
        sinon.collection.restore();
        layout.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('parseFieldMetadata', function() {

        using('different field metadata', [
            {
                'before': {'name': 'test', 'align': 'left'},
                'after': {
                    'name': 'test',
                    'align': 'tleft'
                }
            },
            {
                'before': {'name': 'test', 'align': 'center'},
                'after': {
                    'name': 'test',
                    'align': 'tcenter'
                }
            },
            {
                'before': {'name': 'test', 'align': 'right'},
                'after': {
                    'name': 'test',
                    'align': 'tright'
                }
            },
            {
                'before': {'name': 'test', 'align': 'invalid'},
                'after': {'name': 'test'}
            },
            {
                'before': {'name': 'test', 'width': '30%'},
                'after': {'name': 'test', 'width': '30%'}
            },
            {
                'before': {'name': 'test', 'width': '30px'},
                'after': {
                    'name': 'test',
                    'width': '30px',
                    'styles': 'max-width:30px;min-width:30px',
                    'expectedWidth': 30
                }
            },
            {
                'before': {'name': 'test', 'width': '30'},
                'after': {
                    'name': 'test',
                    'width': '30',
                    'styles': 'max-width:30px;min-width:30px',
                    'expectedWidth': 30
                }
            },
            {
                'before': {'name': 'test', 'width': 'small'},
                'after': {
                    'name': 'test',
                    'width': 'small',
                    'widthClass': 'cell-small',
                    'expectedWidth': 'small'
                }
            },
            {
                'before': {'name': 'test', 'width': 'xlarge'},
                'after': {
                    'name': 'test',
                    'width': 'xlarge',
                    'widthClass': 'cell-xlarge',
                    'expectedWidth': 'xlarge'
                }
            }
        ], function(data) {

            it('should validate and format width and alignment for each field', function() {
                var metadata = {panels: [{fields: [data['before']]}]};
                var options = view.parseFieldMetadata({meta: metadata});

                expect(options.meta.panels[0].fields[0]).toEqual(data['after']);
            });
        });
    });

    it('should set the limit correctly if sorting and offset is already set', function() {
        var options1 = view.getSortOptions(view.collection);
        var offset = 5;
        expect(options1.offset).toBeUndefined();

        view.collection.offset = offset;

        var options2 = view.getSortOptions(view.collection);
        expect(options2.limit).toEqual(offset);
        expect(options2.offset).toEqual(0);

    });
    describe('setOrderBy', function() {
        var testElement = $('<th data-orderby="" data-fieldname="name" class="sorting_desc orderByname"><span>Name</span></th>');
        var event = {
            currentTarget: testElement
        };
        beforeEach(function() {
            view.$el.append(testElement);
        });
        afterEach(function() {
            view.$(testElement).remove();
        });
        it('should set orderby correctly', function() {
            view.setOrderBy(event);
            expect(view.orderBy).toEqual({field: 'name', direction: 'desc'});
        });
        it('should change direction when set order by active field', function() {
            view.setOrderBy(event);
            expect(view.orderBy.direction).toEqual('desc');
            view.setOrderBy(event);
            expect(view.orderBy.direction).toEqual('asc');

        });
        it('should set orderby correctly to collection', function() {
            view.setOrderBy(event);
            expect(view.collection.orderBy).toEqual({field: 'name', direction: 'desc'});
        });

        it('should reset pagination', function() {
            var resetPagination = sinon.collection.stub(view.collection, 'resetPagination');
            view.setOrderBy(event);
            expect(resetPagination).toHaveBeenCalled();
        });
    });

    describe('should use last state for store sorting', function() {

        it('should be orderby last state key not empty', function() {
            expect(view.orderByLastStateKey).not.toBeEmpty();
        });

        it('should call set last state when set order by', function() {
            var lastStateSetStub = sinon.collection.stub(app.user.lastState, 'set');
            var testElement = $('<th data-orderby="" data-fieldname="name" class="sorting_desc orderByname"><span>Name</span></th>');
            var event = {
                currentTarget: testElement
            };
            view.$el.append(testElement);
            view.setOrderBy(event);

            expect(lastStateSetStub).toHaveBeenCalled();
            expect(lastStateSetStub.lastCall.args[1]).toEqual({field: 'name', direction: 'desc'});
        });

        it('should call get last state when initialize view', function() {
            var orderBy = {
                field: 'name',
                direction: 'desc'
            };
            var lastStateGetStub = sinon.collection.stub(app.user.lastState, 'get', function(key) {
                return orderBy;
            });
            var testView = SugarTest.createView("base", "Cases", "list", null, null);

            expect(lastStateGetStub).toHaveBeenCalled();
            expect(testView.orderBy).toEqual(orderBy);
            expect(testView.collection.orderBy).toEqual(orderBy);
        })
    });

    describe('_render', function () {
        it('should render "noaccess" template when user has no access', function () {
            var hasAccessStub = sinon.collection.stub(app.acl, 'hasAccess', function () {
                return false;
            });
            var templateGetStub = sinon.collection.stub(app.template, 'get', function() {
                return function() {
                    return '';
                };
            });
            view.render();
            expect(templateGetStub).toHaveBeenCalled();
            expect(templateGetStub).toHaveBeenCalledWith('list.noaccess');
        });
    });
});
