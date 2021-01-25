<?php

use CRM_MembersOnlyEvent_BAO_NonMemberPriceFieldValue as NonMemberPriceFieldValue;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceFieldValue as PriceFieldValueFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceField as PriceFieldFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceSet as PriceSetFabricator;

require_once __DIR__ . '/../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_BAO__NonMemberPriceFieldValue
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_BAO_NonMemberPriceFieldValueTest extends BaseHeadlessTest {

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
    $priceSet = PriceSetFabricator::fabricate($priceSetParams);

    $this->attachPriceSetToEvent($eventID, $priceSet['id']);

    return $priceSet;
  }

  private function mockPriceField($eventID) {
    $priceSet = $this->mockPriceSet($eventID);

    $priceField = PriceFieldFabricator::fabricate([
      'price_set_id' => $priceSet['id'],
      'label' => "Price Field",
      'name' => "price_field",
      'html_type' => 'Radio',
    ]);

    return $priceField;
  }

  private function mockPriceFieldValue($params) {
    $priceField = $this->mockPriceField($params['event_id']);
    $params = array_merge($params, ['price_field_id' => $priceField['id']]);
    $priceFieldValue = PriceFieldValueFabricator::fabricate($params);

    return $priceFieldValue;
  }

  /**
   * @param $eventID
   * @param $priceSetID
   */
  private function attachPriceSetToEvent($eventID, $priceSetID) {
    CRM_Price_BAO_PriceSet::addTo('civicrm_event', $eventID, $priceSetID);
  }

  /**
   * Tests updateAllowedMembershipTypes().
   */
  public function testUpdateNonMemberFields() {
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $nonMemberPriceFieldValue = $this->mockPriceFieldValue([
      'event_id' => $membersOnlyEvent->event_id,
      'label' => "Non-member Fee",
    ]);

    NonMemberPriceFieldValue::updateNonMemberFieldValues($membersOnlyEvent->id, [$nonMemberPriceFieldValue['id']]);

    $nonMemberPriceFieldValueDAO = new CRM_MembersOnlyEvent_DAO_NonMemberPriceFieldValue();
    $nonMemberPriceFieldValueDAO->members_only_event_id = $membersOnlyEvent->id;
    $nonMemberPriceFieldValueDAO->find();
    $eventMembershipTypes = [];
    while ($nonMemberPriceFieldValueDAO->fetch()) {
      $eventMembershipTypes[] = $nonMemberPriceFieldValueDAO->price_field_value_id;
    }

    $this->assertEquals(1, count($eventMembershipTypes));
    $this->assertEquals([$nonMemberPriceFieldValue['id']], array_values($eventMembershipTypes));
  }

  /**
   * Tests getAllowedMembershipTypesIDs().
   */
  public function testGetAllowedMembershipTypesIDs() {
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $nonMemberPriceFieldValue = $this->mockPriceFieldValue([
      'event_id' => $membersOnlyEvent->event_id,
      'label' => "Non-member Fee",
    ]);

    NonMemberPriceFieldValue::updateNonMemberFieldValues($membersOnlyEvent->id, [$nonMemberPriceFieldValue['id']]);

    $nonMemberFieldValueIDs = NonMemberPriceFieldValue::getNonMemberFieldValueIDs($membersOnlyEvent->id);

    $this->assertEquals([$nonMemberPriceFieldValue['id']], $nonMemberFieldValueIDs);
  }

}
