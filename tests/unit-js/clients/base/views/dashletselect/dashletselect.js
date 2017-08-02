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
describe('Base.View.Dashletselect', function() {
    var moduleName = 'Home',
        app, view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'filtered-list');
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', moduleName, 'dashletselect');
    });
    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinon.collection.restore();
    });

    describe('get available dashlets', function() {
        it('should get all dashlet views that defines Dashlet plugin', function() {
            var customModule = 'RevenueLineItems';
            SugarTest.loadComponent('base', 'view', 'alert');
            SugarTest.loadComponent('base', 'view', 'dashablelist');
            sinon.collection.stub(app.view, 'componentHasPlugin', function() {
                return true;
            }, this);
            sinon.collection.stub(app.metadata, 'getModuleNames', function() {
                return [customModule];
            });
            SugarTest.testMetadata.addViewDefinition('dashablelist', {
                dashlets: [
                    {
                        config: {}
                    }
                ]
            });
            //custom module dashlet
            SugarTest.testMetadata.addViewDefinition('piechart', {
                dashlets: [
                    {
                        config: {}
                    }
                ]
            }, customModule);
            view.loadData();
            var actual = view.collection;
            expect(actual.length).toBe(2);
            expect(actual.at(0).get('type')).toBe('dashablelist');
            expect(actual.at(1).get('type')).toBe('piechart');
            expect(actual.at(1).get('metadata').module).toBe(customModule);
        });

        it('should get all sub dashlets that defines in dashlets array', function() {
            SugarTest.loadComponent('base', 'view', 'alert');
            SugarTest.loadComponent('base', 'view', 'dashablelist');
            sinon.collection.stub(app.view, 'componentHasPlugin', function() {
                return true;
            }, this);
            SugarTest.testMetadata.addViewDefinition('dashablelist', {
                dashlets: [
                    {
                        label: 'first1',
                        config: {}
                    },
                    {
                        label: 'first2',
                        config: {}
                    }
                ]
            });
            view.loadData();
            var actual = view.collection;
            expect(actual.length).toBe(2);
            expect(actual.at(0).get('type')).toBe('dashablelist');
            expect(actual.at(1).get('type')).toBe('dashablelist');
        });

        it('should filter acl access role for module', function() {
            SugarTest.loadComponent('base', 'view', 'alert');
            SugarTest.loadComponent('base', 'view', 'dashablelist');
            sinon.collection.stub(app.view, 'componentHasPlugin', function() {
                return true;
            }, this);
            var noAccessModules = ['Accounts', 'Contacts'];
            sinon.collection.stub(app.acl, 'hasAccess', function(action, module) {
                return !_.contains(noAccessModules, module);
            });
            SugarTest.testMetadata.addViewDefinition('dashablelist', {
                dashlets: [
                    {
                        label: 'first1',
                        config: {}
                    },
                    {
                        label: 'first2',
                        config: {
                            module: 'Contacts'
                        }
                    },
                    {
                        label: 'first3',
                        config: {
                            module: 'Notes'
                        }
                    }
                ]
            });
            view.loadData();
            var actual = view.collection;
            expect(actual.length).toBe(2);
            expect(actual.at(0).get('type')).toBe('dashablelist');
            expect(actual.at(0).get('title')).toBe('first1');
            expect(actual.at(1).get('type')).toBe('dashablelist');
            expect(actual.at(1).get('title')).toBe('first3');
        });
    });

    describe('getFilteredList', function() {
        it('should get filtered dashlet list', function() {
            SugarTest.loadComponent('base', 'view', 'alert');
            SugarTest.loadComponent('base', 'view', 'dashablelist');
            SugarTest.testMetadata.addViewDefinition('dashablelist', {
                dashlets: [
                    //Matched module and layout
                    {
                        label: 'first1',
                        config: {},
                        filter: {
                            module: [
                                'Home'
                            ],
                            view: 'records'
                        }
                    },
                    //Mismatched the module (Excluded)
                    {
                        label: 'first2',
                        config: {},
                        filter: {
                            module: [
                                'Accounts',
                                'Contacts'
                            ]
                        }
                    },
                    //Matched module without filtering view
                    {
                        label: 'first3',
                        config: {},
                        filter: {
                            module: [
                                'Home',
                                'Contacts'
                            ]
                        }
                    },
                    //Mismatched the view with matched module (Excluded)
                    {
                        label: 'first4',
                        config: {},
                        filter: {
                            module: [
                                'Home'
                            ],
                            view: 'record'
                        }
                    }
                ]
            });
            var contextStub = sinon.stub(app.controller.context, 'get', function(arg) {
                if (arg === 'module') {
                    return moduleName;
                } else {
                    return 'record';
                }
            });

            view.loadData();
            var actualCollection = view.collection;

            contextStub.restore();

            expect(actualCollection.length).toBe(2);
        });
    });

    describe('selectDashlet', function() {
        var metadata;
        beforeEach(function() {
            metadata = {
                'component': 'component',
                'config': {},
                'filter': {},
                'label': 'LBL',
                'type': 'type'
            };
            app.drawer = {
                'load': $.noop
            };
        });

        afterEach(function() {
            metadata = null;
            delete app.drawer;
        });

        it('should load dashlet configurations in the drawer', function() {
            var loadSpy = sinon.collection.spy(app.drawer, 'load');
            view.selectDashlet(metadata);

            expect(loadSpy.calledOnce).toBeTruthy();
            expect(loadSpy.args[0][0].layout.type).toEqual('dashletconfiguration');
        });
    });

    describe('getFields', function() {
        beforeEach(function() {
            view.meta = {
                'panels': [
                    {
                        'fields': ['field1', 'field2']
                    },
                    {
                        'fields': ['field3']
                    },
                    {
                        'fields': ['field4', {name: 'field5'}]
                    }
                ]
            };
        });

        afterEach(function() {
            view.meta = null;
        });

        it('should get a flattened list of fields from the metadata', function() {
            expect(view.getFields()).toEqual(
                ['field1', 'field2', 'field3', 'field4', {name: 'field5'}]
            );
        });
    });

    describe('loadData', function() {
        var dashletCollection;
        beforeEach(function() {
            dashletCollection = [
                {
                    'title': 'Bob',
                    'description': 'starts with B',
                    'filter': {}
                },
                {
                    'title': 'Charlie',
                    'description': 'starts with C',
                    'filter': {}
                },
                {
                    'title': 'Annie',
                    'description': 'starts with A',
                    'filter': {}
                }
            ];
            sinon.collection.stub(view, '_addBaseViews');
            sinon.collection.stub(view, '_addModuleViews');
            sinon.collection.stub(view, 'getFilteredList', function() {
                return dashletCollection;
            });
        });

        afterEach(function() {
            dashletCollection = [];
        });

        it('should reset `filteredCollection` if `this.collection` is not empty',
            function() {
                view.collection.add(dashletCollection);
                var collectionModels = view.collection.models;
                view.filteredCollection = [];

                view.loadData();

                expect(view.collection.models).toEqual(collectionModels);
                expect(view.filteredCollection).toEqual(collectionModels);
            });

        it('should fetch dashable components and sort the models by `title`', function() {
            view.loadData();

            expect(view.collection.dataFetched).toBeTruthy();
            expect(_.map(view.collection.models, function(obj) {
                return obj.get('title');
            })).toEqual(['Annie', 'Bob', 'Charlie']);
        });
    });
});
