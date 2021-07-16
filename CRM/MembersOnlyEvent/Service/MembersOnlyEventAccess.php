<?php

use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;
use CRM_MembersOnlyEvent_BAO_EventGroup as EventGroup;
use CRM_MembersOnlyEvent_Configurations as Configurations;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;
use CRM_MembersOnlyEvent_Utils_Array as ArrayUtils;

class CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess {

  /**
   * @var array
   */
  private $contactActiveAllowedMemberships = [];

  /**
   * @var array
   */
  private $contactAllowedGroups = [];

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
      $this->contactAllowedGroups = $this->getAllowedGroups($this->membersOnlyEvent, $this->contactID);
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

    if (empty($this->membersOnlyEvent->is_groups_only) && CRM_Core_Permission::check('members only event registration')) {
      // Any user with 'members only event registration' permission
      // can access any members-only event.
      return TRUE;
    }

    if (empty($this->membersOnlyEvent->is_groups_only)) {
      return !empty($this->contactActiveAllowedMemberships);
    }

    return !empty($this->contactAllowedGroups);
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
   * Returns a list of allowed groups that the user belongs to.
   *
   * @param \CRM_MembersOnlyEvent_DAO_MembersOnlyEvent $membersOnlyEvent
   * @param int $contactID
   *
   * @return array
   *   Array of groups.
   */
  private function getAllowedGroups($membersOnlyEvent, $contactID) {
    $contactAllowedGroups = EventGroup::getContactAllowedGroups($membersOnlyEvent->id, $contactID);

    return $contactAllowedGroups;
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

  /**
   * Gets event access details for a given event ids
   *
   * @param array $eventIDs
   *
   * @throws \CRM_Core_Exception
   */
  public static function getEventAccessDetails($eventIDs) {
    $membersOnlyEvents = MembersOnlyEvent::getMembersOnlyEvents($eventIDs);
    $membersOnlyEvents = ArrayUtils::keyBy($membersOnlyEvents, 'id');
    $eventIDAndMembersOnlyEventIDMap = array_column($membersOnlyEvents, 'id', 'event_id');
    $eventGroups = EventGroup::getEventGroups(array_keys($membersOnlyEvents));
    $groupsKeyedByEventID = [];
    foreach ($eventGroups as $eventGroup) {
      $membersOnlyEvent = $membersOnlyEvents[$eventGroup['members_only_event_id']];
      $eventID = $membersOnlyEvent['event_id'];
      $groupsKeyedByEventID[$eventID][] = $eventGroup['group_id'];
    }

    $contactId = CRM_Core_Session::getLoggedInContactID();
    $contactGroupIDs = EventGroup::getContactGroupIDs($contactId);

    $events = [];
    foreach ($eventIDs as $eventID) {
      $groups = $groupsKeyedByEventID[$eventID] ?? [];
      $allowedGroupsWhichUserBelongsTo = array_intersect($contactGroupIDs, $groups);
      $membersOnlyEventID = $eventIDAndMembersOnlyEventIDMap[$eventID] ?? NULL;
      $notice_for_access_denied = $membersOnlyEvents[$membersOnlyEventID]['notice_for_access_denied'] ?? '';
      $events[] = [
        'event_id' => $eventID,
        'is_groups_only_event' => !empty($groupsKeyedByEventID[$eventID]),
        'allowed_groups' => $groups,
        'is_user_in_any_allowed_group' => !empty($allowedGroupsWhichUserBelongsTo),
        'allowed_groups_which_user_belongs_to' => $allowedGroupsWhichUserBelongsTo,
        'notice_for_access_denied' => $notice_for_access_denied,
      ];
    }

    return $events;
  }

}
