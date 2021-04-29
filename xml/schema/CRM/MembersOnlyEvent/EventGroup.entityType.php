<?php
// This file declares a new entity type. For more details, see "hook_civicrm_entityTypes" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
return [
  [
    'name' => 'EventGroup',
    'class' => 'CRM_MembersOnlyEvent_DAO_EventGroup',
    'table' => 'membersonlyevent_event_group',
  ],
];
