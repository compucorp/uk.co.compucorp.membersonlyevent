<?php
use CRM_MembersOnlyEvent_ExtensionUtil as E;

class CRM_MembersOnlyEvent_BAO_MembersOnlyEventSelectPriceField extends CRM_MembersOnlyEvent_DAO_MembersOnlyEventSelectPriceField {

  /**
   * Create a new MembersOnlyEventSelectPriceField based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_MembersOnlyEvent_DAO_MembersOnlyEventSelectPriceField|NULL
   */
  public static function create($params) {
    $entityName = 'MembersOnlyEventSelectPriceField';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new CRM_MembersOnlyEvent_DAO_MembersOnlyEventSelectPriceField();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

}
