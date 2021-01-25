<?php

use CRM_MembersOnlyEvent_Hook_BuildForm_BaseField as BaseField;
use CRM_MembersOnlyEvent_BAO_EntityPriceFieldValue as EntityPriceFieldValue;

/**
 * Class for Price Field Option Form BuildForm Hook
 */
class CRM_MembersOnlyEvent_Hook_BuildForm_Option extends BaseField {

  /**
   * Checks if the hook should be handled.
   *
   * @param $formName
   * @param $form
   *
   * @return bool
   */
  protected function shouldHandle($formName, &$form) {
    if ($formName === CRM_Price_Form_Option::class) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param $formName
   * @param $form
   *
   * @throws \CRM_Core_Exception
   */
  protected function buildForm($formName, &$form) {
    $defaults = [];
    if ($form->getAction() === CRM_Core_Action::UPDATE) {
      $id = $form->getVar('_oid');
      $entityPriceFieldValue = new EntityPriceFieldValue();
      $entityPriceFieldValue->price_field_value_id = $id;
      $entityPriceFieldValue->find();
      while ($entityPriceFieldValue->fetch()) {
        if ($entityPriceFieldValue->entity_table == 'Event') {
          $defaults['entity_table'] = 'Participant';
          $defaults['events'][] = $entityPriceFieldValue->entity_id;
        }
        elseif ($entityPriceFieldValue->entity_table == 'MembershipType') {
          $defaults['entity_table'] = 'Membership';
          $defaults['membership_types'][] = $entityPriceFieldValue->entity_id;
        }
      }
    }

    $this->addElementsToForm($form, $defaults);

    $templateFile = CRM_Core_Resources::singleton()
      ->getPath('uk.co.compucorp.membersonlyevent', 'templates/CRM/MembersOnlyEvent/Hook/BuildForm/Option.tpl');
    CRM_Core_Region::instance('page-body')->add([
      'template' => $templateFile,
    ]);
  }

  /**
   * @param $form
   * @param $defaults
   */
  public function addElementsToForm(&$form, $defaults = []) {
    // Add the field element in the form.
    $form->add('select', 'entity_table', ts('Other Sign Up?'), $this->getEntityOptions());
    $form->addEntityRef("membership_types", ts('Select Membership Type'), [
      'entity' => 'membershipType',
      'multiple' => TRUE,
      'placeholder' => '- ' . ts('Select Membership Type') . ' -',
      'select' => ['minimumInputLength' => 0],
    ]);
    $form->addEntityRef('events', ts('Select Event'), [
      'entity' => 'event',
      'multiple' => TRUE,
      'placeholder' => '- ' . ts('Select Event') . ' -',
      'select' => ['minimumInputLength' => 0],
    ]);

    $form->setDefaults($defaults);
  }

}
