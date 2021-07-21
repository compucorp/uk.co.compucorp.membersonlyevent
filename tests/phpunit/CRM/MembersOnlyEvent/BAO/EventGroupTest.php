<?php

use CRM_MembersOnlyEvent_Test_Fabricator_Group as GroupFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_SmartGroup as SmartGroupFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_BAO_EventGroup as EventGroup;
use CRM_MembersOnlyEvent_Test_Fabricator_Contact as ContactFabricator;

require_once __DIR__ . '/../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_BAO_MembersOnlyEventTest
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_BAO_EventGroupTest extends BaseHeadlessTest {

  /**
   * Tests updateAllowedGroups().
   */
  public function testUpdateAllowedGroups() {
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $group1 = GroupFabricator::fabricate(['title' => 'Student'], TRUE);
    $group2 = GroupFabricator::fabricate(['title' => 'Teacher'], TRUE);

    $groupIds = [$group1->id, $group2->id];

    EventGroup::updateAllowedGroups($membersOnlyEvent->id, $groupIds);

    $eventGroupDAO = new CRM_MembersOnlyEvent_DAO_EventGroup();
    $eventGroupDAO->members_only_event_id = $membersOnlyEvent->id;
    $eventGroupDAO->find();
    $eventGroups = [];
    while ($eventGroupDAO->fetch()) {
      $eventGroups[] = $eventGroupDAO->group_id;
    }

    $this->assertEquals(2, count($eventGroups));
    $this->assertEquals($groupIds, array_values($eventGroups));

  }

  /**
   * Tests getAllowedGroupIDs().
   */
  public function testGetAllowedGroupIDs() {
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $group = GroupFabricator::fabricate(['title' => 'Student'], TRUE);
    EventGroup::updateAllowedGroups($membersOnlyEvent->id, [$group->id]);
    $eventGroupIds = EventGroup::getAllowedGroupIDs($membersOnlyEvent->id);

    $this->assertEquals([$group->id], $eventGroupIds);

  }

  /**
   * Tests getContactAllowedGroups().
   */
  public function testIfGetContactAllowedGroupsReturnsNothingIfNoAllowedGroupsAreConfigured() {
    $params = [
      'first_name' => 'John',
      'last_name' => 'Doe',
    ];
    $contactID = ContactFabricator::fabricate($params)['id'];
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $studentsGroup = GroupFabricator::fabricate(['title' => 'Students'], TRUE);

    $params = [
      'contact_id' => $contactID,
      'group_id' => $studentsGroup->id,
      'status' => 'Added',
    ];
    $contactGroups = civicrm_api3('GroupContact', 'create', $params);

    $contactGroupIds = EventGroup::getContactAllowedGroups($membersOnlyEvent->id, $contactID);
    $this->assertEquals([], $contactGroupIds);
  }

  /**
   * Tests getContactAllowedGroups().
   */
  public function testIfGetContactAllowedGroupsReturnsNothingIfAllowedGroupsAreConfiguredButTheContactIsNotPartOfAnyOfThem() {
    $params = [
      'first_name' => 'John',
      'last_name' => 'Doe',
    ];
    $contactID = ContactFabricator::fabricate($params)['id'];
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $studentsGroup = GroupFabricator::fabricate(['title' => 'Students'], TRUE);
    EventGroup::updateAllowedGroups($membersOnlyEvent->id, [$studentsGroup->id]);

    $contactGroupIds = EventGroup::getContactAllowedGroups($membersOnlyEvent->id, $contactID);
    $this->assertEquals([], $contactGroupIds);
  }

  /**
   * Tests getContactAllowedGroups().
   */
  public function testIfGetContactAllowedGroupsWorksWithNormalGroups() {
    $params = [
      'first_name' => 'John',
      'last_name' => 'Doe',
    ];
    $contactID = ContactFabricator::fabricate($params)['id'];
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $studentsGroup = GroupFabricator::fabricate(['title' => 'Students'], TRUE);
    EventGroup::updateAllowedGroups($membersOnlyEvent->id, [$studentsGroup->id]);

    $params = [
      'contact_id' => $contactID,
      'group_id' => $studentsGroup->id,
      'status' => 'Added',
    ];
    $contactGroups = civicrm_api3('GroupContact', 'create', $params);

    $contactGroupIds = EventGroup::getContactAllowedGroups($membersOnlyEvent->id, $contactID);
    $this->assertEquals([$studentsGroup->id], $contactGroupIds);
  }

  /**
   * Tests getContactAllowedGroups().
   */
  public function testIfGetContactAllowedGroupsWorksWithSmartGroups() {
    $params = [
      'first_name' => 'John',
      'last_name' => 'Doe',
    ];
    $contactID = ContactFabricator::fabricate($params)['id'];
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $smartGroup = SmartGroupFabricator::fabricate(['formValues' => ['sort_name' => 'Doe, John']]);
    EventGroup::updateAllowedGroups($membersOnlyEvent->id, [$smartGroup->id]);

    $contactGroupIds = EventGroup::getContactAllowedGroups($membersOnlyEvent->id, $contactID);
    $this->assertEquals([$smartGroup->id], $contactGroupIds);
  }

}
