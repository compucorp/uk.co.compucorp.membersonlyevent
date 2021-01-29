<?php

use CRM_MembersOnlyEvent_DAO_MembersOnlyEventPriceFieldValue as MembersOnlyEventPriceFieldValue;

class CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEventPriceFieldValue {

  /**
   * Fabricates a members only event price field value.
   *
   * @param array $params
   * @param bool $saveObject
   *
   * @return \CRM_MembersOnlyEvent_BAO_MembersOnlyEventPriceFieldValue
   */
  public static function fabricate($params = [], $saveObject = FALSE) {
    $membersOnlyEventPriceFieldValue = new MembersOnlyEventPriceFieldValue();

    foreach ($params as $property => $value) {
      $membersOnlyEventPriceFieldValue->$property = $value;
    }

    if ($saveObject) {
      return $membersOnlyEventPriceFieldValue->save();
    }

    return $membersOnlyEventPriceFieldValue;
  }

}
