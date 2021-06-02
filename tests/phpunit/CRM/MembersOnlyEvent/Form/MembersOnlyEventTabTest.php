<?php

require_once __DIR__ . '/../../../BaseHeadlessTest.php';

/**
 * CRM_MembersOnlyEvent_Form_MembersOnlyEventTabTest
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_Form_MembersOnlyEventTabTest extends BaseHeadlessTest {

  /**
   * @var CRM_MembersOnlyEvent_Form_MembersOnlyEventTab
   */
  private $membersOnlyEventTab;

  public function setUp() {
    $formController = new CRM_Core_Controller();
    $this->membersOnlyEventTab = new CRM_MembersOnlyEvent_Form_MembersOnlyEventTab();
    $this->membersOnlyEventTab->controller = $formController;
  }

  /**
   * Tests build form with valid price set
   */
  public function testBuildFromWithValidPriceSet() {
    $eventID = $this->mockEvent();
    $this->membersOnlyEventTab->_id = $eventID;
    $this->membersOnlyEventTab->buildQuickForm();
    $isGroupsOnlyEventElement = $this->membersOnlyEventTab->getElement('event_access_type');
    $this->assertTrue(is_object($isGroupsOnlyEventElement));
  }

  /**
   * Mocks and event for testing
   *
   * @return int
   */
  private function mockEvent() {
    $event = CRM_MembersOnlyEvent_Test_Fabricator_Event::fabricate([
      'is_monetary' => 1,
      'is_online_registration' => 1,
      'title' => 'Test Event',
    ]);

    return $event->id;
  }

}
