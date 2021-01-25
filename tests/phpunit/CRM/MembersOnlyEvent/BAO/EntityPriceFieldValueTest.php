<?php

use CRM_MembersOnlyEvent_BAO_EntityPriceFieldValue as EntityPriceFieldValue;
use CRM_MembersOnlyEvent_Test_Fabricator_Event as EventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceFieldValue as PriceFieldValueFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceField as PriceFieldFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_PriceSet as PriceSetFabricator;



require_once __DIR__ . '/../../../BaseHeadlessTest.php';

/**
 * Class CRM_MembersOnlyEvent_BAO_EntityPriceFieldValue
 *
 * @group headless
 */
class CRM_MembersOnlyEvent_BAO_EntityPriceFieldValueTest extends BaseHeadlessTest {

  /**
   * @param $eventID
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function mockPriceSet() {
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

  private function mockPriceField() {
    $priceSet = $this->mockPriceSet();

    $priceField = PriceFieldFabricator::fabricate([
      'price_set_id' => $priceSet['id'],
      'label' => "Price Field",
      'name' => "price_field",
      'html_type' => 'Radio',
    ]);

    return $priceField;
  }

  private function mockPriceFieldValue($params) {
    $priceField = $this->mockPriceField();
    $params = array_merge($params, ['price_field_id' => $priceField['id']]);
    $priceFieldValue = PriceFieldValueFabricator::fabricate($params);

    return $priceFieldValue;
  }

  /**
   * Tests updateEntityPriceFieldValues().
   */
  public function testUpdateEntityPriceFieldValues() {
    $event1 = EventFabricator::fabricate();
    $event2 = EventFabricator::fabricate();
    $priceFieldValue = $this->mockPriceFieldValue([
      'label' => "Non-member Fee",
    ]);

    EntityPriceFieldValue::updateEntityPriceFieldValues($priceFieldValue['id'], 'Event', [
      $event1->id,
      $event2->id,
    ]);

    $entityPriceFieldValueDAO = new CRM_MembersOnlyEvent_DAO_EntityPriceFieldValue();
    $entityPriceFieldValueDAO->price_field_value_id = $priceFieldValue['id'];
    $entityPriceFieldValueDAO->find();
    $entityIDs = [];
    while ($entityPriceFieldValueDAO->fetch()) {
      $entityIDs[] = $entityPriceFieldValueDAO->entity_id;
    }

    $this->assertEquals(2, count($entityIDs));
    $this->assertEquals([$event1->id, $event2->id], array_values($entityIDs));
  }

  /**
   * Tests getEntityPriceFieldValueIDs().
   */
  public function testGetEntityPriceFieldValueIDs() {
    $event1 = EventFabricator::fabricate();
    $event2 = EventFabricator::fabricate();
    $priceFieldValue = $this->mockPriceFieldValue([
      'label' => "Non-member Fee",
    ]);

    EntityPriceFieldValue::updateEntityPriceFieldValues($priceFieldValue['id'], 'Event', [
      $event1->id,
      $event2->id,
    ]);

    list($entityTable, $entityIDs) = EntityPriceFieldValue::getEntityPriceFieldValueIDs($priceFieldValue['id']);

    $this->assertEquals('Event', $entityTable);
    $this->assertEquals(2, count($entityIDs));
    $this->assertEquals([$event1->id, $event2->id], array_values($entityIDs));
  }
}
