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
(function(test) {
    var app = SUGAR.App;
    test.loadComponent = function(client, type, name, module) {
        var path = "/clients/" + client + "/" + type + "s/" + name;
        path = (module) ? "../modules/" + module + path : ".." + path;

        SugarTest.loadFile(path, name, "js", function(data) {
            try {
                data = eval(data);
            } catch(e) {
                app.logger.error("Failed to eval view controller for " + name + ": " + e + ":\n" + data);
            }
            test.addComponent(client, type, name, data, module);
        });
    };

    test.addComponent = function(client, type, name, data, module) {
        if (type === 'data') {
            if (name === 'model') {
                app.data.declareModelClass(module, null, client, data);
            } else {
                app.data.declareCollectionClass(module, client, data);
            }
        } else {
            app.view.declareComponent(type, name, module, data, true, client);
        }
    };

    test.loadPlugin = function(name, subdir) {
        subdir = subdir ? '/' + subdir : '';
        var path = '../include/javascript/sugar7/plugins' + subdir;
        SugarTest.loadFile(path, name, 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
    };

    test.loadHandlebarsTemplate = function(name, type, client, template, module) {
        var templateName = template || name;
        var path = "/clients/" + client + "/" + type + "s/" + name;
        path = (module) ? "../modules/" + module + path : ".." + path;
        SugarTest.loadFile(path, templateName, "hbs", function(data) {
            test.testMetadata.addTemplate(name, type, data, templateName, module);
        });
    };

    /**
     * Used to create a field object of a given type. Can load the relevent controller automatically from source. 
     * 
     * @param {Object} options Object containing the list of options to pass to this function
     * 
     * - client {String} optional name of client to load the controller from
     * - name {String}
     * - type {String}
     * - viewName {String}
     * - fieldDef {Object}
     * - module {String} optional
     * - model {Backbone.Model} optional
     * - context {app.Context} optional
     * - loadFromModule {boolean} optional when true will attempt to load source file from the Module directory.
     * - loadJsFile {boolean optional defaults to true. When true will attempt to load the controller from source file.
     * @return {app.view.Field}
     */
    test.createField = function(client, name, type, viewName, fieldDef, module, model, context, loadFromModule) {
        var loadJsFile = true;
        //Handle a params object instead of a huge list of params
        if (_.isObject(client)) {
            name = client.name;
            type = client.type;
            viewName = client.viewName;
            fieldDef = client.fieldDef;
            module = client.module;
            model = client.model;
            context = client.context;
            loadFromModule = client.loadFromModule;
            loadJsFile = !_.isUndefined(client.loadJsFile) ? client.loadJsFile : loadJsFile;
            client = client.client || "base";
        }

        if(loadJsFile) {
            if (loadFromModule) {
                test.loadComponent(client, "field", type, module);
            } else {
                test.loadComponent(client, "field", type);
            }
        }

        var view = new app.view.View({ name: viewName, context: context });
        var def = { name: name, type: type, events: (fieldDef) ? fieldDef.events : {} };
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module
            });
            context.prepare();
        }

        model = model || new app.data.createBean();

        if (fieldDef) {
            model.fields = model.fields || {};
            model.fields[name] = fieldDef;
        }

        var field = app.view.createField({
            def: def,
            view: view,
            context: context,
            model: model,
            module:module,
            platform: client
        });


        var _origDispose = field._dispose;
        field._dispose = function() {
            if(this.context) {
                SugarTest._events.context.push(this.context._events);
            }
            if(this.model) {
                SugarTest._events.model.push(this.model._events);
            }
            _origDispose.apply(this, arguments);
        };

        SugarTest.components.push(view);
        return field;
    };

    test.createView = function(client, module, viewName, meta, context, loadFromModule, layout, loadComponent) {
        if (_.isUndefined(loadComponent) || loadComponent)
        {
            if (loadFromModule) {
                test.loadComponent(client, "view", viewName, module);
            } else {
                test.loadComponent(client, "view", viewName, null);
            }
        }
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module
            });
            context.prepare();
        }

        var view = app.view.createView({
            type : viewName,
            context : context,
            module : module,
            meta : meta,
            layout: layout,
            platform: client
        });

        var _origDispose = view._dispose;
        view._dispose = function() {
            if(this.context) {
                SugarTest._events.context.push(this.context._events);
            }
            if(this.model) {
                SugarTest._events.model.push(this.model._events);
            }
            _origDispose.apply(this, arguments);
        };

        SugarTest.components.push(view);
        return view;
    };

    /**
     * Helper that loads and declares a custom Bean and a custom BeanCollection.
     *
     * @param {String} client The platform.
     * @param {String} module The custom Bean module.
     * @param {Boolean} [loadModel] Set to false to prevent an attempt to load
     * the model override.
     * @param {Boolean} [loadCollection] Set to false to prevent an attempt to
     * load the collection override.
     */
    test.declareData = function(client, module, loadModel, loadCollection) {
        loadModel = (loadModel !== false);
        loadCollection = (loadCollection !== false);

        if (loadModel) {
            test.loadComponent(client, 'data', 'model', module);
        }

        if (loadCollection) {
            test.loadComponent(client, 'data', 'collection', module);
        }

        SugarTest.datas.push(module);
    };

    test.createLayout = function(client, module, layoutName, meta, context, loadFromModule, params) {
        if (loadFromModule) {
            test.loadComponent(client, "layout", layoutName, module);
        } else {
            test.loadComponent(client, "layout", layoutName);
        }
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module,
                layout: layoutName
            });
            context.prepare();
        }

        var layout = app.view.createLayout(_.extend({
            type: layoutName,
            context: context,
            module: module,
            meta: meta,
            platform: client
        }, params));

        //FIXME: SC-3880 Execution of this line should be contingent on params passed
        layout.initComponents();

        var _origDispose = layout._dispose;
        layout._dispose = function() {
            if(this.context) {
                SugarTest._events.context.push(this.context._events);
            }
            if(this.model) {
                SugarTest._events.model.push(this.model._events);
            }
            _origDispose.apply(this, arguments);
        };
        SugarTest.components.push(layout);
        return layout;
    };

    test.loadFile = function(path, file, ext, parseData, dataType) {
        dataType = dataType || 'text';

        var fileContent = null,
            url = path + "/" + file + "." + ext;

        $.ajax({
            async:    false, // must be synchronous to guarantee that a test doesn't run before the fixture is loaded
            cache:    false,
            dataType: dataType,
            url: url,
            success:  function(data) {
                fileContent = parseData(data);
            },
            error: function(error, status, errThrown) {
                console.log(status, errThrown);
                console.log('Failed to load: ' + url);
            }
        });

        return fileContent;
    };

    test.loadFixture = function(file, fixturePath) {
        return test.loadFile(fixturePath || "./fixtures", file, "json", function(data) { return data; }, "json");
    };

    test.testMetadata = {
        _data: null,

        init: function() {
            this._data = $.extend(true, {}, fixtures.metadata);
            this._data.layouts = this._data.layouts || {};
            this._data.views = this._data.views || {};
            this._data.fields = this._data.fields || {};

            // Lang strings are now retrieved in a separate GET, so we need to augment
            // our metadata fake with them here before calling setting metadata.set.
            if (!this.labelsFixture && this._data.labels) {
                this.labelsFixture = SugarTest.loadFixture('labels');
                this._data = $.extend(this._data, this.labelsFixture);
            }
        },

        addTemplate: function(name, type, template, templateName, module) {
            type = type + 's';
            if (this.isInitialized()) {
                if (module) {
                    type = (type === 'fields') ? 'fieldTemplates' : type;
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].templates[templateName] = template;
                } else {
                    this._data[type][name] = this._data[type][name] || {};
                    this._data[type][name].templates = this._data[type][name].templates || {};
                    this._data[type][name].templates[templateName] = template;
                }
            }
        },

        updateModuleMetadata: function(module, moduleDef) {
            if (this.isInitialized()) {
                this._data.modules[module] = _.extend((this._data.modules[module] || {}), (moduleDef || {}));
            }
        },

        addViewDefinition: function(name, viewDef, module) {
            this._addDefinition(name, 'views', viewDef, module);
        },

        addLayoutDefinition: function(name, layoutDef, module) {
            this._addDefinition(name, 'layouts', layoutDef, module);
        },

        _initModuleStructure: function(module, type, name) {
            this._data.modules[module] = this._data.modules[module] || {};
            this._data.modules[module][type] = this._data.modules[module][type] || {};
            this._data.modules[module][type][name] = this._data.modules[module][type][name] || {};
            this._data.modules[module][type][name].templates = this._data.modules[module][type][name].templates || {};
        },

        _addDefinition: function(name, type, def, module) {
            if (this.isInitialized()) {
                if (module) {
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].meta = def;
                } else {
                    this._data[type][name] = this._data[type][name] || {};
                    this._data[type][name].meta = def;
                }
            }
        },

        set: function() {
            if (this.isInitialized()) {
                this._data._hash = true; //force ignore cache
                _.each(this._data.modules, function(module) {
                    module._patched = false;
                });
                SugarTest.app.metadata.set(this._data, true, true);
            }
        },

        revert: function() {
            if (this.isInitialized()) {
                SugarTest.app.metadata.set(fixtures.metadata, true, true);
            }
        },

        dispose: function() {
            this.revert();
            this._data = null;
            this.labelsFixture = null;
        },

        isInitialized: function() {
            if (this._data) {
                return true;
            } else {
                return false;
            }
        },

        get: function() {
            return this._data;
        }
    };

}(SugarTest));
