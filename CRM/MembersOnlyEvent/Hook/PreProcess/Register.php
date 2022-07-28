<?php

use CRM_MembersOnlyEvent_Hook_PreProcess_Base as PreProcessBase;
use CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess as MembersOnlyEventAccessService;

/**
 * Class for Event Registration Form PreProcess Hook
 */
class CRM_MembersOnlyEvent_Hook_PreProcess_Register extends PreProcessBase {

  /**
   * @var \CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess
   */
  private $membersOnlyEventAccessService;

  /**
   * Checks if the hook should be handled.
   *
   * @param $formName
   * @param $form
   *
   * @return bool
   */
  protected function shouldHandle($formName, &$form) {
    if ($formName !== CRM_Event_Form_Registration_Register::class
      || $form->getAction() !== CRM_Core_Action::ADD) {

      return FALSE;
    }

    $this->membersOnlyEventAccessService = new MembersOnlyEventAccessService($form->_eventId);
    $membersOnlyEvent = $this->membersOnlyEventAccessService->getMembersOnlyEvent();
    if (!$membersOnlyEvent) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Callback for event registration page
   *
   * Hence that users are supposed to register for events
   * from the info page, so in case the user tired to access
   * the registration page directly we will just redirect him
   * to the info page.
   *
   * @param $formName
   * @param CRM_Event_Form_Registration_Register $form
   *
   * @throws \CRM_Core_Exception
   */
  protected function preProcess($formName, &$form) {
    $this->membersOnlyEventAccessService->redirectUsersWithoutEventAccess();
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
    CRM_Core_Resources::singleton()
      ->addStyle('.crm-not-you-message { display: none; }');
    if (isset($cid)) {
      CRM_Core_Session::setStatus('You have already registered for this event! You cannot register other users.');
      $id = CRM_Utils_Request::retrieve('id', 'Positive');
      $params = 'id=' . $id;
      if ($reset = CRM_Utils_Request::retrieve('reset', 'Positive')) {
        $params .= '&reset=' . $reset;
      }
      $url = CRM_Utils_System::url(CRM_Utils_System::currentPath(), $params);
      CRM_Utils_System::redirect($url);
      $form->_skipDupeRegistrationCheck = TRUE;
    }

  }

}
