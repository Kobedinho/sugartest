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

describe("Sugar7 utils", function() {
    var app;
    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) { eval(d) });
    });

    afterEach(function() {

    });

    describe('hideForecastCommitStageField()', function() {
        var options;
        beforeEach(function() {
            options = {
                panels: [
                    {
                        fields: [
                            {
                                name: 'commit_stage',
                                label: 'LBL_COMMIT_STAGE'
                            }
                        ]
                    }
                ]
            };
        });

        afterEach(function() {
            options = undefined;
        });
        it('should replace commit_stage with a spacer', function() {
            sinon.stub(app.metadata, 'getModule', function() {
                return {
                    is_setup: false
                };
            });
            app.utils.hideForecastCommitStageField(options.panels);
            expect(options.panels[0].fields[0]).toEqual(
                {name: 'spacer', label: 'LBL_COMMIT_STAGE', span: 6, readonly: true}
            );
            app.metadata.getModule.restore();
        });
    });

    describe("getSubpanelCollection()", function() {
        it("should return the proper subpanel collection", function() {
            var ctx = {};
            ctx.children = [];

            var mdl = new Backbone.Model(),
                targetMdl = new Backbone.Model();

            targetMdl.set({id: 'targetMdl'});
            mdl.set({module: 'Test'});

            var col = new Backbone.Collection();
            col.add(targetMdl);

            mdl.set({collection: col});
            ctx.children.push(mdl);

            var targetCol = app.utils.getSubpanelCollection(ctx, 'Test');

            expect(targetCol.models[0].get('id')).toEqual('targetMdl');
        });
    });
    
    describe('Handling iframe URLs', function() {
		
    	it('Add frame mark to URL', function() {
    		var withMark = app.utils.addIframeMark('/sugar7/index.php?module=Administration&action=Home'); 
    		expect(withMark).toBe('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		withMark = app.utils.addIframeMark('/sugar7/index.php'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    		withMark = app.utils.addIframeMark('/sugar7/index.php?bwcFrame=1'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    	});
    	
    	it('Remove frame mark from URL', function() {
    		var noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&bwcFrame=1&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php');
    		expect(noMark).toBe('/sugar7/index.php'); 
    	});
    });

    describe('getRecordName', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model();
        });
        it('should get document_name for Documents module', function() {
            model.module = 'Documents';
            model.set({
                document_name: 'Awesome Document',
                name: 'document.zip'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Document');
        });
        it('get full_name when available', function() {
            model.module = 'Contacts';
            model.set({
                full_name: 'Awesome Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Name');
        });
        it('build full name based on first name and last name', function() {
            model.module = 'Users';
            model.set({
                first_name: 'Awesome',
                last_name: 'Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Name');
        });
        it('get name otherwise', function() {
            model.module = 'Leads';
            model.set({
                name: 'Simple Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Simple Name');
        });

        it('should return the last name', function() {
            model.module = 'Contacts';
            model.set('last_name', 'Name');
            expect(app.utils.getRecordName(model)).toEqual('Name');
        });
    });

    describe('email addresses', function() {
        var combos,
            model;

        combos = {
            primary_valid: {
                email_address: 'primary@valid.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            },
            primary_invalid: {
                email_address: 'primary@invalid.com',
                primary_address: true,
                invalid_email: true,
                opt_out: false
            },
            primary_opted_out: {
                email_address: 'primary@optout.com',
                primary_address: true,
                invalid_email: false,
                opt_out: true
            },
            primary_bad: {
                email_address: 'primary@bad.com',
                primary_address: true,
                invalid_email: true,
                opt_out: true
            },
            valid: {
                email_address: 'is@valid.com',
                primary_address: false,
                invalid_email: false,
                opt_out: false
            },
            invalid: {
                email_address: 'is@invalid.com',
                primary_address: false,
                invalid_email: true,
                opt_out: false
            },
            opted_out: {
                email_address: 'is@optout.com',
                primary_address: false,
                invalid_email: false,
                opt_out: true
            },
            bad: {
                email_address: 'is@bad.com',
                primary_address: false,
                invalid_email: true,
                opt_out: true
            }
        };

        beforeEach(function() {
            model = new Backbone.Model();
        });

        using('getEmailAddress', [
            [
                [combos.primary_valid, combos.valid],
                {primary_address: true},
                combos.primary_valid.email_address
            ],
            [
                [combos.valid, combos.primary_valid],
                undefined,
                combos.valid.email_address
            ],
            [
                [combos.primary_invalid, combos.invalid],
                {invalid_email: true},
                combos.primary_invalid.email_address
            ],
            [
                [combos.primary_valid, combos.valid],
                {invalid_email: true},
                ''
            ],
            [
                [combos.primary_valid, combos.valid],
                {opt_out: true},
                ''
            ],
            [
                [combos.valid, combos.invalid],
                {invalid_email: true},
                combos.invalid.email_address
            ],
            [
                [combos.valid, combos.opted_out],
                {opt_out: true},
                combos.opted_out.email_address
            ],
            [
                [combos.valid, combos.invalid, combos.opted_out, combos.bad],
                {invalid_email: true, opt_out: true},
                combos.bad.email_address
            ],
            [
                [combos.bad, combos.valid, combos.invalid, combos.primary_bad, combos.opted_out],
                {primary_address: true, invalid_email: true, opt_out: true},
                combos.primary_bad.email_address
            ]
        ], function(emails, options, expected) {
            it('should return ' + expected, function() {
                model.set('email', emails);
                expect(app.utils.getEmailAddress(model, options)).toEqual(expected);
            });
        });

        using('getPrimaryEmailAddress', [
            [[combos.primary_valid, combos.valid], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.invalid], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.opted_out], combos.primary_valid.email_address],
            [[combos.primary_valid, combos.bad], combos.primary_valid.email_address],
            [[combos.primary_invalid, combos.valid], combos.valid.email_address],
            [[combos.primary_invalid, combos.invalid], ''],
            [[combos.primary_invalid, combos.opted_out], combos.opted_out.email_address],
            [[combos.primary_invalid, combos.bad], ''],
            [[combos.primary_opted_out, combos.valid], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.invalid], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.opted_out], combos.primary_opted_out.email_address],
            [[combos.primary_opted_out, combos.bad], combos.primary_opted_out.email_address],
            [[combos.primary_bad, combos.valid], combos.valid.email_address],
            [[combos.primary_bad, combos.invalid], ''],
            [[combos.primary_bad, combos.opted_out], combos.opted_out.email_address],
            [[combos.primary_bad, combos.bad], ''],
            [[combos.valid, combos.invalid], combos.valid.email_address],
            [[combos.valid, combos.opted_out], combos.valid.email_address],
            [[combos.valid, combos.bad], combos.valid.email_address],
            [[combos.invalid, combos.opted_out], combos.opted_out.email_address],
            [[combos.invalid, combos.bad], ''],
            [[combos.opted_out, combos.bad], combos.opted_out.email_address]
        ], function(emails, expected) {
            it('should return ' + expected, function() {
                model.set('email', emails);
                expect(app.utils.getPrimaryEmailAddress(model)).toEqual(expected);
            });
        });
    });

    var name = 'module';
    using('query strings',
        [
            ['?module=asdf', 'asdf'],
            ['?asdf=asdf&module=asdf&module=zxcv', 'zxcv'],
            ['?asdf=asdf&module=zxcv&modtrwer=zxcv', 'zxcv'],
            ['?xcvb=asdf&asdf=asdf&ryuit=zxcv', '']
        ],
        function (value, result) {
            it('should be able to get parameters', function () {
                var testResult = app.utils.getWindowLocationParameterByName(name, value);
                expect(result).toEqual(testResult);
            });
        });

    describe('getSelectedUsersReportees', function() {
        describe('as manager', function() {
            var user;
            beforeEach(function() {
                user = {
                    is_manager: true,
                    id: 'test_id'
                };
            });

            afterEach(function() {
                sinon.collection.restore();
                delete user;
            });

            it('will make an xhr call with status equal to active', function() {
                var post_args = undefined;
                sinon.collection.stub(app.api, 'call', function(type, url, args) {
                    post_args = args;
                });
                app.utils.getSelectedUsersReportees(user, {});
                expect(app.api.call).toHaveBeenCalled();
                expect(post_args).not.toBeUndefined();
                expect(post_args.filter[0].status).toEqual('Active');
            });
        });
    });
});
