<?php

use CRM_MembersOnlyEvent_Hook_BuildForm_BaseField as BaseField;

/**
 * Class for Price Field Form BuildForm Hook
 */
class CRM_MembersOnlyEvent_Hook_BuildForm_Field extends BaseField {

  /**
   * Checks if the hook should be handled.
   *
   * @param $formName
   * @param $form
   *
   * @return bool
   */
  protected function shouldHandle($formName, &$form) {
    if ($formName === CRM_Price_Form_Field::class
      && $form->getAction() === CRM_Core_Action::ADD) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param $formName
   * @param CRM_Price_Form_Field $form
   *
   * @throws \CRM_Core_Exception
   */
  protected function buildForm($formName, &$form) {
    $this->addElementsToForm($form, []);

    $templateFile = CRM_Core_Resources::singleton()
      ->getPath('uk.co.compucorp.membersonlyevent', 'templates/CRM/MembersOnlyEvent/Hook/BuildForm/Field.tpl');
    CRM_Core_Region::instance('page-body')->add([
      'template' => $templateFile,
    ]);
  }

  /**
   * @param CRM_Price_Form_Field $form
   * @param array $defaults
   */
  protected function addElementsToForm(&$form, $defaults = []) {
    $field_option_count = $form::NUM_OPTION;
    for ($i = 1; $i <= $field_option_count; $i++) {
      $form->add('select', "entity_table[$i]", ts('Additional Signup?'), $this->getEntityOptions());
      $form->addEntityRef("membership_types[$i]", ts('Select Membership Type'), [
        'entity' => 'membershipType',
        'multiple' => TRUE,
        'placeholder' => '- ' . ts('Select Membership Type') . ' -',
        'select' => ['minimumInputLength' => 0],
      ]);
      $form->addEntityRef("events[$i]", ts('Select Event'), [
        'entity' => 'event',
        'multiple' => TRUE,
        'placeholder' => '- ' . ts('Select Event') . ' -',
        'select' => ['minimumInputLength' => 0],
      ]);
    }
    $form->assign('field_option_count', $field_option_count);
  }

}
