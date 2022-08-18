<?php

use CRM_MembersOnlyEvent_Hook_PageRun_Base as PageRunBase;
use CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess as MembersOnlyEventAccessService;

/**
 * Class for Event Info PageRun Hook
 */
class CRM_MembersOnlyEvent_Hook_PageRun_Register extends PageRunBase {

  /**
   * @var \CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess
   */
  private $membersOnlyEventAccessService;

  /**
   * Checks if the hook should be handled.
   *
   * @param $pageName
   * @param $page
   *
   * @return bool
   */
  protected function shouldHandle($pageName, &$page) {
    if ($pageName === CRM_Event_Page_EventInfo::class) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Callback for event info page
   *
   * @param $page
   */
  protected function pageRun(&$page) {
    $eventID = $page->_id;
    $this->membersOnlyEventAccessService = new MembersOnlyEventAccessService($eventID);

    $this->showSessionMessageWhenRegisteringAnotherParticipant();

    if ($this->membersOnlyEventAccessService->userHasEventAccess()) {
      // skip early and show the page if the user has access to the members-only event.
      return;
    }

    $this->hideEventInfoPageRegisterButton();

    $this->addbocks();
  }

  /**
   * Handle session message if the user is trying
   * to register another participant.
   */
  private function showSessionMessageWhenRegisteringAnotherParticipant() {
    $isEventForMembersOnly = $this->membersOnlyEventAccessService->getMembersOnlyEvent();

    if (!$isEventForMembersOnly) {
      return;
    }

    $session = CRM_Core_Session::singleton();
    $statusMessages = $session->get('status');
    if (empty($statusMessages)) {
      return;
    }

    foreach ($statusMessages as $k => $msg) {
      if (strpos($msg['text'], 'register another participant')) {
        $statusMessages[$k]['text'] = ts("It looks like you are already registered for this event. If you want to change your registration, or you feel that you've gotten this message in error, please contact the site administrator.");
      }
    }
    $session->set('status', $statusMessages);
  }

  /**
   * Hides the event info page action links which contain
   * the event register link.
   */
  private function hideEventInfoPageRegisterButton() {
    CRM_Core_Region::instance('event-page-eventinfo-actionlinks-top')
      ->update('default', [
        'disabled' => TRUE,
      ]);
    CRM_Core_Region::instance('event-page-eventinfo-actionlinks-bottom')
      ->update('default', [
        'disabled' => TRUE,
      ]);
  }

  /**
   * Checks whether the ssp_core is enabled or not.
   */
  private function isSSPCoreEnabled() {
    $config = CRM_Core_Config::singleton();
    if (!$config->userSystem->is_drupal) {
      return FALSE;
    }

    if (!module_exists('ssp_core')) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Adds the access denied, login and membership blocks.
   */
  private function addbocks() {
    if ($this->isSSPCoreEnabled()) {
      // Skip adding the blocks, the module uses a custom template.
      return;
    }

    $membersOnlyEvent = $this->membersOnlyEventAccessService->prepareMembersOnlyEventForTemplate();

    CRM_Core_Region::instance('event-page-eventinfo-actionlinks-bottom')
      ->add([
        'template' => 'CRM/Event/Page/blocks.tpl',
        'membersOnlyEvent' => $membersOnlyEvent,
      ]);
  }

}
