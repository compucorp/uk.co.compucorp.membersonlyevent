<?php

use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;
use CRM_MembersOnlyEvent_Configurations as Configurations;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;

class CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess {

  /**
   * @var array
   */
  private $contactActiveAllowedMemberships = [];

  /**
   * @var \CRM_MembersOnlyEvent_DAO_MembersOnlyEvent|null
   */
  private $membersOnlyEvent = NULL;

  /**
   * @var int|null
   */
  private $contactID = NULL;

  /**
   * @var int|null
   */
  private $eventID = NULL;

  /**
   * @param int $eventID
   */
  public function __construct($eventID) {
    if (!$this->isValidPath()) {
      return;
    }
    $this->eventID = $eventID;

    $this->membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($this->eventID);
    $this->contactID = CRM_Core_Session::getLoggedInContactID();

    if ($this->contactID) {
      // Only calculate for logged in users
      $this->contactActiveAllowedMemberships = $this->getAllowedMemberships($this->membersOnlyEvent, $this->contactID);
    }
  }

  /**
   * Checks if the logged-in user has
   * an access to the specified event or not.
   *
   * @return bool
   *   True if has access or False otherwise
   */
  public function userHasEventAccess() {
    if (!$this->contactID) {
      // the user is not logged-in so he cannot access the event
      return FALSE;
    }

    return !empty($this->contactActiveAllowedMemberships);
  }

  /**
   * Return a list of allowed and active memberships
   * Also in case 'membership duration check' is enabled.
   * It check for the membership is valid (active) during the period of
   * the specified event.
   *
   * The membership is valid during an event if :
   * 1- The membership end date is empty (lifetime membership).
   * 2- Or the membership end data is >= the event start date.
   *
   * @param \CRM_MembersOnlyEvent_DAO_MembersOnlyEvent $membersOnlyEvent
   * @param int $contactID
   *   A list of allowed and active memberships to be checked
   *   if they are valid during the specified event period.
   *
   * @return array
   *   Array of valid memberships.
   */
  private function getAllowedMemberships($membersOnlyEvent, $contactID) {
    $contactActiveAllowedMemberships = EventMembershipType::getContactActiveAllowedMemberships($membersOnlyEvent->id, $contactID);

    if (empty($contactActiveAllowedMemberships)) {
      // the users does not have any active membership
      // so he cannot access the event.
      return $contactActiveAllowedMemberships;
    }

    $configs = Configurations::get();
    if (empty($configs['membership_duration_check'])) {
      // the 'membership duration check' is not enabled
      // so the user should be able to access the event.
      return $contactActiveAllowedMemberships;
    }

    $validMemberships = [];
    $eventStartDate = $this->getEventStartDate($membersOnlyEvent->event_id);
    foreach ($contactActiveAllowedMemberships as $membership) {
      $membershipEndDate = !(empty($membership['end_date'])) ? $membership['end_date'] : '';
      if (empty($membershipEndDate) || ($membershipEndDate >= $eventStartDate)) {
        // the user has an active allowed membership for this event
        // so the user should be able to access the event.
        $validMemberships[] = $membership;
      }
    }

    return $validMemberships;
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
    $eventInfo = civicrm_api3('event', 'get',
      [
        'id' => $eventID,
        'return' => ['start_date'],
        'sequential' => 1,
      ])['values'][0];

    $date = new DateTime($eventInfo['start_date']);
    $eventStartDate = $date->format('Y-m-d');

    return $eventStartDate;
  }

  /**
   * @return bool
   */
  public function hasMembership() {
    return !empty($this->contactActiveAllowedMemberships);
  }

  /**
   * @return \CRM_MembersOnlyEvent_DAO_MembersOnlyEvent|NULL
   */
  public function getMembersOnlyEvent() {
    return $this->membersOnlyEvent;
  }

  /**
   * if the user has no access, redirect to the main page
   */
  public function redirectUsersWithoutEventAccess() {
    if (!$this->userHasEventAccess()) {
      // if the user has no access, redirect to the main page
      CRM_Utils_System::redirect('/');
    }
  }

  /**
   * @return bool
   */
  public function isValidPath() {
    $currentPath = CRM_Utils_System::currentPath();
    $validPaths = [
      'civicrm/event/register',
      'civicrm/event/info',
    ];
    return in_array($currentPath, $validPaths);
  }

}
