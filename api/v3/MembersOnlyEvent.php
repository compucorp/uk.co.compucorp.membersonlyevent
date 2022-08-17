<?php

use CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess as MembersOnlyEventAccessService;

/**
 * MembersOnlyEvent.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_members_only_event_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * MembersOnlyEvent.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_members_only_event_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * MembersOnlyEvent.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_members_only_event_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * MembersOnlyEvent.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_members_only_event_get($params) {
  $result = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  if ($result['count'] < 1) {
    return $result;
  }

  foreach ($result['values'] as $key => $value) {
    $membersOnlyEventAccessService = new MembersOnlyEventAccessService($value['event_id']);
    $membersOnlyEvent = $membersOnlyEventAccessService->prepareMembersOnlyEventForTemplate();

    $result['values'][$key]['is_showing_login_block'] = $membersOnlyEvent['is_showing_login_block'];
    $result['values'][$key]['login_block_content'] = $membersOnlyEvent['login_block_content'] ?? '';
    $result['values'][$key]['login_block_header'] = $membersOnlyEvent['login_block_header'] ?? '';
    $result['values'][$key]['purchase_membership_url'] = $membersOnlyEvent['purchase_membership_url'];
    $result['values'][$key]['is_user_allowed'] = $membersOnlyEventAccessService->userHasEventAccess();
  }

  return $result;
}
