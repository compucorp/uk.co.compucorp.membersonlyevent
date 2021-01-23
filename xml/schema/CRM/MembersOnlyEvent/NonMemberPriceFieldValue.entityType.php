<?php
// This file declares a new entity type. For more details, see "hook_civicrm_entityTypes" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
return [
  [
    'name' => 'NonMemberPriceFieldValue',
    'class' => 'CRM_MembersOnlyEvent_DAO_NonMemberPriceFieldValue',
    'table' => 'membersonlyevent_non_member_price_field_value',
  ],
];
