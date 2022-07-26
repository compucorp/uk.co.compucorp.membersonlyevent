<?php

use CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess as MembersOnlyEventAccessService;
use CRM_MembersOnlyEvent_Test_Fabricator_Event as EventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_Contact as ContactFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_MembershipType as MembershipTypeFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_Membership as MembershipFabricator;
use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;

require_once __DIR__ . '/../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_BAO__MembersOnlyEventPriceFieldValue
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_Service_MembersOnlyEventAccessTest extends BaseHeadlessTest {

  /**
   * @param int $value
   *
   * @throws \CiviCRM_API3_Exception
   */
  public function setMembershipDurationCheckSettings($value) {
    civicrm_api3('Setting', 'create', [
      'membership_duration_check' => $value,
    ]);
  }

  /**
   * @return int
   * @throws \CiviCRM_API3_Exception
   */
  public function createLoggedInUser() {
    $contactID = ContactFabricator::fabricate()['id'];

    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
    return $contactID;
  }

  /**
   * Tests IsValidPath is true for event info page
   */
  public function testIfEventInfoPageIsValidPath() {
    $event = EventFabricator::fabricate();
    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/event/info';
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService($event->id);
    $this->assertTrue($membersOnlyEventAccessService->isValidPath());

    CRM_Utils_GlobalStack::singleton()->pop();
  }

  /**
   * Tests IsValidPath is true for event registeration page
   */
  public function testIfEventRegisterationPageIsValidPath() {
    $event = EventFabricator::fabricate();
    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/event/register';

    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService($event->id);
    $this->assertTrue($membersOnlyEventAccessService->isValidPath());

    CRM_Utils_GlobalStack::singleton()->pop();
  }

  /**
   * Tests IsValidPath is false for any url not event info page or registeration page
   */
  public function testIfNonEventInfoPageOrRegisterationPageIsInvalidPath() {
    $event = EventFabricator::fabricate();
    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/home';
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService($event->id);
    $this->assertFalse($membersOnlyEventAccessService->isValidPath());

    CRM_Utils_GlobalStack::singleton()->pop();
  }

  /**
   * Tests hasMembership for logged user with valid membership
   */
  public function testLoggedUserWithValidMembership() {
    $contactID = $this->createLoggedInUser();
    $this->setMembershipDurationCheckSettings(0);

    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $membershipType = MembershipTypeFabricator::fabricate(['name' => 'Student'], TRUE);
    EventMembershipType::updateAllowedMembershipTypes($membersOnlyEvent->id, [$membershipType->id]);

    $membership = MembershipFabricator::fabricate([
      'contact_id' => $contactID,
      'membership_type_id' => $membershipType->id,
      'join_date' => date('Y-m-01', strtotime('first day of january this year')),
      'start_date' => date('Y-m-01', strtotime('first day of january this year')),
      'financial_type_id' => 'Member Dues',
      'skipLineItem' => 1,
    ]);

    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/event/register';
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService($membersOnlyEvent->event_id);

    $this->assertTrue($membersOnlyEventAccessService->hasMembership());

    CRM_Utils_GlobalStack::singleton()->pop();
  }

  /**
   * Tests authenticated only 'event access type' for anonymous user.
   */
  public function testAnonymousUserWithAuthenticatedOnlyEvent() {
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate([
      'event_access_type' => MembersOnlyEvent::EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY,
    ]);

    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/event/register';
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService($membersOnlyEvent->event_id);

    $this->assertFalse($membersOnlyEventAccessService->userHasEventAccess());

    CRM_Utils_GlobalStack::singleton()->pop();
  }

  /**
   * Tests authenticated only 'event access type' for logged user.
   */
  public function testLoggedUserWithAuthenticatedOnlyEvent() {
    $this->createLoggedInUser();

    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate([
      'event_access_type' => MembersOnlyEvent::EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY,
    ]);

    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/event/register';
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService($membersOnlyEvent->event_id);

    $this->assertTrue($membersOnlyEventAccessService->userHasEventAccess());

    CRM_Utils_GlobalStack::singleton()->pop();
  }

  /**
   * Tests getMembersOnlyEvent().
   */
  public function testGetMembersOnlyEvent() {
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();

    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/event/register';
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService($membersOnlyEvent->event_id);
    $this->assertEquals($membersOnlyEvent->id, $membersOnlyEventAccessService->getMembersOnlyEvent()->id);

    CRM_Utils_GlobalStack::singleton()->pop();
  }

}
