<?php

use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;

/**
 * Class CRM_MembersOnlyEvent_Hook_Event
 */
class CRM_MembersOnlyEvent_Hook_Tabset_Event {

  /**
   * @param $eventID
   * @param $tabs
   */
  public function handle($eventID, &$tabs) {
    $url = CRM_Utils_System::url(
      'civicrm/event/manage/membersonlyevent',
      'reset=1&id=' . $eventID . '&action=update&component=event');

    $tab['membersonlyevent'] = [
      'title' => ts('Members Only Event Settings'),
      'link' => $url,
      'valid' => $this->isTabValid($eventID),
      'active' => TRUE,
      'current' => FALSE,
      'class' => 'ajaxForm',
    ];

    //Insert this tab into position 4 (after `Online Registration` tab)
    $tabs = array_merge(
      array_slice($tabs, 0, 4),
      $tab,
      array_slice($tabs, 4)
    );
  }

  /**
   * Checks if the members-only settings tab
   * should be valid or not. Currently it is valid
   * only if the event is members-only event and
   * online registration is enabled.
   *
   * @param int $eventID
   *
   * @return bool
   *
   */
  private function isTabValid($eventID) {
    $event = civicrm_api3('Event', 'get', [
      'sequential' => 1,
      'return' => ['is_online_registration'],
      'id' => $eventID,
    ]);

    $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($eventID);
    $isOnlineRegistrationEnabled = !empty($event['values'][0]['is_online_registration']) ? TRUE : FALSE;
    $isValid = $isOnlineRegistrationEnabled && $membersOnlyEvent ? TRUE : FALSE;

    return $isValid;
  }

}
