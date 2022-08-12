<?php

class CRM_MembersOnlyEvent_BAO_MembersOnlyEvent extends CRM_MembersOnlyEvent_DAO_MembersOnlyEvent {

  /**
   * Contribution page link type for 'purchase membership button'.
   *
   * @const int
   */
  const LINK_TYPE_CONTRIBUTION_PAGE = 0;

  /**
   * URL link type for 'purchase membership button'.
   *
   * @const int
   */
  const LINK_TYPE_URL = 1;

  /**
   * Event access type for 'members only events'.
   *
   * @const int
   */
  const EVENT_ACCESS_TYPE_MEMBERS_ONLY = 1;

  /**
   * Event access type for 'groups only events'.
   *
   * @const int
   */
  const EVENT_ACCESS_TYPE_GROUPS_ONLY = 2;

  /**
   * Event access type for 'authenticated users only events'.
   *
   * @const int
   */
  const EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY = 3;

  /**
   * Block type for 'Login block only'.
   *
   * @const int
   */
  const BLOCK_TYPE_LOGIN_ONLY = 1;

  /**
   * Block type for 'Login or register block'.
   *
   * @const int
   */
  const BLOCK_TYPE_LOGIN_OR_REGISTER_BLOCK = 2;

  /**
   * Creates a new Members-Only Event record
   * based on array-data
   *
   * @param array $params
   *
   * @return CRM_MembersOnlyEvent_BAO_MembersOnlyEvent
   */
  public static function create($params) {
    $entityName = 'MembersOnlyEvent';
    $hookOperation = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hookOperation, $entityName, CRM_Utils_Array::value('id', $params), $params);

    $membersOnlyEvent = new self();
    $membersOnlyEvent->copyValues($params);
    $membersOnlyEvent->save();

    CRM_Utils_Hook::post($hookOperation, $entityName, $membersOnlyEvent->id, $membersOnlyEvent);

    return $membersOnlyEvent;
  }

  /**
   * Gets the members-Only Event data
   * given the event ID, or return false if
   * the event is not a members-only event.
   *
   * @param int $eventID
   *
   * @return CRM_MembersOnlyEvent_DAO_MembersOnlyEvent|FALSE
   */
  public static function getMembersOnlyEvent($eventID) {
    $membersOnlyEvent = new self();
    $membersOnlyEvent->event_id = $eventID;
    $membersOnlyEvent->find(TRUE);

    if ($membersOnlyEvent->N) {
      return $membersOnlyEvent;
    }

    return FALSE;
  }

  /**
   * Gets the members-only events given the event IDs
   *
   * @param $eventIDs
   * @param $eventAccessType
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function getMembersOnlyEvents($eventIDs, int $eventAccessType) {
    if (empty($eventIDs)) {
      return [];
    }

    $result = civicrm_api3('MembersOnlyEvent', 'get', [
      'sequential' => 1,
      'event_access_type' => $eventAccessType,
      'event_id' => ['IN' => $eventIDs],
      'return' => ['id', 'event_id', 'notice_for_access_denied'],
      'options' => ['limit' => 0],
    ]);

    return $result['values'] ?? [];
  }

}
