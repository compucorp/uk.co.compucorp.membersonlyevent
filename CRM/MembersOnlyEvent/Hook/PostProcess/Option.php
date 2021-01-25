<?php

use CRM_MembersOnlyEvent_Hook_PostProcess_BaseField as BaseField;
use CRM_MembersOnlyEvent_BAO_EntityPriceFieldValue as EntityPriceFieldValue;

/**
 * Class for Price Field Option Form PostProcess Hook
 */
class CRM_MembersOnlyEvent_Hook_PostProcess_Option extends BaseField {

  /**
   * Checks if the hook should be handled.
   *
   * @param $formName
   * @param $form
   *
   * @return bool
   */
  protected function shouldHandle($formName, $form) {
    if ($formName === CRM_Price_Form_Option::class) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param $formName
   * @param $form
   */
  protected function postProcess($formName, &$form) {
    $id = $form->getVar('_oid');
    if (empty($id)) {
      $id = $this->findOptionIDByValues($form->_submitValues);
    }

    switch (CRM_Utils_Array::value('entity_table', $form->_submitValues)) {
      case 'Membership':
        $entityIDs = explode(',', $form->_submitValues['membership_types']);
        EntityPriceFieldValue::updateEntityPriceFieldValues($id, 'MembershipType', $entityIDs);
        break;

      case 'Participant':
        $entityIDs = explode(',', $form->_submitValues['events']);
        EntityPriceFieldValue::updateEntityPriceFieldValues($id, 'Event', $entityIDs);
        break;

      default:
        EntityPriceFieldValue::removeEntityPriceFieldValues($id);
        break;
    }
  }

}
