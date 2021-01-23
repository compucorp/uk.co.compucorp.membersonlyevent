<?php

use CRM_MembersOnlyEvent_DAO_NonMemberPriceFieldValue as NonMemberPriceFieldValue;

class CRM_MembersOnlyEvent_Test_Fabricator_NonMemberPriceFieldValue {

  /**
   * Fabricates a members only event select price field type.
   *
   * @param array $params
   * @param bool $saveObject
   *
   * @return \CRM_MembersOnlyEvent_BAO_NonMemberPriceFieldValue
   */
  public static function fabricate($params = [], $saveObject = FALSE) {
    $nonMemberPriceFieldValue = new NonMemberPriceFieldValue();

    foreach ($params as $property => $value) {
      $nonMemberPriceFieldValue->$property = $value;
    }

    if ($saveObject) {
      return $nonMemberPriceFieldValue->save();
    }

    return $nonMemberPriceFieldValue;
  }

}

