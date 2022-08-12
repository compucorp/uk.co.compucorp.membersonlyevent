<?php

use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;

class CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent {

  /**
   * Fabricates a membership type.
   *
   * @param array $params
   *
   * @return CRM_MembersOnlyEvent_DAO_MembersOnlyEvent
   */
  public static function fabricate($params = []) {
    $params = array_merge(static::getDefaultParams(), $params);

    if (empty($params['event_id'])) {
      $event = CRM_MembersOnlyEvent_Test_Fabricator_Event::fabricate();
      $params['event_id'] = $event->id;
    }

    $membersOnlyEvent = new CRM_MembersOnlyEvent_DAO_MembersOnlyEvent();
    foreach ($params as $property => $value) {
      $membersOnlyEvent->$property = $value;
    }

    return $membersOnlyEvent->save();
  }

  private static function getDefaultParams() {
    return [
      'is_showing_custom_access_denied_message' => 1,
      'notice_for_access_denied' => 'Access Denied',
      'is_showing_login_block' => 1,
      'block_type' => MembersOnlyEvent::BLOCK_TYPE_LOGIN_ONLY,
      'login_block_message' => 'Please Login',
      'is_showing_purchase_membership_block' => 1,
      'purchase_membership_button_label' => 'Purchase membership to book the event',
      'purchase_membership_body_text' => 'Buy a membership',
      'purchase_membership_link_type' => 1,
      'contribution_page_id' => NULL,
      'purchase_membership_url' => NULL,
      'event_access_type' => MembersOnlyEvent::EVENT_ACCESS_TYPE_MEMBERS_ONLY,
    ];
  }

}
