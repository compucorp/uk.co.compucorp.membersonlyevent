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
    if (
      empty($this->membersOnlyEvent) ||
      ($this->contactID && CRM_Core_Permission::check('members only event registration'))
    ) {
      // Any user (including anonymous) with 'members only event registration' permission
      // can access any members-only event.
      return TRUE;
    }

    $event_access_type = (int) $this->membersOnlyEvent->event_access_type;
    if ($event_access_type === MembersOnlyEvent::EVENT_ACCESS_TYPE_MEMBERS_ONLY) {
      return !empty($this->contactActiveAllowedMemberships);
    }

    if ($event_access_type === MembersOnlyEvent::EVENT_ACCESS_TYPE_GROUPS_ONLY) {
      return !empty($this->contactAllowedGroups);
    }

    if ($event_access_type === MembersOnlyEvent::EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY) {
      return !empty($this->contactID);
    }
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
      // if the user has no access, redirect to the event info page.
      $id = CRM_Utils_Request::retrieve('id', 'Positive');
      $params = 'id=' . $id;
      if ($reset = CRM_Utils_Request::retrieve('reset', 'Positive')) {
        $params .= '&reset=' . $reset;
      }

      $url = CRM_Utils_System::url('civicrm/event/info', $params);
      CRM_Utils_System::redirect($url);
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
   * Prepares members-only event for template.
   *
   * @return array
   *   The membersOnlyEvent details.
   *
   * @throws \CRM_Core_Exception
   */
  public function prepareMembersOnlyEventForTemplate() {
    $membersOnlyEvent = (array) $this->membersOnlyEvent;
    $config = CRM_Core_Config::singleton();

    $contactId = CRM_Core_Session::getLoggedInContactID();
    if ($contactId) {
      // Logged in users cannot see the login form and will be redirected.
      $membersOnlyEvent['is_showing_login_block'] = "0";
    }

    if (!empty($membersOnlyEvent['is_showing_login_block'])) {
      $block_type = (int) $membersOnlyEvent['block_type'];
      if ($block_type === MembersOnlyEvent::BLOCK_TYPE_LOGIN_ONLY) {
        $user_login_form_content = '';
        if ($config->userSystem->is_drupal) {
          $user_login_form = drupal_get_form('user_login');
          $register_page = '/civicrm/event/register?reset=1&id=' . $membersOnlyEvent['event_id'];
          $query_string_prefix = "?";
          if (strpos($user_login_form['#action'], '?') !== FALSE) {
            $query_string_prefix = "&";
          }
          $user_login_form['#action'] .= $query_string_prefix . 'destination=' . urlencode($register_page);
          $user_login_form_content = drupal_render($user_login_form);
        }

        $membersOnlyEvent['login_block_content'] = $user_login_form_content;
        $membersOnlyEvent['login_block_header'] = '<h2>' . ts('Login') . '</h2>';
      }
      else {
        $user_login_form_content = '';
        if ($config->userSystem->is_drupal) {
          $user_login_form = drupal_get_form('ssp_core_user_login_or_register_form');
          $user_login_form_content = drupal_render($user_login_form);
        }

        $membersOnlyEvent['login_block_content'] = $user_login_form_content;
        $membersOnlyEvent['login_block_header'] = '<h2>' . ts('Login or Register') . '</h2>';
      }
    }

    if (!empty($membersOnlyEvent['is_showing_purchase_membership_block'])) {
      if ($membersOnlyEvent['purchase_membership_link_type'] === "0") {
        $path = 'civicrm/contribute/transact';
        $params = 'reset=1&id=' . $membersOnlyEvent['contribution_page_id'];
        $membersOnlyEvent['purchase_membership_url'] = CRM_Utils_System::url($path, $params);
      }
    }

    return $membersOnlyEvent;
  }

  /**
   * Gets event access details for a given event ids
   *
   * @param array $eventIDs
   *
   * @throws \CRM_Core_Exception
   */
  public static function getEventAccessDetails($eventIDs) {
    $membersOnlyEvents = MembersOnlyEvent::getMembersOnlyEvents($eventIDs, MembersOnlyEvent::EVENT_ACCESS_TYPE_GROUPS_ONLY);
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
      $allowedGroupsWhichUserBelongsTo = array_values(array_intersect($contactGroupIDs, $groups));
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
