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
    $this->mockEventFeeWithValidPriceSet($eventID);
    $this->membersOnlyEventTab->_id = $eventID;
    $this->membersOnlyEventTab->buildQuickForm();
    $priceFieldsToHideElement = $this->membersOnlyEventTab->getElement('pricefields_to_hide');
    $this->assertTrue(is_object($priceFieldsToHideElement));
  }

  /**
   * Tests build form with invalid price set
   */
  public function testBuildFormWithInvalidPriceSet() {
    $eventID = $this->mockEvent();
    $this->membersOnlyEventTab->_id = $eventID;
    $this->mockEventFeeWithInvalidPriceSet($eventID);
    $this->membersOnlyEventTab->buildQuickForm();

    //Expect PEAR_Exception: QuickForm Error: nonexistent html element as pricefields_to_hide
    //should not be set when InvalidPriceSet for members only event is used.
    $this->expectException(PEAR_Exception::class);
    $priceFieldsToHideElement = $this->membersOnlyEventTab->getElement('pricefields_to_hide');
    $this->assertFalse(is_object($priceFieldsToHideElement));
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

  /**
   * Mocks Event Fee with Invalid Price Set
   *
   * @param $eventID
   */
  private function mockEventFeeWithInvalidPriceSet($eventID) {
    $priceSet = $this->mockPriceSet($eventID);
    $priceField = $this->mockPriceField($priceSet['id'], 'Select');
    $this->mockPriceValue($priceField['id']);
    $this->attachPriceSetToEvent($eventID, $priceSet['id']);
  }

  /**
   * Mocks Event Fee with Valid Price Set
   *
   * @param $eventID
   */
  private function mockEventFeeWithValidPriceSet($eventID) {
    $priceSet = $this->mockPriceSet($eventID);
    $priceField = $this->mockPriceField($priceSet['id']);
    $this->mockPriceValue($priceField['id']);
    $this->attachPriceSetToEvent($eventID, $priceSet['id']);
  }

  /**
   * @param $eventID
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function mockPriceSet($eventID) {
    $priceSetParams = [
      'name' => "test_price_set",
      'title' => "Test Price Set",
      'extends' => "CiviEvent",
      'financial_type_id' => "Event Fee",
      'is_active' => 1,
    ];
    $priceSet = CRM_MembersOnlyEvent_Test_Fabricator_PriceSet::fabricate($priceSetParams);

    return $priceSet;
  }

  /**
   * @param $priceSetID
   * @param string $htmlType
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function mockPriceField($priceSetID, $htmlType = 'Radio') {
    return CRM_MembersOnlyEvent_Test_Fabricator_PriceField::fabricate([
      'price_set_id' => $priceSetID,
      'label' => "Price Field 1",
      'name' => "price_field_1",
      'html_type' => $htmlType,
    ]);
  }

  /**
   * @param $priceFieldID
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function mockPriceValue($priceFieldID) {
    return CRM_MembersOnlyEvent_Test_Fabricator_PriceFieldValue::fabricate([
      'price_field_id' => $priceFieldID,
      'label' => "Price Field Value with Event Type 1",
      'amount' => 240,
      'financial_type_id' => "Event Fee",
    ]);
  }

  /**
   * @param $eventID
   * @param $priceSetID
   */
  private function attachPriceSetToEvent($eventID, $priceSetID) {
    CRM_Price_BAO_PriceSet::addTo('civicrm_event', $eventID, $priceSetID);
  }

}
