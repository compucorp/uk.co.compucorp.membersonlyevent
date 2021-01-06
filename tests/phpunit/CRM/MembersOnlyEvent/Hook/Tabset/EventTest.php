<?php

use CRM_MembersOnlyEvent_Test_Fabricator_Event as EventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;

require_once __DIR__ . '/../../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_Hook_Tabset_EventTest
 */
class CRM_MembersOnlyEvent_Hook_Tabset_EventTest extends BaseHeadlessTest {

  /**
   * Tests valid members only event for tab set hook
   * when event online registration is enable
   */
  public function testValidMembersOnlyEventTabSet() {
    $event = EventFabricator::fabricate(['is_online_registration' => TRUE]);
    MembersOnlyEventFabricator::fabricate(['event_id' => $event->id]);
    $evenTabset = new CRM_MembersOnlyEvent_Hook_Tabset_Event();
    $tabsets = [];
    $evenTabset->handle($event->id, $tabsets);
    $this->assertArrayHasKey('membersonlyevent', $tabsets);
    $this->assertTrue($tabsets['membersonlyevent']['valid']);
  }

  /**
   * Tests invalid members only event for tab set hook
   * when event online registration is not enabled
   */
  public function testInValidMemberOnlyEventTabSet() {
    $event = EventFabricator::fabricate(['is_online_registration' => FALSE]);
    $membersOnlyEventTemplate = MembersOnlyEventFabricator::fabricate(['event_id' => $event->id]);
    $tabsets = [];
    $evenTabset = new CRM_MembersOnlyEvent_Hook_Tabset_Event();
    $evenTabset->handle($event->id, $tabsets);
    $this->assertArrayHasKey('membersonlyevent', $tabsets);
    $this->assertFalse($tabsets['membersonlyevent']['valid']);
  }

}
