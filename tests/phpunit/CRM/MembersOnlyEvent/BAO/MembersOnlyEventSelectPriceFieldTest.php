<?php

use CRM_MembersOnlyEvent_BAO_MembersOnlyEventSelectPriceField as MembersOnlyEventSelectPriceField;
use CRM_MembersOnlyEvent_Test_Fabricator_Event as EventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceFieldValue as PriceFieldValueFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceField as PriceFieldFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceSet as PriceSetFabricator;


require_once __DIR__ . '/../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_BAO__MembersOnlyEventSelectPriceFieldTest
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_BAO_MembersOnlyEventSelectPriceFieldTest extends BaseHeadlessTest {

  /**
   * Tests Create function().
   */
  public function testCreate() {
    $event = EventFabricator::fabricate(['is_monetary' => 1,
      'is_online_registration' => 1,
      'title' => 'Test Event'
    ]);

    $priceFields = $this->mockPriceFields($event->id);
    $membersOnlyEvent = MembersOnlyEventFabricator::fabricate();

    $priceFieldIds = [];
    foreach ($priceFields as $priceField) {
      $params = [
        'members_only_event_id' => $membersOnlyEvent->id,
        'price_field_id' => $priceField['id'],
      ];
      MembersOnlyEventSelectPriceField::create($params);
      $priceFieldIds[] = $priceField['id'];
    }

    $membersOnlyEventSelectPriceFieldDAO = new CRM_MembersOnlyEvent_DAO_MembersOnlyEventSelectPriceField();
    $membersOnlyEventSelectPriceFieldDAO->members_only_event_id = $membersOnlyEvent->id;
    $membersOnlyEventSelectPriceFieldDAO->find();
    $priceFields = [];
    while ($membersOnlyEventSelectPriceFieldDAO->fetch()) {
      $priceFields[] = $membersOnlyEventSelectPriceFieldDAO->price_field_id;
    }

    $this->assertEquals(2, count($priceFields));
    $this->assertEquals($priceFields, array_values($priceFields));

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
    $priceSet = PriceSetFabricator::fabricate($priceSetParams);

    return $priceSet;
  }

  private function mockPriceFields($eventID) {
    $priceSet = $this->mockPriceSet($eventID);

    $memberPriceField = PriceFieldFabricator::fabricate([
      'price_set_id' => $priceSet['id'],
      'label' => "Member Price Field",
      'name' => "member price field",
      'html_type' => 'Radio',
    ]);

    $priceFieldValue1 = PriceFieldValueFabricator::fabricate([
      'price_field_id' => $memberPriceField['id'],
      'label' => "member price value 1",
      'amount' => 500,
      'financial_type_id' => "Event Fee",
    ]);

    $priceFieldValues2 = PriceFieldValueFabricator::fabricate([
      'price_field_id' => $memberPriceField['id'],
      'label' => "member price value 2",
      'amount' => 100,
      'financial_type_id' => "Event Fee",
    ]);

    $nonMemberPriceField = PriceFieldFabricator::fabricate([
      'price_set_id' => $priceSet['id'],
      'label' => "Non Member Price Field",
      'name' => "non_member_price_field",
      'html_type' => 'Checkbox',
    ]);

    $priceFieldValue1 = PriceFieldValueFabricator::fabricate([
      'price_field_id' => $nonMemberPriceField['id'],
      'label' => "Non member price value 1",
      'amount' => 500,
      'financial_type_id' => "Event Fee",
    ]);
    $priceFieldValues2 = PriceFieldValueFabricator::fabricate([
      'price_field_id' => $nonMemberPriceField['id'],
      'label' => "Non member price value 2",
      'amount' => 100,
      'financial_type_id' => "Event Fee",
    ]);

    $this->attachPriceSetToEvent($eventID, $priceSet['id']);

    return [$memberPriceField, $nonMemberPriceField];

  }

  /**
   * @param $eventID
   * @param $priceSetID
   */
  private function attachPriceSetToEvent($eventID, $priceSetID) {
    CRM_Price_BAO_PriceSet::addTo('civicrm_event', $eventID, $priceSetID);
  }

}
