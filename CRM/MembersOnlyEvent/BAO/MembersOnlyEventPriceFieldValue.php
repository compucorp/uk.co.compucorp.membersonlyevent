<?php
use CRM_MembersOnlyEvent_ExtensionUtil as E;

class CRM_MembersOnlyEvent_BAO_MembersOnlyEventPriceFieldValue extends CRM_MembersOnlyEvent_DAO_MembersOnlyEventPriceFieldValue {

  /**
   * Create a new MembersOnlyEventPriceFieldValue based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_MembersOnlyEvent_DAO_MembersOnlyEventPriceFieldValue|NULL
   */
  public static function create($params) {
    $entityName = 'MembersOnlyEventPriceFieldValue';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new CRM_MembersOnlyEvent_DAO_MembersOnlyEventPriceFieldValue();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Stores the non-member price field values
   *
   * @param int $membersOnlyEventID
   * @param array $priceFieldValueIDs
   */
  public static function updateNonMemberPriceFieldValues($membersOnlyEventID, $priceFieldValueIDs) {
    $transaction = new CRM_Core_Transaction();

    $removeResponse = self::removeNonMemberPriceFieldValues($membersOnlyEventID);
    $createResponse = self::createNonMemberPriceFieldValues($membersOnlyEventID, $priceFieldValueIDs);

    if ($removeResponse === FALSE || $createResponse === FALSE) {
      $transaction->rollback();
    }
    else {
      $transaction->commit();
    }
  }

  /**
   * Removes all non-member price field values for
   * the provided members-only event.
   *
   * @param int $membersOnlyEventID
   */
  private static function removeNonMemberPriceFieldValues($membersOnlyEventID) {
    $membersOnlyEventPriceFieldValue = new self();
    $membersOnlyEventPriceFieldValue->members_only_event_id = $membersOnlyEventID;
    $membersOnlyEventPriceFieldValue->delete();
  }

  /**
   * Sets the non-member price field values for
   * for the provided members-only event.
   *
   * @param int $membersOnlyEventID
   * @param array $priceFieldValueIDs
   *
   * @return boolean
   *   True if the creation of all non-member price field values
   *   succeed or False otherwise.
   */
  private static function createNonMemberPriceFieldValues($membersOnlyEventID, $priceFieldValueIDs) {
    $createdRecordsCount = 0;
    foreach ($priceFieldValueIDs as $priceFieldValueID) {
      $membersOnlyEventPriceFieldValue = new self();
      $membersOnlyEventPriceFieldValue->members_only_event_id = $membersOnlyEventID;
      $membersOnlyEventPriceFieldValue->price_field_value_id = $priceFieldValueID;
      $membersOnlyEventPriceFieldValue->save();
      $createdRecordsCount++;
    }

    return $createdRecordsCount == count($priceFieldValueIDs);
  }

  /**
   * Gets the non-member price field values  for specific
   * members-only event.
   *
   * @param int $membersOnlyEventID
   *
   * @return array
   *   The IDs of non-member price field values
   */
  public static function getNonMemberPriceFieldValueIDs($membersOnlyEventID) {
    $membersOnlyEventPriceFieldValue = new self();
    $membersOnlyEventPriceFieldValue->members_only_event_id = $membersOnlyEventID;
    $membersOnlyEventPriceFieldValue->find();

    $priceFieldValueIDs = array();
    while ($membersOnlyEventPriceFieldValue->fetch()) {
      $priceFieldValueIDs[] = $membersOnlyEventPriceFieldValue->price_field_value_id;
    }

    return $priceFieldValueIDs;
  }

}
