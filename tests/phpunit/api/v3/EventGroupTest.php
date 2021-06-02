<?php

use CRM_MembersOnlyEvent_Test_Fabricator_MembersOnlyEvent as MembersOnlyEventFabricator;
use CRM_MembersOnlyEvent_Test_Fabricator_Group as GroupFabricator;

/**
 * EventGroup API Test Case
 * @group headless
 */
class api_v3_EventGroupTest extends BaseHeadlessTest {
  use \Civi\Test\Api3TestTrait;

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    $table = CRM_Core_DAO_AllCoreTables::getTableForEntityName('EventGroup');
    $this->assertTrue($table && CRM_Core_DAO::checkTableExists($table), 'There was a problem with extension installation. Table for ' . 'EventGroup' . ' not found.');
    parent::setUp();
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Simple example test case.
   *
   * Note how the function name begins with the word "test".
   */
  public function testCreateGetDelete() {
    $fabricatedMembersOnlyEvent = MembersOnlyEventFabricator::fabricate();
    $fabricatedGroup = GroupFabricator::fabricate([
      'title' => 'Group A',
    ], TRUE);

    $created = $this->callAPISuccess('EventGroup', 'create', [
      'members_only_event_id' => $fabricatedMembersOnlyEvent->id,
      'group_id' => $fabricatedGroup->id,
    ]);
    $this->assertTrue(is_numeric($created['id']));

    $get = $this->callAPISuccess('EventGroup', 'get', []);
    $this->assertEquals(1, $get['count']);
    $this->assertEquals($fabricatedMembersOnlyEvent->id, $get['values'][$created['id']]['members_only_event_id']);
    $this->assertEquals($fabricatedGroup->id, $get['values'][$created['id']]['group_id']);

    $this->callAPISuccess('EventGroup', 'delete', [
      'id' => $created['id'],
    ]);
  }

}
