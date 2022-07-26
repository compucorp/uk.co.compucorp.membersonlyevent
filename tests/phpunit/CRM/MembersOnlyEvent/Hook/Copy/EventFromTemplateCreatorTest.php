<?php

use CRM_MembersOnlyEvent_Test_Fabricator_Event as EventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;
use CRM_MembersOnlyEvent_Test_Fabricator_MembershipType as MembershipTypeFabricator;
use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;

require_once __DIR__ . '/../../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_BAO_MembersOnlyEventTest
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_Hook_Copy_EventFromTemplateCreatorTest extends BaseHeadlessTest {

  private $eventTemplate;

  private $membersOnlyEventTemplate;

  private $event;

  public function setUp() {
    $this->eventTemplate = EventFabricator::fabricate(['is_template' => TRUE]);
    $this->membersOnlyEventTemplate = MembersOnlyEventFabricator::fabricate(['event_id' => $this->eventTemplate->id]);
    $this->event = EventFabricator::fabricate();

  }

  /**
   * Tests Create MemberOnlyEvent when new Event was created from template.
   */
  public function testCreateEventFromTemplate() {
    $eventFromTemplateCreator = new CRM_MembersOnlyEvent_Hook_Copy_EventFromTemplateCreator($this->event->id, $this->eventTemplate->id);
    $eventFromTemplateCreator->create();

    $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($this->event->id);

    $templateKeys = [
      'contribution_page_id',
      'is_showing_custom_access_denied_message',
      'notice_for_access_denied',
      'is_showing_login_block',
      'block_type',
      'login_block_message',
      'is_showing_purchase_membership_block',
      'purchase_membership_button_label',
      'purchase_membership_body_text',
      'purchase_membership_link_type',
      'purchase_membership_url',
      'event_access_type',
    ];
    foreach ($templateKeys as $templateKey) {
      $this->assertEquals($this->membersOnlyEventTemplate->{$templateKey}, $membersOnlyEvent->{$templateKey});
    }
  }

  public function testCreateEventFromTemplateWithMembershipType() {
    $membershipType1 = MembershipTypeFabricator::fabricate(['name' => 'Student'], TRUE);
    $membershipType2 = MembershipTypeFabricator::fabricate(['name' => 'Teacher'], TRUE);
    $membershipTypeIds = [$membershipType1->id, $membershipType2->id];
    EventMembershipType::updateAllowedMembershipTypes($this->membersOnlyEventTemplate->id, $membershipTypeIds);

    $eventId = $this->event->id;
    $templateId = $this->eventTemplate->id;
    $eventFromTemplateCreator = new CRM_MembersOnlyEvent_Hook_Copy_EventFromTemplateCreator($eventId, $templateId);
    $eventFromTemplateCreator->create();

    $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($eventId);

    $eventMembershipTypeDAO = new CRM_MembersOnlyEvent_DAO_EventMembershipType();
    $eventMembershipTypeDAO->members_only_event_id = $membersOnlyEvent->id;
    $eventMembershipTypeDAO->find();
    $eventMembershipTypes = [];
    while ($eventMembershipTypeDAO->fetch()) {
      $eventMembershipTypes[] = $eventMembershipTypeDAO->membership_type_id;
    }
    $this->assertEquals($membershipTypeIds, array_values($eventMembershipTypes));

  }

}
