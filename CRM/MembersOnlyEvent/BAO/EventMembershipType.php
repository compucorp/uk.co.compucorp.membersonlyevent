<?php

class CRM_MembersOnlyEvent_BAO_EventMembershipType extends CRM_MembersOnlyEvent_DAO_EventMembershipType {

  /**
   * Stores the allowed membership types for specific
   * members-only event
   *
   * @param int $membersOnlyEventID
   * @param array $allowedMembershipTypeIDs
   */
  public static function updateAllowedMembershipTypes($membersOnlyEventID, $allowedMembershipTypeIDs) {
    $oldAllowedMembershipTypeIDs = self::getAllowedMembershipTypeIDs($membersOnlyEventID);
    if ($oldAllowedMembershipTypeIDs == $allowedMembershipTypeIDs) {
      return;
    }

    $transaction = new CRM_Core_Transaction();

    $removeResponse = self::removeAllowedMembershipTypes($membersOnlyEventID);
    $createResponse = self::createAllowedMembershipTypes($membersOnlyEventID, $allowedMembershipTypeIDs);

    if ($removeResponse === FALSE || $createResponse === FALSE) {
      $transaction->rollback();
    }
    else {
      $transaction->commit();
    }
  }

  /**
   * Removes all allowed membership types for
   * the provided members-only event.
   *
   * @param int $membersOnlyEventID
   */
  private static function removeAllowedMembershipTypes($membersOnlyEventID) {
    $membership_type = new self();
    $membership_type->members_only_event_id = $membersOnlyEventID;

    return $membership_type->delete();
  }

  /**
   * Sets the allowed membership types
   * for the provided members-only event.
   *
   * @param int $membersOnlyEventID
   * @param array $allowedMembershipTypeIDs
   *
   * @return boolean
   *   True if the creation of all allowed membership
   *   types succeed or False otherwise.
   */
  private static function createAllowedMembershipTypes($membersOnlyEventID, $allowedMembershipTypeIDs) {
    $createdRecordsCount = 0;
    foreach ($allowedMembershipTypeIDs as $allowedMembershipTypeID) {
      $eventMembershipType = new self();
      $eventMembershipType->members_only_event_id = $membersOnlyEventID;
      $eventMembershipType->membership_type_id = $allowedMembershipTypeID;
      $eventMembershipType->save();
      $createdRecordsCount++;
    }

    return $createdRecordsCount == count($allowedMembershipTypeIDs);
  }

  /**
   * Gets the allowed membership types for specific
   * members-only event.
   *
   * @param int $membersOnlyEventID
   *
   * @return array
   *   The IDs of allowed membership types
   */
  public static function getAllowedMembershipTypeIDs($membersOnlyEventID) {
    $eventMembershipType = new self();
    $eventMembershipType->members_only_event_id = $membersOnlyEventID;
    $eventMembershipType->find();

    $allowedMembershipTypeIDs = [];
    while ($eventMembershipType->fetch()) {
      $allowedMembershipTypeIDs[] = $eventMembershipType->membership_type_id;
    }

    return $allowedMembershipTypeIDs;
  }

  /**
   * Gets the memberships for the specified
   * contact in case he has any active membership
   * with a membership type allowed to access the
   * provided members-only event.
   *
   * @param int $membersOnlyEventID
   * @param int $contactID
   *
   * @return array
   *   List of contact Memberships or empty array if nothing found
   */
  public static function getContactActiveAllowedMemberships($membersOnlyEventID, $contactID) {
    $params = [
      'sequential' => 1,
      'contact_id' => $contactID,
      'active_only' => 1,
    ];

    $allowedMembershipTypes = self::getAllowedMembershipTypeIDs($membersOnlyEventID);
    if (!empty($allowedMembershipTypes)) {
      $params['membership_type_id'] = ['IN' => $allowedMembershipTypes];
    }

    $contactActiveMemberships = civicrm_api3('Membership', 'get', $params);

    if ($contactActiveMemberships['count']) {
      return $contactActiveMemberships['values'];
    }

    return [];
  }

}
