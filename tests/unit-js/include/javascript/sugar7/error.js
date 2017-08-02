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
describe('Sugar7 error handler', function() {

    var app, origLayout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();

        origLayout = app.controller.layout;
        app.controller.layout = {error: {
            handleValidationError: function() { }
        }};
    });

    afterEach(function() {
        app.controller.layout = origLayout;
        sinon.collection.restore();
    });

    describe('422 Handle validation error', function() {
        it('should show an alert on error', function() {
            var alertStub = sinon.collection.stub(app.alert, 'show');

            app.error.handleValidationError({});
            expect(alertStub).toHaveBeenCalled();
        });

        it('should call the layout error handler if it exists', function() {
            var layoutStub = sinon.collection.stub(app.controller.layout.error, 'handleValidationError').returns(null);
            var alertStub = sinon.collection.stub(app.alert, 'show');
            app.error.handleValidationError({});
            expect(layoutStub).toHaveBeenCalled();
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
            layoutStub.restore();
        });

        it('should not show an alert if the layout handler returns false', function() {
            var layoutStub = sinon.collection.stub(app.controller.layout.error, 'handleValidationError').returns(false);
            var alertStub = sinon.collection.stub(app.alert, 'show');
            app.error.handleValidationError({});
            expect(layoutStub).toHaveBeenCalled();
            expect(alertStub).not.toHaveBeenCalled();
            alertStub.restore();
            layoutStub.restore();
        });

        it('should do nothing when passed a bean', function() {
            var alertStub = sinon.collection.stub(app.alert, 'show');
            var bean = new SugarTest.app.data.beanModel();
            app.error.handleValidationError(bean);
            expect(alertStub).not.toHaveBeenCalled();
            alertStub.restore();
        });
    });

    describe('400 invalid request error', function() {
        var errorPageStub;

        beforeEach(function() {
            errorPageStub = sinon.collection.stub(app.controller, 'loadView');
        });

        it('should show an error page on error', function() {
            app.error.handleUnspecified400Error({});
            expect(errorPageStub).toHaveBeenCalledWith({
                layout: 'error',
                errorType: '400',
                module: 'Error',
                create: true
            });
        });
    });

    describe('412 precondition failed error', function() {
        var syncStub;

        beforeEach(function() {
            syncStub = sinon.collection.stub(app, 'sync');
            app.isSynced = true;
        });

        it('should not sync if we have already started syncing', function() {
            app.isSynced = false;
            app.error.handleHeaderPreconditionFailed({});
            expect(syncStub).not.toHaveBeenCalled();
        });

        it('should only sync when metadata is out of date', function() {
            var error = null;

            app.error.handleHeaderPreconditionFailed(error);
            expect(syncStub).not.toHaveBeenCalled();

            error = {
                code: 'throwing 412 error for no reason'
            };

            app.error.handleHeaderPreconditionFailed(error);
            expect(syncStub).not.toHaveBeenCalled();
        });

        describe('infinite loop prevention', function() {

            beforeEach(function() {
                sinon.collection.stub(app.logger, 'fatal');
            });

            using('different error responses', [
                {
                    metaHash: false,
                    userHash: false,
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    loadingAfterSync: true,
                    shouldSync: false
                },
                {
                    metaHash: false,
                    userHash: 'oldUser',
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    loadingAfterSync: true,
                    shouldSync: false
                },
                {
                    metaHash: 'oldMeta',
                    userHash: false,
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'newUser',
                    loadingAfterSync: true,
                    shouldSync: false
                },
                {
                    metaHash: 'oldMeta',
                    userHash: 'oldUser',
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    loadingAfterSync: true,
                    shouldSync: false
                },
                {
                    metaHash: 'newMeta',
                    userHash: 'newUser',
                    oldMetaHash: 'oldMeta',
                    oldUserHash: 'oldUser',
                    loadingAfterSync: false,
                    shouldSync: true
                }
            ], function(options) {
                it('should only sync when we have a new metadata hash and a new user hash', function() {
                    sinon.collection.stub(app.metadata, 'getHash').returns(options.oldMetaHash);
                    sinon.collection.stub(app.router, 'refresh');
                    sinon.collection.stub(app.user, 'get').withArgs('_hash').returns(options.oldUserHash);

                    var error = {
                        code: 'metadata_out_of_date',
                        responseText: JSON.stringify({
                            metadata_hash: options.metaHash,
                            user_hash: options.userHash
                        }),
                        request: {
                            state: {
                                loadingAfterSync: options.loadingAfterSync
                            }
                        }
                    };
                    app.error.handleHeaderPreconditionFailed(error);
                    expect(syncStub.called).toBe(options.shouldSync);
                });
            });
        });
    });
});
