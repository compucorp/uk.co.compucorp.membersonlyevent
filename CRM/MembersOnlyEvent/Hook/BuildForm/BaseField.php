<?php

/**
 * Abstract class for BuildForm Hook
 */
abstract class CRM_MembersOnlyEvent_Hook_BuildForm_BaseField {

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

    $this->buildForm($formName, $form);
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param $formName
   * @param $formClass
   */
  abstract protected function shouldHandle($formName, &$form);

  /**
   * @param $formName
   * @param $form
   */
  abstract protected function buildForm($formName, &$form);

  /**
   * @param object $form
   * @param array $defaults
   */
  abstract protected function addElementsToForm(&$form, $defaults);

  /**
   * @return array
   */
  public function getEntityOptions() {
    $entityOptions = [
      0 => ts('No'),
    ];
    try {
      $components = civicrm_api3('Setting', 'getvalue', [
        'group' => "CiviCRM Preferences",
        'name' => "enable_components",
      ]);
      if (in_array('CiviMember', $components)) {
        $entityOptions['Membership'] = ts('Membership');
      }
      if (in_array('CiviEvent', $components)) {
        $entityOptions['Participant'] = ts('Participant');
      }
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_var('Cannot find enabled components', $e);
    }

    return $entityOptions;
  }
}
