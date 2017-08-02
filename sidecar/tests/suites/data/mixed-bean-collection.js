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
describe("MixedBeanCollection", function() {
    var metadata;
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        metadata = SugarTest.loadFixture("metadata");
    });

    afterEach(function() {
        sandbox.restore();
    });

    it("should be able to fetch records that belong to different modules", function() {
        app.config.maxQueryResult = 2;
        app.data.declareModels(metadata.modules);
        var records = app.data.createMixedBeanCollection();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/globalsearch\?max_num=2&module_list=Accounts%2CContacts.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(fixtures.api["rest/v10/search"].GET.response)]);

        records.fetch({
            module_list: ["Accounts","Contacts"],
            apiOptions: {
                useNewApi: true
            }

        });
        SugarTest.server.respond();

        expect(records.module_list).toEqual(["Accounts","Contacts"]);
        expect(records.length).toEqual(2);

        records.each(function(record) {
            expect(record instanceof app.Bean).toBeTruthy();
        });

        expect(records.models[0].module).toEqual("Contacts");
        expect(records.models[1].module).toEqual("Accounts");

    });

    it("should be able to group models by module", function() {

        app.data.declareModels(metadata.modules);
        var records = app.data.createMixedBeanCollection();

        records.add(app.data.createBean("Accounts", { name: "Apple" }));
        records.add(app.data.createBean("Cases", { subject: "A" }));
        records.add(app.data.createBean("Cases", { subject: "B" }));
        records.add(app.data.createBean("Contacts", { name: "John Smith" }));
        records.add(app.data.createBean("Accounts", { name: "Microsoft" }));
        records.add(app.data.createBean("Cases", { subject: "C" }));

        var groups = records.groupByModule();

        expect(groups["Accounts"]).toBeDefined();
        expect(groups["Accounts"].length).toEqual(2);

        expect(groups["Contacts"]).toBeDefined();
        expect(groups["Contacts"].length).toEqual(1);

        expect(groups["Cases"]).toBeDefined();
        expect(groups["Cases"].length).toEqual(3);

    });

    describe('keeping the collection and its related link collections in sync', function() {
        var accounts;
        var contacts;
        var collection;
        beforeEach(function() {
            app.data.declareModels(metadata.modules);
            accounts = app.data.createBeanCollection('Accounts');
            collection = app.data.createMixedBeanCollection(null, {links: {accounts: accounts, contacts: 'Contacts'}});
            contacts = collection._linkedCollections['contacts'];
        });

        it('should have a reference to its links collections if links are passed when creating the collection', function() {
            expect(collection._linkedCollections['Contacts'] instanceof app.BeanCollection);
            expect(collection._linkedCollections['Accounts'] instanceof app.BeanCollection);
        });

        describe('Listening to its linked collections changes', function() {
            it('should update its models when `add` or `remove` is triggered in one of its linked collection', function() {
                sandbox.spy(collection, 'trigger');

                expect(collection.length === 0).toBe(true);

                accounts.add({name: 'blah'});

                expect(collection.length === 1).toBe(true);

                expect(collection.trigger).toHaveBeenCalledWith('add');
                expect(collection.trigger.withArgs('update').calledOnce).toBe(true);

                accounts.remove(accounts.at(0));

                expect(collection.length === 0).toBe(true);

                expect(collection.trigger).toHaveBeenCalledWith('remove');
                expect(collection.trigger.withArgs('update').calledTwice).toBe(true);
            });

            it('should update its models when `reset` is triggered on one of its linked collection', function() {
                accounts.add({id:'1234', name: 'foo', _link: 'accounts'});
                accounts.add({id:'2345', name: 'bar', _link: 'accounts'});
                contacts.add({id:'3456', name: 'bar', _link: 'contacts'});

                expect(collection.length === 3).toBe(true);
                accounts.reset();

                expect(collection.length === 1).toBe(true)
                expect(collection.get('1234')).not.toBeDefined();
                expect(collection.get('3456')).toBeDefined();

                contacts.reset([{id: '4567', _link: 'contacts'}, {id: '5678', _link: 'accounts'}]);

                expect(collection.length === 2).toBe(true);
                expect(collection.get('3456')).not.toBeDefined();
                expect(collection.get('4567')).toBeDefined();
            });

            it('should remove a model from the delta when it gets created and then removed from the collection', function() {
                let newAccount = app.data.createBean('accounts', { name: 'foo', _link: 'accounts' });
                accounts.add(newAccount);

                let expectedDelta = {
                    accounts: {
                        create: [
                            {name: 'foo', _link: 'accounts'}
                        ]
                    },
                };

                expect(collection.getDelta()).toEqual(expectedDelta);

                accounts.remove(newAccount);

                expect(accounts.length).toEqual(0);
                expect(collection.getDelta()).toEqual({});

                collection.add({ name: 'foo', _link: 'accounts' });

                expect(collection.getDelta()).toEqual(expectedDelta);

                collection.remove(collection.at(0));
                expect(collection.getDelta()).toEqual({});
            });

            it('should not trigger any event if an already existing model is added to a linked collection', function() {
                accounts.add({id:'1234', name: 'foo', _link: 'accounts'});
                sandbox.spy(accounts, 'trigger');
                sandbox.spy(collection, 'trigger');
                expect(collection.length).toEqual(1);

                let account = collection.at(0);
                accounts.add(account);

                expect(collection.length).toEqual(1);
                expect(accounts.trigger).not.toHaveBeenCalledWith('add');
                expect(collection.trigger).not.toHaveBeenCalledWith('add');
            });
        });

        describe('adding a model', function() {
            it('should add the model to the matching link collection', function() {
                collection.add({name: 'blah', _link: 'accounts'});
                expect(accounts.length).toEqual(1);
            });

            it('should not do anything if the model already exists in the collection', function() {
                collection.add({name: 'blah', _link: 'accounts'});
                sandbox.spy(collection, 'trigger');
                expect(accounts.length).toEqual(1);

                let account = collection.at(0);
                collection.add(account);

                expect(accounts.length).toEqual(1);
                expect(collection.trigger).not.toHaveBeenCalledWith('add');
            });
        });

        describe('removing a model', function() {
            it('should remove the model from the corresponding link collection', function() {
                collection.add({name: 'blah', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blou', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'bloff', _link: 'accounts', _module: 'Accounts'});

                expect(collection.length).toEqual(3);
                expect(contacts.length).toEqual(2);
                expect(accounts.length).toEqual(1);

                collection.remove(collection.at(0));

                expect(collection.length).toEqual(2);
                expect(contacts.length).toEqual(1);

                collection.remove(collection.models);

                expect(collection.length).toEqual(0);
                expect(contacts.length).toEqual(0);
                expect(accounts.length).toEqual(0);
            });
        });

        describe('resetting the collection', function() {
            it('should reset the corresponding link collections the same way', function() {
                collection.add({name: 'blah', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blou', _link: 'contacts', _module: 'Contacts'});
                sandbox.spy(contacts, 'trigger');

                expect(contacts.length).toEqual(2);

                collection.reset();

                expect(contacts.length).toEqual(0);

                expect(contacts.trigger).toHaveBeenCalledWith('reset');
                expect(contacts.trigger).toHaveBeenCalledOnce();

                sandbox.spy(accounts, 'trigger');
                contacts.trigger.reset();

                collection.reset([{name: 'blah', _link: 'contacts', _module: 'Contacts'}, {name: 'blou', _link: 'contacts', _module: 'Contacts'}, {name: 'bloff', _link: 'accounts', _module: 'Accounts'}]);

                expect(contacts.length).toEqual(2);
                expect(accounts.length).toEqual(1);

                expect(contacts.trigger).toHaveBeenCalledWith('reset');
                expect(contacts.trigger).toHaveBeenCalledOnce();
                expect(accounts.trigger).toHaveBeenCalledWith('reset');
                expect(accounts.trigger).toHaveBeenCalledOnce();
                expect(collection.length === 3).toBe(true);
            });
        });

        describe('getDelta', function() {
            it('should return a hash containing the changes made on the linked collections', function() {
                collection.add({name: 'blah', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blou', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blou', _link: 'accounts', _module: 'Accounts'});
                accounts.add({name: 'foo', _link: 'accounts'});
                contacts.add({name: 'bar', _link: 'contacts'});

                var delta = collection.getDelta();
                var expectedDelta = {
                    accounts: {
                        create: [
                        {name: 'blou', _link: 'accounts', _module: 'Accounts'},
                        {name: 'foo', _link: 'accounts'}
                        ]
                    },
                    contacts: {
                        create: [
                        // '`field_0: 100` is a default field from the Contacts metadata fixture.
                        {name: 'blah', _link: 'contacts', _module: 'Contacts', field_0: 100},
                        {name: 'blou', _link: 'contacts', _module: 'Contacts', field_0: 100},
                        {name: 'bar', _link: 'contacts', field_0: 100}
                        ]
                    }
                };

                expect(delta).toEqual(expectedDelta);

                collection.reset();

                expect(collection.getDelta()).toEqual({});
            });
        });

        describe('resetDelta', function () {
            it('should resets the delta of all its links', function () {
                collection.add({ name: 'blah', _link: 'contacts', _module: 'Contacts' });
                collection.add({ name: 'blou', _link: 'contacts', _module: 'Contacts' });
                collection.add({ name: 'blou', _link: 'accounts', _module: 'Accounts' });

                expect(_.isEmpty(collection.getDelta())).toBe(false);
                expect(_.isEmpty(contacts.getDelta())).toBe(false);
                expect(_.isEmpty(accounts.getDelta())).toBe(false);

                collection.resetDelta();

                expect(_.isEmpty(collection.getDelta())).toBe(true);
                expect(_.isEmpty(contacts.getDelta())).toBe(true);
                expect(_.isEmpty(accounts.getDelta())).toBe(true);
            });
        });
    });
});
