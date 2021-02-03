<?php

use CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess as MembersOnlyEventAccessService;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_Contact as ContactFabricator;

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
    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/event/info';
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService();
    $this->assertTrue($membersOnlyEventAccessService->isValidPath());

    CRM_Utils_GlobalStack::singleton()->pop();
  }

  /**
   * Tests IsValidPath is true for event registeration page
   */
  public function testIfEventRegisterationPageIsValidPath() {
    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/event/register';

    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService();
    $this->assertTrue($membersOnlyEventAccessService->isValidPath());

    CRM_Utils_GlobalStack::singleton()->pop();
  }

  /**
   * Tests IsValidPath is false for any url not event info page or registeration page
   */
  public function testIfNonEventInfoPageOrRegisterationPageIsInvalidPath() {
    $config = CRM_Core_Config::singleton();
    $tmpGlobals = [];
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/home';
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService();
    $this->assertFalse($membersOnlyEventAccessService->isValidPath());

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
    $tmpGlobals['_REQUEST']['id'] = $membersOnlyEvent->event_id;
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);

    $membersOnlyEventAccessService = new MembersOnlyEventAccessService();
    $this->assertEquals($membersOnlyEvent->id, $membersOnlyEventAccessService->getMembersOnlyEvent()->id);

    CRM_Utils_GlobalStack::singleton()->pop();
  }

}
