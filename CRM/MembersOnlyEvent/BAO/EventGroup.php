<?php

use CRM_MembersOnlyEvent_ExtensionUtil as E;

class CRM_MembersOnlyEvent_BAO_EventGroup extends CRM_MembersOnlyEvent_DAO_EventGroup {

  /**
   * Static instance to hold the contact's group IDs.
   *
   * @var array
   */
  private static $contactGroupIDs = NULL;

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
   * Gets the groups for the specified contact in case he
   * belongs to any allowed group to access the provided
   * members-only event.
   *
   * @param int $contactID
   *
   * @return array
   *   List of contact groups or empty array if nothing found
   */
  public static function getContactGroupIDs($contactID) {
    if (self::$contactGroupIDs !== NULL) {
      return self::$contactGroupIDs;
    }
    self::$contactGroupIDs = [];

    $params = [
      'sequential' => 1,
      'contact_id' => (int) $contactID,
      'status' => 'Added',
      'options' => ['limit' => 0],
      // The return param is not working.
      /* 'return' => ['group_id'], */
      // The IN param is not working.
      /* 'group_id' => ['IN' => $allowedGroups], */
    ];

    $contactGroups = civicrm_api3('GroupContact', 'get', $params);

    if ($contactGroups['count']) {
      $contactGroupIDs = array_map(function($contactGroup) {
        return $contactGroup['group_id'];
      }, $contactGroups['values']);
      self::$contactGroupIDs = $contactGroupIDs;
    }

    return self::$contactGroupIDs;
  }

}
