<?php
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
// created: 2014-01-23 18:54:55
$viewdefs['ProductTemplates']['EditView'] = array (
  'templateMeta' =>
  array (
    'maxColumns' => '2',
    'widths' =>
    array (
      0 =>
      array (
        'label' => '10',
        'field' => '30',
      ),
      1 =>
      array (
        'label' => '10',
        'field' => '30',
      ),
    ),
    'useTabs' => false,
    'syncDetailEditViews' => true,
  ),
  'panels' =>
  array (
    'default' =>
    array (
      0 =>
      array (
        0 =>
        array (
          'name' => 'name',
          'label' => 'LBL_NAME',
          'displayParams' =>
          array (
            'required' => true,
          ),
        ),
        1 =>
        array (
          'name' => 'status',
          'label' => 'LBL_STATUS',
        ),
      ),
      1 =>
      array (
        0 =>
        array (
          'name' => 'category_name',
          'label' => 'LBL_CATEGORY_NAME',
        ),
      ),
      2 =>
      array (
        0 =>
        array (
          'name' => 'website',
          'label' => 'LBL_URL',
        ),
        1 =>
        array (
          'name' => 'date_available',
          'label' => 'LBL_DATE_AVAILABLE',
        ),
      ),
      3 =>
      array (
        0 =>
        array (
          'name' => 'tax_class',
          'label' => 'LBL_TAX_CLASS',
        ),
        1 =>
        array (
          'name' => 'qty_in_stock',
          'label' => 'LBL_QUANTITY',
        ),
      ),
      4 =>
      array (
        0 =>
        array (
          'name' => 'manufacturer_id',
          'label' => 'LBL_LIST_MANUFACTURER_ID',
        ),
        1 =>
        array (
          'name' => 'weight',
          'label' => 'LBL_WEIGHT',
        ),
      ),
      5 =>
      array (
        0 =>
        array (
          'name' => 'mft_part_num',
          'label' => 'LBL_MFT_PART_NUM',
        ),
      ),
      6 =>
      array (
        0 =>
        array (
          'name' => 'vendor_part_num',
          'label' => 'LBL_VENDOR_PART_NUM',
        ),
        1 =>
        array (
          'name' => 'type_id',
          'label' => 'LBL_TYPE',
        ),
      ),
      7 =>
      array (
        0 =>
        array (
          'name' => 'currency_id',
          'label' => 'LBL_CURRENCY',
        ),
        1 =>
        array (
          'name' => 'support_name',
          'label' => 'LBL_SUPPORT_NAME',
        ),
      ),
      8 =>
      array (
        0 =>
        array (
          'name' => 'cost_price',
          'label' => 'LBL_COST_PRICE',
        ),
        1 =>
        array (
          'name' => 'support_contact',
          'label' => 'LBL_SUPPORT_CONTACT',
        ),
      ),
      9 =>
      array (
        0 =>
        array (
          'name' => 'list_price',
          'label' => 'LBL_LIST_PRICE',
        ),
        1 =>
        array (
          'name' => 'support_description',
          'label' => 'LBL_SUPPORT_DESCRIPTION',
        ),
      ),
      10 =>
      array (
        0 =>
        array (
          'name' => 'discount_price',
          'label' => 'LBL_DISCOUNT_PRICE',
        ),
        1 =>
        array (
          'name' => 'support_term',
          'label' => 'LBL_SUPPORT_TERM',
        ),
      ),
      11 =>
      array (
        0 =>
        array (
          'name' => 'pricing_formula',
          'label' => 'LBL_PRICING_FORMULA',
        ),
      ),
      12 =>
      array (
        0 =>
        array (
          'name' => 'description',
          'label' => 'LBL_DESCRIPTION',
        ),
      ),
      1 =>
      array (
        0 =>
        array (
          'name' => 'unit_test_c',
        ),
        1 =>
        array (
          'name' => 'id',
        ),
      ),
    ),
  ),
);
