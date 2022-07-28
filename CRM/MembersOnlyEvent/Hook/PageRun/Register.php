<?php

use CRM_MembersOnlyEvent_Hook_PageRun_Base as PageRunBase;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;
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

    $this->handleAccessOptionForUser();
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
   * Handles access options for logged / anonymous user.
   */
  private function handleAccessOptionForUser() {
    $membersOnlyEvent = $this->membersOnlyEventAccessService->getMembersOnlyEvent();

    if ($membersOnlyEvent->is_showing_purchase_membership_block) {
      $this->addMembershipPurchaseButtonToEventInfoPage($membersOnlyEvent);
      $userLoggedIn = CRM_Core_Session::getLoggedInContactID();
      if ($userLoggedIn) {
        return;
      }
      $loginURL = CRM_Core_Config::singleton()->userSystem->getLoginURL();
      $infoText = 'This event is for members only, if you have a current, pending or former membership
                 please log in before purchase membership. If you are not a current member you will be charged
                 an additional membership fee. <a href="' . $loginURL . '">Click here to login </a>';
      CRM_Core_Session::setStatus(ts($infoText));

    }
    else {
      // Purchase membership button is disabled, so we will just show the configured notice message
      CRM_Core_Session::setStatus($membersOnlyEvent->notice_for_access_denied);
    }
  }

  /**
   * Adds membership purchase button based
   * on the members-only event configurations to
   * the header and the footer of the event info page.
   *
   * @param \CRM_MembersOnlyEvent_DAO_MembersOnlyEvent $membersOnlyEvent
   */
  private function addMembershipPurchaseButtonToEventInfoPage($membersOnlyEvent) {
    switch ($membersOnlyEvent->purchase_membership_link_type) {
      case MembersOnlyEvent::LINK_TYPE_CONTRIBUTION_PAGE:
        $contributionPageID = $membersOnlyEvent->contribution_page_id;
        $path = 'civicrm/contribute/transact';
        $params = 'reset=1&id=' . $contributionPageID;
        $membershipPurchaseURL = CRM_Utils_System::url($path, $params);
        break;

      case MembersOnlyEvent::LINK_TYPE_URL:
      default:
        $membershipPurchaseURL = $membersOnlyEvent->purchase_membership_url;
        break;
    }

    $buttonText = $membersOnlyEvent->purchase_membership_button_label;

    $this->addActionButtonToEventInfoPage($membershipPurchaseURL, $buttonText);
  }

  /**
   * Adds a button with the specified
   * url and text to the header and the footer
   * of the event info page.
   *
   * @param $url
   * @param $buttonText
   */
  public function addActionButtonToEventInfoPage($url, $buttonText) {
    $buttonToAdd = [
      'template' => 'CRM/Event/Page/members-event-button.tpl',
      'button_text' => ts($buttonText),
      'position' => 'top',
      'url' => $url,
      'weight' => -10,
    ];

    CRM_Core_Region::instance('event-page-eventinfo-actionlinks-top')
      ->add($buttonToAdd);

    $buttonToAdd['position'] = 'bottom';
    CRM_Core_Region::instance('event-page-eventinfo-actionlinks-bottom')
      ->add($buttonToAdd);
  }

}
