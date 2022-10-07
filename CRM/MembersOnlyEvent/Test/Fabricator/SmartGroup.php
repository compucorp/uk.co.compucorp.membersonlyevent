<?php

use CRM_Contact_BAO_Group as Group;

class CRM_MembersOnlyEvent_Test_Fabricator_SmartGroup {

  /**
   * Fabricates a group.
   *
   * @param array $params
   *
   * @return \CRM_Contact_BAO_SmartGroup
   */
  public static function fabricate($params = []) {
    $params = array_merge(static::getDefaultParams(), $params);

    if (empty($params['name'])) {
      $params['name'] = md5(mt_rand());
      $params['title'] = $params['name'];
    }

    $group = Group::createSmartGroup($params);

    // Rebuilding the group to add the matching contacts
    // to it.
    civicrm_api3('Job', 'group_rebuild');

    return $group;
  }

  private static function getDefaultParams() {
    return [
      'title' => 'SmartGroup A',
      'description' => 'SmartGroup A description',
      'source' => 'Test',
      'is_active' => '1',
      'visibility' => 'User and User Admin Only',
      'group_type' => 1,
      'is_hidden' => 0,
      'is_reserved' => 0,
      'frontend_title' => 'SmartGroup A, public name',
      'frontend_description' => 'SmartGroup A, public description',
      'formValues' => ['sort_name' => 'Doe'],
    ];
  }

}
