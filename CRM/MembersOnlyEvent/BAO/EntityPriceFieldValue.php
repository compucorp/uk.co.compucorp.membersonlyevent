<?php

use CRM_MembersOnlyEvent_ExtensionUtil as E;

class CRM_MembersOnlyEvent_BAO_EntityPriceFieldValue extends CRM_MembersOnlyEvent_DAO_EntityPriceFieldValue {

  /**
   * Create a new EntityPriceFieldValue based on array-data
   *
   * @param array $params key-value pairs
   *
   * @return CRM_MembersOnlyEvent_DAO_EntityPriceFieldValue|NULL
   */
  public static function create($params) {
    $className = 'CRM_MembersOnlyEvent_DAO_EntityPriceFieldValue';
    $entityName = 'EntityPriceFieldValue';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * @param $priceFieldValueID
   * @param $entityTable
   * @param $entityIDs
   */
  public static function updateEntityPriceFieldValues($priceFieldValueID, $entityTable, $entityIDs) {
    $transaction = new CRM_Core_Transaction();

    $removeResponse = self::removeEntityPriceFieldValues($priceFieldValueID);
    $createResponse = self::createEntityPriceFieldValues($priceFieldValueID, $entityTable, $entityIDs);

    if ($removeResponse === FALSE || $createResponse === FALSE) {
      $transaction->rollback();
    }
    else {
      $transaction->commit();
    }
  }

  /**
   * @param $priceFieldValueID
   */
  private static function removeEntityPriceFieldValues($priceFieldValueID) {
    $entityPriceFieldValue = new self();
    $entityPriceFieldValue->price_field_value_id = $priceFieldValueID;
    return $entityPriceFieldValue->delete();
  }

  /**
   * @param $priceFieldValueID
   * @param $entityTable
   * @param $entityIDs
   *
   * @return bool
   */
  private static function createEntityPriceFieldValues($priceFieldValueID, $entityTable, $entityIDs) {
    $createdRecordsCount = 0;
    foreach ($entityIDs as $entityID) {
      $entityPriceFieldValue = new self();
      $entityPriceFieldValue->entity_table = $entityTable;
      $entityPriceFieldValue->entity_id = $entityID;
      $entityPriceFieldValue->price_field_value_id = $priceFieldValueID;
      $entityPriceFieldValue->save();
      $createdRecordsCount++;
    }

    return $createdRecordsCount == count($entityIDs);
  }

  /**
   * @param $priceFieldValueID
   *
   * @return array
   */
  public static function getEntityPriceFieldValueIDs($priceFieldValueID) {
    $entityPriceFieldValue = new self();
    $entityPriceFieldValue->price_field_value_id = $priceFieldValueID;
    $entityPriceFieldValue->find();

    $entityIDs = [];
    $entityTable = [];
    while ($entityPriceFieldValue->fetch()) {
      $entityIDs[] = $entityPriceFieldValue->entity_id;
      $entityTable = $entityPriceFieldValue->entity_table;
    }

    return [$entityTable, $entityIDs];
  }

}
