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
describe('Forecasts.Base.Views.ListHeaderpane', function() {
    var view,
        layout,
        context,
        moduleName = 'Forecasts';

    beforeEach(function() {
        context = SugarTest.app.context.getContext();
        layout = {
            context: context,
            on: function() {},
            off: function() {}
        };
        view = SugarTest.createView('base', moduleName, 'list-headerpane', null, null, true, layout, true);
        view.saveDraftBtnField = {
            setDisabled: function(disabled) {
                return disabled;
            }
        };
        view.commitBtnField = {
            setDisabled: function(disabled) {
                return disabled;
            },
            $: function() {
                return {
                    tooltip: function() {}
                }
            }
        };
    });

    afterEach(function() {
        view.saveDraftBtnField = null;
        view.commitBtnField = null;
        view.dispose();
        view = null;
        sinon.collection.restore();
    });

    describe('setButtonStates()', function() {
        beforeEach(function() {
            sinon.collection.spy(view.saveDraftBtnField, 'setDisabled');
            sinon.collection.spy(view.commitBtnField, 'setDisabled');
            view.forecastSyncComplete = false;
            view.fieldHasErrorState = false;
            view.saveBtnDisabled = false;
            view.commitBtnDisabled = false;
        });

        it('should disable if forecastSyncComplete is true and fieldHasErrorState is true', function() {
            view.forecastSyncComplete = true;
            view.fieldHasErrorState = true;
            view.setButtonStates();
            expect(view.saveDraftBtnField.setDisabled).toHaveBeenCalledWith(true);
            expect(view.commitBtnField.setDisabled).toHaveBeenCalledWith(true);
        });

        it('should disable if forecastSyncComplete is false', function() {
            view.setButtonStates();
            expect(view.saveDraftBtnField.setDisabled).toHaveBeenCalledWith(true);
            expect(view.commitBtnField.setDisabled).toHaveBeenCalledWith(true);
        });

        it('should set disabled state if forecastSyncComplete is true and fieldHasErrorState is false', function() {
            view.forecastSyncComplete = true;
            view.saveBtnDisabled = true;
            view.commitBtnDisabled = false;
            view.setButtonStates();
            expect(view.saveDraftBtnField.setDisabled).toHaveBeenCalledWith(true);
            expect(view.commitBtnField.setDisabled).toHaveBeenCalledWith(false);
        });

        it('should set disabled state if forecastSyncComplete is true and fieldHasErrorState is false', function() {
            view.forecastSyncComplete = true;
            view.saveBtnDisabled = false;
            view.commitBtnDisabled = true;
            view.setButtonStates();
            expect(view.saveDraftBtnField.setDisabled).toHaveBeenCalledWith(false);
            expect(view.commitBtnField.setDisabled).toHaveBeenCalledWith(true);
        });
    });
});
