# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules @reports-group
Feature: Reports module verification

  Background:
    Given I use default account
    Given I launch App with config: "skipTutorial"

  @list-method
  Scenario: Reports > List View
    Given Reports records exist:
      | *name     | module    |
      | Report_A  | Accounts  |
    Given I open about view and login
    When I choose Reports in modules menu
    Then I should see *Report_A in #ReportsList.ListView
    Then I verify fields for *Report_A in #ReportsList.ListView
      | fieldName    | value    |
      | name         | Report_A |
    When I click Create button on #ReportsList header
    Then I should be redirected to "bwc/index.php?module=Reports&report_module=&action=index&page=report&Create_Custom_Report=Create+Custom+Report&bwcRedirect=1" route

  @list-preview @list-preview-description
  Scenario: Reports > List View > Preview > Check fields
    Given Reports records exist:
      | *name    | description |
      | Report_B | abc         |
    Given I open about view and login
    When I choose Reports in modules menu
    When I click on preview button on *Report_B in #ReportsList.ListView
    Then I should see #Report_BPreview view
    Then I verify fields on #Report_BPreview.PreviewView
      | fieldName   | value |
      | description | abc   |
