<?php

use CRM_MembersOnlyEvent_Hook_PostProcess_BaseField as BaseField;
use CRM_MembersOnlyEvent_BAO_EntityPriceFieldValue as EntityPriceFieldValue;

/**
 * Class for Price Field Form PostProcess Hook
 */
class CRM_MembersOnlyEvent_Hook_PostProcess_Field extends BaseField {

  /**
   * Checks if the hook should be handled.
   *
   * @param $formName
   * @param $form
   *
   * @return bool
   */
  protected function shouldHandle($formName, $form) {
    if ($formName === CRM_Price_Form_Field::class
      && $form->getAction() === CRM_Core_Action::ADD
      && !empty($form->_submitValues['entity_table'])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param $formName
   * @param $form
   */
  protected function postProcess($formName, &$form) {
    // The arrays of values for the multiple options that were created.
    $optionMultiFields = [
      'option_label',
      'option_amount',
      'option_financial_type_id',
      'option_count',
      'option_max_value',
      'option_weight',
    ];

    // Walk through options and only deal with additional signup options.
    foreach ($form->_submitValues['entity_table'] as $price_option_key => $price_option_othersignup) {
      switch ($price_option_othersignup) {
        case 'Membership':
          $entityIDs = $form->_submitValues['membership_types'][$price_option_key];
          $entityTable = 'MembershipType';
          break;

        case 'Participant':
          $entityIDs = $form->_submitValues['events'][$price_option_key];
          $entityTable = 'Event';
          break;

        default:
          continue 2;
      }

      $entityIDs = explode(',', $entityIDs);

      // Assemble information from the option fields.
      $vals = [];
      foreach ($optionMultiFields as $f) {
        if (!empty($form->_submitValues[$f][$price_option_key])) {
          $fShort = substr($f, 7);
          $vals[$fShort] = $form->_submitValues[$f][$price_option_key];
        }
      }
      if (empty($vals)) {
        continue;
      }
      else {
        $vals['fieldId'] = $this->findFieldIDByValues($form->_submitValues);
      }
      $priceOptionId = $this->findOptionIDByValues($vals);

      EntityPriceFieldValue::updateEntityPriceFieldValues($priceOptionId, $entityTable, $entityIDs);

    }
  }

}
