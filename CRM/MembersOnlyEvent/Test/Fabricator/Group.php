<?php

use CRM_Contact_DAO_Group as Group;

class CRM_MembersOnlyEvent_Test_Fabricator_Group {

  /**
   * Fabricates a group.
   *
   * @param array $params
   * @param bool $saveObject
   *
   * @return \CRM_Contact_BAO_Group
   */
  public static function fabricate($params = [], $saveObject = FALSE) {
    $params = array_merge(static::getDefaultParams(), $params);

    $group = new Group();

    if (empty($params['name'])) {
      $params['name'] = md5(mt_rand());
      $params['title'] = $params['name'];
    }

    foreach ($params as $property => $value) {
      $group->$property = $value;
    }

    if ($saveObject) {
      return $group->save();
    }

    return $group;
  }

  private static function getDefaultParams() {
    return [
      'title' => 'Group A',
      'description' => 'Group A description',
      'source' => 'Test',
      'is_active' => '1',
      'visibility' => 'User and User Admin Only',
      'group_type' => 1,
      'is_hidden' => 0,
      'is_reserved' => 0,
      'frontend_title' => 'Group A, public name',
      'frontend_description' => 'Group A, public description',
    ];
  }

}
