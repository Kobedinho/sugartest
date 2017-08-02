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
describe('SugarLogic Sidecar Expression Context', function () {
    let sandbox;
    let model;
    let collection;
    let context;
    let view;
    let app;

    beforeEach(function () {
        sandbox = sinon.sandbox.create();
        SugarTest.seedMetadata(true);
        app = SugarTest.app;

        model = app.data.createBean('Contacts', {
            first_name: 'Foo',
            last_name: 'Bar',
        });

        app.plugins.register('SugarLogic', 'view', SUGAR.expressions.plugin);

        model.fields = fixtures.metadata.modules.Contacts.fields;
        collection = new app.BeanCollection([model]);
        view = SugarTest.createComponent('View', {
            context: app.context.getContext({
                url: 'someurl',
                module: 'Contacts',
                model: model,
                collection: collection,
            }),
            name: 'sugarlogictestview',
            platform: 'base',
        });
    });

    afterEach(function () {
        sandbox.restore();
    });

    describe('Plugin Initialization', function () {
        it('should return all fields used in dependencies that are not fields of type link', function () {
            sandbox.stub(view, 'getApplicableDeps', function () {
                return [
                    {
                        name: 'name_vis',
                        hooks: ['all'],
                        trigger: 'true',
                        triggerFields: ['accounts', 'name'],
                        relatedFields: [],
                        onload: true,
                        isRelated: false,
                        actions: [
                            {
                                action: 'SetVisibility',
                                params: { target: 'name', value: 'contains(related($accounts,\"name\"), $last_name)' },
                            },
                        ],
                        notActions: [],
                    },
                    {
                        name: 'vis2',
                        hooks: ['all'],
                        trigger: 'true',
                        triggerFields: ['parent_id'],
                        relatedFields: [],
                        onload: true,
                        isRelated: false,
                        actions: [
                            {
                                action: 'SetVisibility',
                                params: { target: 'description', value: 'greaterThan(2, $parent_id)' },
                            },
                        ],
                        notActions: [],
                    },
                ];
            });

            expect(view._getDepFields()).toEqual(['last_name', 'parent_id']);
        });
    });
});
