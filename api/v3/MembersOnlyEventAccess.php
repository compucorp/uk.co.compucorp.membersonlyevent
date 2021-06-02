<?php

use CRM_MembersOnlyEvent_ExtensionUtil as E;
use CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess as MembersOnlyEventAccessService;

/**
 * MembersOnlyEventAccess.get API.
 *
 * @param array $params
 *   event_id
 *
 * @return array
 *   API result descriptor
 *
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_members_only_event_access_get($params) {
  $eventIDs = [];
  if (isset($params['event_id']) && is_int($params['event_id'])) {
    $eventIDs = [$params['event_id']];
  }

  if (isset($params['event_id']) && is_array($params['event_id'])) {
    $eventIDs = $params['event_id']['IN'];
  }

  $eventAccesses = MembersOnlyEventAccessService::getEventAccessDetails($eventIDs);

  return civicrm_api3_create_success($eventAccesses, $params);
}
