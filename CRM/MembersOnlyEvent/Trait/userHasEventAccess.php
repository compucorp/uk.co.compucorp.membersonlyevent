<?php

use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;
use CRM_MembersOnlyEvent_Configurations as Configurations;

trait CRM_MembersOnlyEvent_Trait_userHasEventAccess {

  /**
   * Checks if the logged-in user has
   * an access to the specified event or not.
   *
   * @param \CRM_MembersOnlyEvent_BAO_MembersOnlyEvent $membersOnlyEvent
   *
   * @return bool
   *   True if has access or False otherwise
   */
  private function userHasEventAccess($membersOnlyEvent) {
    $contactID = CRM_Core_Session::getLoggedInContactID();
    if (!$contactID) {
      // the user is not logged-in so he cannot access the event
      return FALSE;
    }

    if (CRM_Core_Permission::check('members only event registration')) {
      // any user with 'members only event registration' permission
      // can access any members-only event.
      return TRUE;
    }

    $contactActiveAllowedMemberships = EventMembershipType::getContactActiveAllowedMemberships($membersOnlyEvent->id, $contactID);

    if (!$contactActiveAllowedMemberships) {
      // the users does not have any active membership
      // so he cannot access the event.
      return FALSE;
    }

    return $this->isMembershipsDurationValidDuringEvent($membersOnlyEvent->event_id, $contactActiveAllowedMemberships);
  }

  /**
   * Checks if any of a list of allowed an active memberships
   * is valid (active) during the  period of
   * the specified event in case 'membership duration check'
   * is enabled.
   * The membership is valid during an event if :
   * 1- The membership end date is empty.
   * 2- The event start date is empty.
   * 3- The membership end data is > the event start date.
   *
   * @param int $eventID
   * @param array $activeAllowedMemberships
   *   A list of allowed and active memberships to be checked
   *   if they are valid during the specified event period.
   *
   * @return bool
   *   True if there is any valid membership during the event period
   *   or false otherwise.
   */
  private function isMembershipsDurationValidDuringEvent($eventID, $activeAllowedMemberships) {
    $configs = Configurations::get();
    if (empty($configs['membership_duration_check'])) {
      // the 'membership duration check' is not enabled
      // so the user should be able to access the event.
      return TRUE;
    }

    $eventStartDate = $this->getEventStartDate($eventID);
    foreach ($activeAllowedMemberships as $membership) {
      $membershipEndDate = !(empty($membership['end_date'])) ? $membership['end_date'] : '';
      if (empty($membershipEndDate) || empty($eventStartDate) || ($membershipEndDate >= $eventStartDate)) {
        // the user has an active allowed membership for this event
        // so the user should be able to access the event.
        return TRUE;
      }
    }

    // since 'membership duration check' is enabled but
    // the user does not have any active allowed membership
    // for this event so he will not be able to access the event.
    return FALSE;
  }

  /**
   * Gets the event start date in Y-m-d format if exist.
   *
   * @param $eventID
   *
   * @return string
   *   Event start date in y-md format
   *   or empty string if no start date exist.
   */
  private function getEventStartDate($eventID) {
    $eventStartDate = '';

    $eventInfo = civicrm_api3('event', 'get',
      [
        'id' => $eventID,
        'return' => ['start_date'],
        'sequential' => 1,
      ])['values'][0];

    if (!empty($eventInfo['start_date'])) {
      $date = new DateTime($eventInfo['start_date']);
      $eventStartDate = $date->format('Y-m-d');
    }

    return $eventStartDate;
  }

}
