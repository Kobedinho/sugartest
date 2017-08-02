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
describe('app.cache', function () {
    var app;

    beforeEach(function () {
        app = SugarTest.app;

        this.sandbox = sinon.sandbox.create();

        let sugarStore = _.extend({}, store, {
            // make store compatible with stash
            cut: store.remove,
            cutAll: store.clear,
        });
        this.sandbox.stub(app.cache, 'store', sugarStore);
    });

    afterEach(function () {
        app.cache.cutAll(true);
        this.sandbox.restore();
    });

    it('should store strings', function () {
        var value = "This is a test.",
            key = "testKey";
        app.cache.set(key, value);
        expect(app.cache.get(key)).toEqual(value);
    });

    it('should store objects', function () {
        var value = {foo: "test", bar:{more:"a"}},
            key = "testKey";
        app.cache.set(key, value);
        expect(app.cache.get(key)).toEqual(value);
    });

    it('should remove values', function () {
        var value = "Hello",
            key = "testKey";
        app.cache.set(key, value);
        expect(app.cache.get(key)).toEqual(value);

        app.cache.cut(key);
        expect(app.cache.get(key)).toBeFalsy();
    });

    it('should provide has to determine if key exists', function () {
        var value = "Hello",
            key = "testKey";
        app.cache.set(key, value);
        app.cache.cut(key);
        expect(app.cache.has(key)).toBeFalsy();
    });

    it('should remove all values', function () {
        var value = "Hello",
            key = "testKey",
            key2 = "testKey2";
        app.cache.set(key, value);
        app.cache.set(key2, value);
        expect(app.cache.get(key)).toEqual(value);
        expect(app.cache.get(key2)).toEqual(value);

        app.cache.cutAll();
        expect(app.cache.get(key)).toBeFalsy();
        expect(app.cache.get(key2)).toBeFalsy();
    });

    it('should clean up unimportant values when clean is called', function() {
        var k1 = "notImportant",
            k2 = "important",
            callback =  function(cb) {
                cb([k2]);
            };

        app.cache.on("cache:clean", callback);

        app.cache.set(k1, "foo");
        app.cache.set(k2, "bar");

        app.cache.clean();

        expect(app.cache.get(k1)).toBeUndefined()
        expect(app.cache.get(k2)).toEqual("bar");

        app.cache.off("cache:clean", callback);
    });

    it('should call clean when a quota error occurs', function() {
        var e = {name: "QUOTA_EXCEEDED_ERR"},
            spy = sinon.spy(app.cache, "clean"),
            set = sinon.stub(app.cache.store, 'set').throws(e);

        expect(function(){app.cache.set("foo", "bar")}).toThrow(e);
        expect(spy).toHaveBeenCalled();
        spy.restore();
        set.restore();
    });

    it('should noop when some other error occurs', function() {
        var e = {name: 'RANDOM_ERR'},
            spy = sinon.spy(app.cache, 'clean'),
            set = sinon.stub(app.cache.store, 'set').throws(e);

        app.cache.set('foo', 'bar');
        expect(spy).not.toHaveBeenCalled();
        spy.restore();
        set.restore();
    });

    it('should migrate from stash to store', function() {

        this.sandbox.stub(app, 'config', {
            uniqueKey: 'UniqueFromTest',
            env: 'test',
            appId: 'Sidecar',
        });

        let buildKey = (key) => `${app.config.env}:${app.config.appId}:${key}`;

        let storage = {
            '1:last-state:footer-tutorial:toggle-show-tutorial': {
                actual: '\'1679-ULT-7.8.0.0\'',
                expected: '1679-ULT-7.8.0.0',
            },
            'meta:public:hash': {
                actual: '\'a0b1d71dd8d6e2c71955a6483931df39\'',
                expected: 'a0b1d71dd8d6e2c71955a6483931df39',
            },
            'tutorialPrefs': {
                actual: "{'showTooltip':true,'viewedVersion':{'recordHome':1},'skipVersion':{}}",
                expected: { showTooltip: true, viewedVersion: { recordHome: 1 }, skipVersion: {} },
                equivalence: true,
            },
            'already_migrated': {
                actual: '"1679-ULT-7.8.0.0"',
                expected: '1679-ULT-7.8.0.0',
            },
            'boolean_case1': {
                actual: 'true',
                expected: true,
            },
            'boolean_case2': {
                actual: 'false',
                expected: false,
            },
            'boolean_case3': {
                actual: true,
                expected: true,
            },
            'empty_case_1': {
                actual: '""',
                expected: '',
            },
            'empty_case_2': {
                actual: '\'\'',
                expected: '',
            },
            'empty_case_3': {
                actual: '0',
                expected: 0,
            },
            'empty_case_4': {
                actual: '\'0\'',
                expected: '0',
            },
            'empty_case_5': {
                actual: '[]',
                expected: [],
                equivalence: true,
            },
            'empty_case_6': {
                actual: 'null',
                expected: undefined,
            },
        };

        _.each(storage, function(value, key) {
            localStorage.setItem(buildKey(key), value.actual);
        });

        app.cache.init();

        _.each(storage, function(value, key) {
            if (value.equivalence) {
                expect(app.cache.get(key)).toEqual(value.expected);
            } else {
                expect(app.cache.get(key)).toBe(value.expected);
            }
        });
    });

    it('should not migrate localStorage keys if already migrated', function() {

        this.sandbox.stub(app, 'config', {
            uniqueKey: 'UniqueFromTest',
            env: 'test',
            appId: 'Sidecar',
        });

        let buildKey = (key) => `${app.config.env}:${app.config.appId}:${key}`;

        let storage = {
            'uniqueKey': 'UniqueFromTest',
            'key-that-should-be-migrated': '\'1679-ULT-7.8.0.0\'',
        };

        _.each(storage, function(value, key) {
            localStorage.setItem(buildKey(key), value);
        });

        app.cache.init();

        expect(app.cache.get('uniqueKey')).toBe('UniqueFromTest');
        expect(app.cache.get('key-that-should-be-migrated')).toBe('\'1679-ULT-7.8.0.0\'');
    });

    it('should clear localStorage when uniqueKey does not match', function() {
        this.sandbox.stub(app, 'config', {
            uniqueKey: 'UniqueFromTest',
            env: 'test',
            appId: 'Sidecar',
        });

        let buildKey = (key) => `${app.config.env}:${app.config.appId}:${key}`;

        let storage = {
            'uniqueKey': 'DifferentKey',
            'data': 'to-be-cleared',
        };

        _.each(storage, function(value, key) {
            localStorage.setItem(buildKey(key), value);
        });

        app.cache.init();

        expect(app.cache.get('uniqueKey')).toBe('UniqueFromTest');
        expect(app.cache.has('data')).toBeFalsy();
    });
});
