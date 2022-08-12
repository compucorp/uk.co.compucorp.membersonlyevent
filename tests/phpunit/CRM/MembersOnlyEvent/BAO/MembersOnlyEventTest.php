<?php

use CRM_MembersOnlyEvent_Test_Fabricator_Event as EventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;

require_once __DIR__ . '/../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_BAO_MembersOnlyEventTest
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_BAO_MembersOnlyEventTest extends BaseHeadlessTest {

  /**
   * Tests Create function().
   */
  public function testCreate() {
    $event = EventFabricator::fabricate();

    $params = [
      'event_id' => $event->id,
      'is_showing_custom_access_denied_message' => 1,
      'notice_for_access_denied' => '<p>Access Denied</p>',
      'is_showing_login_block' => 1,
      'block_type' => MembersOnlyEvent::BLOCK_TYPE_LOGIN_ONLY,
      'login_block_message' => '<p>Please login</p>',
      'is_showing_purchase_membership_block' => 1,
      'purchase_membership_button_label' => 'Purchase membership to book the event',
      'purchase_membership_body_text' => '<p>Buy a membership</p>',
      'purchase_membership_link_type' => 1,
      'contribution_page_id' => NULL,
      'purchase_membership_url' => NULL,
      'event_access_type' => MembersOnlyEvent::EVENT_ACCESS_TYPE_MEMBERS_ONLY,
    ];

    $memberOnlyEvent = MembersOnlyEvent::create($params);
    $this->assertNotNull($memberOnlyEvent->id);
  }

  /**
   * Test GetMembersOnlyEvent function().
   */
  public function testGetMembersOnlyEvent() {
    $fabricatedMembersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $memberOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($fabricatedMembersOnlyEvent->event_id);
    $this->assertEquals($fabricatedMembersOnlyEvent->id, $memberOnlyEvent->id);
  }

}
