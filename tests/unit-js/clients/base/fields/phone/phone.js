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
describe("Base.Fields.Phone", function() {
    var field;

    beforeEach(function(){
        field = SugarTest.createField("base", "phone", "phone", "detail", {});
    });

    afterEach(function() {
        field = null;
    });
    it("should figure out if skype is enabled", function() {
        var metamock = sinon.stub(SugarTest.app.metadata,'getServerInfo', function(){
            return {
              "system_skypeout_on": true
            };
        });
        field.initialize(field.options);

        expect(field.skypeEnabled).toBeTruthy();
        metamock.restore();
        var metamock = sinon.stub(SugarTest.app.metadata,'getServerInfo', function(){
            return {
                "system_skypeout_on": false
            };
        });
        field.initialize(field.options);

        expect(field.skypeEnabled).toBeFalsy();
        metamock.restore();
    });
    it("should format values if theyre in the correct format", function() {
        var data = {
            '+0013443asdf':'+0013443',
            '001(234)43asdf':'001(234)43',
            '011(234)43asdf':'011(234)43'

        }
        var formatted
        field.skypeEnabled = true;
        field.action = 'detail';
        _.each(data, function(value, key){
            formatted = field.format(key);
            expect(formatted).toEqual(key);
            expect(field.skypeValue).toEqual(value);
        });
    });

    it("should checks if value should be skype formatted + 00 or 011 leading is necessary", function() {
        var data = {
            '00123123312': true,
            '0113434343': true,
            '+00123123312': true,
            '+0113434343': true,
            '+2343543532': true,
            '27000345435': false
            },
            formatted;

        _.each(data, function(value, key){
            formatted = field.isSkypeFormatted(key);
            expect(formatted).toEqual(value);
        });
    });

    it("should format phone to skype phone format", function() {
        var data = {
                '00123123312': '00123123312',
                '01123123312': '01123123312',
                '+00123123312': '+00123123312',

                '1-345-3423432': '+1-345-3423432',
                '1(345)3423432': '+1(345)3423432',
                '1.345.3423432': '+1.345.3423432',
                '1 345 3423432': '+1 345 3423432',
                '1/345/3423432': '+1/345/3423432',

                '1-3453423432': '+13453423432',
                '1(3453423432': '+13453423432',
                '1345)3423432': '+13453423432',
                '134.53423432': '+13453423432',
                '1345 3423432': '+13453423432',
                '134/53423432': '+13453423432',

                '1-34(5-34)23432': '+1-345-3423432',
                '1(345)342.3432': '+1(345)3423432',
                '1.345.34-2 3432': '+1.345.3423432',
                '1 3-45 34)23432': '+1 345 3423432',
                '1/345/3(42-3432': '+1/345/3423432'
            };
        _.each(data, function(value, key){
            expect(field.skypeFormat(key)).toEqual(value);
        });
    });
});
