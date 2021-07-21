<?php

class CRM_MembersOnlyEvent_Utils_Array {

  /**
   * Creates an array composed of keys generated from the result of running
   * each element of $items thru the foreach and the value is the corresponding
   * value of each key.
   *
   * @param array $items
   * @param string $key
   */
  public static function keyBy(array $items, string $key) {
    $result = [];
    foreach ($items as $item) {
      $result[$item[$key]] = $item;
    }

    return $result;
  }

}
