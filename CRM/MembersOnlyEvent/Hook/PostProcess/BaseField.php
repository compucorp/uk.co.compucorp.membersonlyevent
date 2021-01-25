<?php


/**
 * Abstract class for PostProcess Hook
 */
abstract class CRM_MembersOnlyEvent_Hook_PostProcess_BaseField {

  /**
   * Handle the hook
   *
   * @param string $formName
   * @param object $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName, $form)) {
      return;
    }

    $this->postProcess($formName, $form);
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param $formName
   * @param $form
   */
  abstract protected function shouldHandle($formName, $form);

  /**
   * @param $formName
   * @param $form
   */
  abstract protected function postProcess($formName, &$form);

  /**
   * Look up a price option by its values.
   *
   * @param array $values
   *
   * @return int
   */
  protected function findOptionIDByValues($values) {
    $fields = [
      'fieldId' => 'price_field_id',
      'label' => 'label',
      'amount' => 'amount',
      'financial_type_id' => 'financial_type_id',
      'count' => 'count',
      'max_value' => 'max_value',
      'weight' => 'weight',
    ];

    $searchParams = [
      'return' => 'id',
      'options' => [
        // In case there are two identical values, pull the newest
        'sort' => "id DESC",
        'limit' => 1,
      ],
    ];

    foreach ($fields as $val => $field) {
      if (!empty($values[$val])) {
        $searchParams[$field] = $values[$val];
      }
    }

    try {
      return civicrm_api3('PriceFieldValue', 'getvalue', $searchParams);
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_var('Failed to find price option/field just created', $e);
    }
  }

  /**
   * Look up a price field by its values.
   *
   * @param array $values
   *
   * @return int
   */
  protected function findFieldIDByValues($values) {
    $fields = [
      'sid' => 'price_set_id',
      'label' => 'label',
      'html_type' => 'html_type',
      'is_display_amounts' => 'is_display_amounts',
    ];

    $searchParams = [
      'return' => 'id',
      'options' => [
        // In case there are two identical values, pull the newest
        'sort' => "id DESC",
        'limit' => 1,
      ],
    ];

    foreach ($fields as $val => $field) {
      if (!empty($values[$val])) {
        $searchParams[$field] = $values[$val];
      }
    }

    try {
      return civicrm_api3('PriceField', 'getvalue', $searchParams);
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_var('Failed to find price option/field just created', $e);
    }
  }

}
