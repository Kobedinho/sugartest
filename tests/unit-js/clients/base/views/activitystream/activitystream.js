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
describe("Activity Stream View", function() {
    var app,
        view,
        viewName = 'activitystream',
        preferenceStub;

    beforeEach(function() {
        app = SugarTest.app;

        sinon.collection.stub(SugarTest.app.data, 'createRelatedCollection', function() {
            return new Backbone.Collection();
        });
        preferenceStub = sinon.collection.stub(SugarTest.app.user, 'getPreference');
        preferenceStub.withArgs('datepref').returns('m/d/Y');
        preferenceStub.withArgs('timepref').returns('H:i');

        SugarTest.testMetadata.init();

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'videoEmbed');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'activitystream');

        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'float');
        SugarTest.loadComponent('base', 'view', viewName);

        SugarTest.testMetadata.set();

        var context = SugarTest.app.context.getContext();
        context.set({
            module: 'Cases'
        });
        context.prepare();
        context.get('model').set({
            id: "edf88cef-1be4-9bcc-4cbc-51caf35c5bb1",
            activity_type: "post",
            display_parent_type: 'Contacts',
            data: {
                embeds: [{
                    type: "video",
                    html: "<iframe width='200px' height='100px'></iframe>",
                    width: 200,
                    height: 100
                }]
            }
        });

        view = SugarTest.createView('base', 'Cases', viewName, null, context);

        sinon.collection.stub(view, 'processAvatars');
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe("processEmbed()", function() {
        it('Should load videoEmbed template when the type is video', function() {
            var appTemplateGetStub = sinon.stub(SugarTest.app.template, 'get');

            view.model.set('data', {
                embeds: [{
                    type: 'video'
                }]
            });
            view.processEmbed();

            expect(appTemplateGetStub.calledWith('activitystream.videoEmbed')).toBe(true);
            appTemplateGetStub.restore();
        });

        it('Should load videoMovieEmbed template when the type is video.movie', function() {
            var appTemplateGetStub = sinon.stub(SugarTest.app.template, 'get');

            view.model.set('data', {
                embeds: [{
                    type: 'video.movie'
                }]
            });
            view.processEmbed();

            expect(appTemplateGetStub.calledWith('activitystream.videoMovieEmbed')).toBe(true);
            appTemplateGetStub.restore();
        });
    });

    describe("resizeVideo()", function() {
        it('Should resize to fit the activity stream container width', function() {
            var widthStub = sinon.stub($.fn, 'width', function() {
                return 300;
            });

            view.render();

            expect(view.$('.embed iframe').prop('width')).toBe('300');
            widthStub.restore();
        });

        it('Should resize with 480px width when the activity stream container width is more than 480px', function() {
            var widthStub = sinon.stub($.fn, 'width', function() {
                return 481;
            });

            view.render();

            expect(view.$('.embed iframe').prop('width')).toBe('480');
            widthStub.restore();
        });

        it('Should resize height so that it keeps its proportion', function() {
            var widthStub = sinon.stub($.fn, 'width', function() {
                return 400;
            });

            view.render();

            expect(view.$('.embed iframe').prop('height')).toBe('200');
            widthStub.restore();
        });
    });

    describe("getPreviewData", function(){
        var previewData,
            aclStub,
            getParentModelStub;

        it('Should load preview enabled with default message', function() {
            var previewId = "3be4-2be4-49bcc-dcbc-cccaf35c5bb1";

            view.model.set({
                display_parent_id: "5asdfgg-2be4-49bcc-dcbc-cccaf35c5bb1",
                display_parent_type: "Contacts"
            });

            getParentModelStub = sinon.stub(view, '_getParentModel').returns({id: previewId});

            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeTruthy();
            expect(previewData.label).toBe('LBL_PREVIEW');

            getParentModelStub.restore();
        });

        it('Should return preview disabled with correct message when no user has no read access to related record', function() {
            aclStub = sinon.stub(SugarTest.app.acl,'hasAccess', function(){
                return false;
            });

            view.model.set({
                display_parent_id: "5asdfgg-2be4-49bcc-dcbc-cccaf35c5bb1",
                display_parent_type: "Contacts"
            });

            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeFalsy();
            expect(previewData.label).toBe('LBL_PREVIEW_DISABLED_NO_ACCESS');

            aclStub.restore();
        });

        it('Should return preview disabled with correct message when related record is same as context model', function() {
            aclStub = sinon.stub(SugarTest.app.acl,'hasAccess', function(){
                return true;
            });

            var moduleId = "3be4-2be4-49bcc-dcbc-cccaf35c5bb1";

            view.model.set({
                display_parent_id: moduleId,
                display_parent_type: "Contacts"
            });

            getParentModelStub = sinon.stub(view, '_getParentModel').returns({id: moduleId, module: "Contacts"});

            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeFalsy();
            expect(previewData.label).toBe('LBL_PREVIEW_DISABLED_SAME_RECORD');

            aclStub.restore();
            getParentModelStub.restore();
        });

        it('Should return preview disabled with correct message when no related record', function() {
            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeFalsy();
            expect(previewData.label).toBe('LBL_PREVIEW_DISABLED_NO_RECORD');
        });

        it('Should return preview disabled with correct message when attachment', function() {
            view.model.set({
                activity_type: "attach"
            });

            view.model.unset("data");

            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeFalsy();
            expect(previewData.label).toBe('LBL_PREVIEW_DISABLED_ATTACHMENT');
        });
    });

    describe("formatAllTagsAndLinks()", function() {
        it('Should format text-based tags in activity post into HTML format', function() {
            view.model.set('data', {
                value: 'foo @[Accounts:1234-1234:foo bar] bar'
            });

            view.formatAllTagsAndLinks();

            expect(view.model.get('data').value).toBe('foo <span class="label label-Accounts sugar_tag"><a href="#Accounts/1234-1234">foo bar</a></span> bar');
        });

        it('Should format text-based tags in comments into HTML format', function() {
            view.commentsCollection.add({
                data: {
                    value: 'foo @[Accounts:1234-1234:foo bar] bar'
                }
            });

            view.formatAllTagsAndLinks();

            expect(view.commentsCollection.at(0).get('data').value).toBe('foo <span class="label label-Accounts sugar_tag"><a href="#Accounts/1234-1234">foo bar</a></span> bar');
        });

        it('Should convert URLs into links in posts', function() {
            view.model.set('data', {
                value: 'www.test.com'
            });

            view.formatAllTagsAndLinks();

            expect(view.model.get('data').value).toBe('<a href="http://www.test.com" target="_blank">www.test.com</a>');
        });

        it('Should convert URLs into links in comments', function() {
            view.commentsCollection.add({
                data: {
                    value: 'www.test.com'
                }
            });

            view.formatAllTagsAndLinks();

            expect(view.commentsCollection.at(0).get('data').value).toBe('<a href="http://www.test.com" target="_blank">www.test.com</a>');
        });

        it('Should convert URLs into links even if it is called twice', function() {
            view.model.set('data', {
                value: 'www.test.com'
            });

            view.formatAllTagsAndLinks();
            view.formatAllTagsAndLinks();

            expect(view.model.get('data').value).toBe('<a href="http://www.test.com" target="_blank">www.test.com</a>');
        });
    });

    describe("formatLinks()", function() {
        it('Should convert URLs with http and https protocols into links', function() {
            var input = 'foo http://www.sugarcrm.com bar https://www.test.com',
                expected = 'foo <a href="http://www.sugarcrm.com" target="_blank">http://www.sugarcrm.com</a> bar <a href="https://www.test.com" target="_blank">https://www.test.com</a>',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should convert URLs into links when other protocols are specified', function() {
            var input = 'foo ftp://sugarcrm.com bar',
                expected = 'foo ftp://sugarcrm.com bar',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should convert URLs into links when no protocols are specified', function() {
            var input = 'foo www.sugarcrm.com bar',
                expected = 'foo <a href="http://www.sugarcrm.com" target="_blank">www.sugarcrm.com</a> bar',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should convert URLs into links when "www" is not specified', function() {
            var input = 'foo http://test.com bar',
                expected = 'foo <a href="http://test.com" target="_blank">http://test.com</a> bar',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should not convert URLs into links when the protocol and "www" are not specified', function() {
            var input = 'foo test.com bar',
                expected = 'foo test.com bar',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should convert URL with parameters into links', function() {
            var input = 'http://www.sugarcrm.com/1234/321?q=difa%20fdaf',
                expected = '<a href="http://www.sugarcrm.com/1234/321?q=difa%20fdaf" target="_blank">http://www.sugarcrm.com/1234/321?q=difa%20fdaf</a>',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should convert URL with port number into links', function() {
            var input = 'http://www.sugarcrm.com:8888/ent/sugarcrm/index.php',
                expected = '<a href="http://www.sugarcrm.com:8888/ent/sugarcrm/index.php" target="_blank">http://www.sugarcrm.com:8888/ent/sugarcrm/index.php</a>',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should not convert tags into links', function() {
            var input = '@[Contacts:1234-1234:foo bar]',
                expected = '@[Contacts:1234-1234:foo bar]',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });
    });

    describe("processUpdateActivityTypeMessage()", function () {
        it('Fields should be disposed when the view is disposed', function () {
            var data = {
                "name": {
                    "field_name": "name",
                    "data_type": "name",
                    "before": "Calm Sailing Inc",
                    "after": "Calm Flying"
                },
                "case_number": {
                    "field_name": "case_number",
                    "data_type": "float",
                    "before": "1234567",
                    "after": "09909099"
                }
            };

            view.model.set('parent_type', 'Cases');
            view.processUpdateActivityTypeMessage(data);

            expect(_.size(view.fields)).toBe(2);
            view.dispose();

            expect(_.size(view.fields)).toBe(0);
        });

        it('Before and After values injected in update string properly using Field format method', function () {
            var results, data, langStub;

            preferenceStub.withArgs('decimal_separator').returns('.');

            langStub = sinon.collection.stub(SugarTest.app.lang, 'get');
            langStub.withArgs('TPL_ACTIVITY_UPDATE_FIELD', 'Activities').returns('{{before}}:{{after}}');

            view.model.set('parent_type', 'Cases');
            data = {
                "name": {
                    "field_name": "name",
                    "data_type": "name",
                    "before": "Calm Sailing Inc",
                    "after": "Calm Flying"
                },
                "case_number": {
                    "field_name": "case_number",
                    "data_type": "float",
                    "before": "200",
                    "after": "100"
                }
            };

            results = view.processUpdateActivityTypeMessage(data);

            expect(results).toContain("200.0000:100.0000");
            expect(results).toContain("Calm Sailing Inc:Calm Flying");
        });
    });

    describe('getAvatarUrlForUser', function() {
        var cacheBefore, user, userId, fetchUserPictureStub;

        beforeEach(function() {
            cacheBefore = app.cache;
            app.cache = {
                get: function(key) {
                    return this[key];
                },
                set: function(key, value) {
                    this[key] = value;
                }
            }

            userId = '123';
            user = new Backbone.Model({created_by: userId});

            fetchUserPictureStub = sinon.stub(view, 'fetchUserPicture');
        });

        afterEach(function() {
            app.cache = cacheBefore;
            fetchUserPictureStub.restore();
        });

        it('Should return picture url if user has a picture', function() {
            var result;
            view.setUserPictureStatus(userId, true);
            result = view.getAvatarUrlForUser(user, 'activities');
            expect(result).toContain(userId + '/file/picture');
            expect(fetchUserPictureStub.callCount).toEqual(0);
        });

        it('Should return empty string if user has no picture', function() {
            var result;
            view.setUserPictureStatus(userId, false);
            result = view.getAvatarUrlForUser(user, 'activities');
            expect(result).toEqual('');
            expect(fetchUserPictureStub.callCount).toEqual(0);
        });

        it('Should return empty string if picture check has not been performed yet', function() {
            var result;
            view.setUserPictureStatus(userId, undefined);
            result = view.getAvatarUrlForUser(user, 'activities');
            expect(result).toEqual('');
            expect(fetchUserPictureStub.callCount).toEqual(1);
        });

        it('Should return empty string picture check result is expired', function() {
            var result;
            view.expiryTime = -(view.expiryTime); //force expire in the past
            view.setUserPictureStatus(userId, true);
            result = view.getAvatarUrlForUser(user, 'activities');
            expect(result).toEqual('');
            expect(fetchUserPictureStub.callCount).toEqual(1);
            view.expiryTime = -(view.expiryTime); //return expiry time positive
        });
    });

    describe('_addBrokenImageHandler', function() {
        it('Should remove broken image and unwrap link to broken image on error', function() {
            var attachActivityHtml = '<a href="" data-note-id="123">Broken Image Link</a>' +
                '<div class="embed"><div><img src="" data-note-id="123"/></div></div>';
            view.$el.append(attachActivityHtml);
            view._addBrokenImageHandler();

            //expect link and image to be in the dom
            expect(view.$('a[data-note-id]').length).toBe(1);
            expect(view.$(view._attachImageSelector).length).toBe(1);

            //image is broken
            view.$(view._attachImageSelector).trigger('error');

            //expect link and image to be removed from the dom
            expect(view.$('a[data-note-id]').length).toBe(0);
            expect(view.$(view._attachImageSelector).length).toBe(0);
        });
    });

    describe('_setRelativeTimeAvailable', function() {
        it('Should show relative time if date created before threshold', function() {
            var now = app.date();
            view.model.set('date_entered', now.format());
            view._setRelativeTimeAvailable();

            expect(view.useRelativeTime).toBeTruthy();
        });

        it('Should show date and time format when activity created past threshold', function() {
            var date = app.date().subtract(7, 'days');
            view.model.set('date_entered', date.format());
            view._setRelativeTimeAvailable();

            expect(view.useRelativeTime).toBeFalsy();
        });
    });
});
