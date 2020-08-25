<?php

use CRM_MembersOnlyEvent_Test_Fabricator_MembershipType as MembershipTypeFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;

require_once __DIR__ . '/../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_BAO_MembersOnlyEventTest
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_BAO_EventMembershipTypeTest extends BaseHeadlessTest {

  /**
   * Tests updateAllowedMembershipTypes().
   */
  public function testUpdateAllowedMembershipTypes() {
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $membershipType1 = MembershipTypeFabricator::fabricate([], TRUE);
    $membershipType2 = MembershipTypeFabricator::fabricate([], TRUE);

    $membershipTypeIds = [$membershipType1->id, $membershipType2->id];

    EventMembershipType::updateAllowedMembershipTypes($membersOnlyEvent->event_id, $membershipTypeIds);

    $eventMembershipTypeDAO = new CRM_MembersOnlyEvent_DAO_EventMembershipType();
    $eventMembershipTypeDAO->members_only_event_id = $membersOnlyEvent->id;
    $eventMembershipTypeDAO->find();
    $eventMembershipTypes = [];
    while ($eventMembershipTypeDAO->fetch()) {
      $eventMembershipTypes[] = $eventMembershipTypeDAO->membership_type_id;
    }

    $this->assertEquals(2, count($eventMembershipTypes));
    $this->assertEquals($membershipTypeIds, array_values($eventMembershipTypes));

  }

  /**
   * Tests getAllowedMembershipTypesIDs().
   */
  public function testGetAllowedMembershipTypesIDs() {
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $membershipType = MembershipTypeFabricator::fabricate([], TRUE);
    EventMembershipType::updateAllowedMembershipTypes($membersOnlyEvent->event_id, [$membershipType->id]);
    $eventMembershipTypeIds = EventMembershipType::getAllowedMembershipTypesIDs($membersOnlyEvent->id);

    $this->assertEquals([$membershipType->id], $eventMembershipTypeIds);

  }

}
