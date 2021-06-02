<?php

use CRM_MembersOnlyEvent_ExtensionUtil as E;

class CRM_MembersOnlyEvent_BAO_EventGroup extends CRM_MembersOnlyEvent_DAO_EventGroup {

  /**
   * Stores the allowed groups for specific
   * members-only event
   *
   * @param int $membersOnlyEventID
   * @param array $allowedGroupIDs
   */
  public static function updateAllowedGroups($membersOnlyEventID, $allowedGroupIDs) {
    $oldAllowedGroupIDs = self::getAllowedGroupIDs($membersOnlyEventID);
    if ($oldAllowedGroupIDs == $allowedGroupIDs) {
      return;
    }

    $transaction = new CRM_Core_Transaction();

    $removeResponse = self::removeAllowedGroups($membersOnlyEventID);
    $createResponse = self::createAllowedGroups($membersOnlyEventID, $allowedGroupIDs);

    if ($removeResponse === FALSE || $createResponse === FALSE) {
      $transaction->rollback();
    }
    else {
      $transaction->commit();
    }
  }

  /**
   * Removes all allowed groups for
   * the provided members-only event.
   *
   * @param int $membersOnlyEventID
   */
  private static function removeAllowedGroups($membersOnlyEventID) {
    $group = new self();
    $group->members_only_event_id = $membersOnlyEventID;

    return $group->delete();
  }

  /**
   * Sets the allowed groups
   * for the provided members-only event.
   *
   * @param int $membersOnlyEventID
   * @param array $allowedGroupIDs
   *
   * @return boolean
   *   True if the creation of all allowed groups
   *   succeed or False otherwise.
   */
  private static function createAllowedGroups($membersOnlyEventID, $allowedGroupIDs) {
    $createdRecordsCount = 0;
    foreach ($allowedGroupIDs as $allowedGroupID) {
      $eventGroup = new self();
      $eventGroup->members_only_event_id = $membersOnlyEventID;
      $eventGroup->group_id = $allowedGroupID;
      $eventGroup->save();
      $createdRecordsCount++;
    }

    return $createdRecordsCount == count($allowedGroupIDs);
  }

  /**
   * Gets the allowed groups for specific
   * members-only event.
   *
   * @param int $membersOnlyEventID
   *
   * @return array
   *   The IDs of allowed groups
   */
  public static function getAllowedGroupIDs($membersOnlyEventID) {
    $eventGroup = new self();
    $eventGroup->members_only_event_id = $membersOnlyEventID;
    $eventGroup->find();

    $allowedGroupIDs = [];
    while ($eventGroup->fetch()) {
      $allowedGroupIDs[] = $eventGroup->group_id;
    }

    return $allowedGroupIDs;
  }

  /**
   * Gets the groups for the specified
   * contact in case he belong to any group
   * allowed to access the provided members-only event.
   *
   * @param int $membersOnlyEventID
   * @param int $contactID
   *
   * @return array
   *   List of contact groups or empty array if nothing found
   */
  public static function getContactAllowedGroups($membersOnlyEventID, $contactID) {
    $allowedGroups = self::getAllowedGroupIDs($membersOnlyEventID);
    if (empty($allowedGroups)) {
      return [];
    }

    return array_intersect(self::getContactGroupIDs($contactID), $allowedGroups);
  }

  /**
   * Gets the groups for the specified contact.
   *
   * @param int $contactID
   *
   * @return array
   *   List of contact groups or empty array if nothing found
   */
  public static function getContactGroupIDs($contactID) {
    $contactGroupIDs = array_merge(
      self::getContactNormalGroupIds($contactID),
      self::getContactSmartGroupIds($contactID)
    );

    return $contactGroupIDs;
  }

  /**
   * Gets The normal (not smart) groups that the contact is part of.
   *
   * @param int $contactID
   *
   * @return array
   *   List of group ids or empty array if nothing found
   */
  private static function getContactNormalGroupIds($contactID) {
    $params = [
      'sequential' => 1,
      'contact_id' => (int) $contactID,
      'status' => 'Added',
      'options' => ['limit' => 0],
    ];

    $contactGroups = civicrm_api3('GroupContact', 'get', $params);

    $groupIDs = [];
    if ($contactGroups['count']) {
      $contactGroupIDs = array_map(function($contactGroup) {
        return $contactGroup['group_id'];
      }, $contactGroups['values']);
      $groupIDs = $contactGroupIDs;
    }

    return $groupIDs;
  }

  /**
   * Gets the smart groups that the contact is part of.
   *
   * @param int $contactID
   *
   * @return array
   *   List of group ids or empty array if nothing found
   */
  private static function getContactSmartGroupIds($contactID) {
    $query = "SELECT group_id FROM `civicrm_group_contact_cache` WHERE contact_id=%1";
    $queryParams = [
      1 => [(int) $contactID, 'Positive'],
    ];
    $cachedGroupContacts = CRM_Core_DAO::executeQuery($query, $queryParams);

    $groupIDs = [];
    while ($cachedGroupContacts->fetch()) {
      $groupIDs[] = $cachedGroupContacts->group_id;
    }

    return $groupIDs;
  }

  /**
   * Gets the event-groups event data given the membersOnlyEvent IDs
   *
   * @param $membersOnlyEventIDs
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function getEventGroups($membersOnlyEventIDs) {
    if (empty($membersOnlyEventIDs)) {
      return [];
    }

    $result = civicrm_api3('EventGroup', 'get', [
      'sequential' => 1,
      'members_only_event_id' => ['IN' => $membersOnlyEventIDs],
      'return' => ['members_only_event_id', 'group_id'],
      'options' => ['limit' => 0],
    ]);

    return $result['values'] ?? [];
  }

}
