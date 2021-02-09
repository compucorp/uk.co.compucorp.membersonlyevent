<?php

/**
 * Abstract class for BuildForm Hook
 */
abstract class CRM_MembersOnlyEvent_Hook_BuildForm_Base {

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
   * @param $form
   */
  abstract protected function shouldHandle($formName, &$form);

  /**
   * @param $formName
   * @param $form
   */
  abstract protected function buildForm($formName, &$form);

}
