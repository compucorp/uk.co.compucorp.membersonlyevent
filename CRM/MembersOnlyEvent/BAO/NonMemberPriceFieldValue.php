<?php
use CRM_MembersOnlyEvent_ExtensionUtil as E;

class CRM_MembersOnlyEvent_BAO_NonMemberPriceFieldValue extends CRM_MembersOnlyEvent_DAO_NonMemberPriceFieldValue {

  /**
   * Create a new NonMemberPriceFieldValue based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_MembersOnlyEvent_DAO_NonMemberPriceFieldValue|NULL
   */
  public static function create($params) {
    $entityName = 'NonMemberPriceFieldValue';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new CRM_MembersOnlyEvent_DAO_NonMemberPriceFieldValue();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Stores the member only field values
   * members-only event
   *
   * @param int $membersOnlyEventID
   * @param array $nonMemberFieldValueIDs
   */
  public static function updateNonMemberFieldValues($membersOnlyEventID, $nonMemberFieldValueIDs) {
    $transaction = new CRM_Core_Transaction();

    $removeResponse = self::removeNonMemberFieldValues($membersOnlyEventID);
    $createResponse = self::createNonMemberFieldValues($membersOnlyEventID, $nonMemberFieldValueIDs);

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
  private static function removeNonMemberFieldValues($membersOnlyEventID) {
    $membership_type = new self();
    $membership_type->members_only_event_id = $membersOnlyEventID;
    $membership_type->delete();
  }

  /**
   * Sets the allowed membership types
   * for the provided members-only event.
   *
   * @param int $membersOnlyEventID
   * @param array $nonMemberFieldValueIDs
   *
   * @return boolean
   *   True if the creation of all allowed membership
   *   types succeed or False otherwise.
   */
  private static function createNonMemberFieldValues($membersOnlyEventID, $nonMemberFieldValueIDs) {
    $createdRecordsCount = 0;
    foreach ($nonMemberFieldValueIDs as $nonMemberFieldValueID) {
      $eventMembershipType = new self();
      $eventMembershipType->members_only_event_id = $membersOnlyEventID;
      $eventMembershipType->price_field_value_id = $nonMemberFieldValueID;
      $eventMembershipType->save();
      $createdRecordsCount++;
    }

    return $createdRecordsCount == count($nonMemberFieldValueIDs);
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
  public static function getNonMemberFieldValueIDs($membersOnlyEventID) {
    $eventMembershipType = new self();
    $eventMembershipType->members_only_event_id = $membersOnlyEventID;
    $eventMembershipType->find();

    $nonMemberFieldValueIDs = array();
    while ($eventMembershipType->fetch()) {
      $nonMemberFieldValueIDs[] = $eventMembershipType->price_field_value_id;
    }

    return $nonMemberFieldValueIDs;
  }

}
