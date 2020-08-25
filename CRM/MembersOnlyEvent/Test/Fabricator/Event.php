<?php

use CRM_Event_DAO_Event as Event;

class CRM_MembersOnlyEvent_Test_Fabricator_Event {

  /**
   * Fabricates a membership type.
   *
   * @param array $params
   *
   */
  public static function fabricate($params = []) {
    $params = array_merge(static::getDefaultParams(), $params);

    $event = new Event();
    foreach ($params as $property => $value) {
      $event->$property = $value;
    }

    return $event->save();
  }

  private static function getDefaultParams() {
    return [
      "title" => "Test Event",
      "event_title" => "Test Event",
      "event_description" => "",
      "event_type_id" => "2",
      "is_public" => "1",
      "start_date" => "2020-06-15 00:00:00",
      "event_start_date" => "2020-06-15 00:00:00",
      "event_end_date" => "",
      "is_online_registration" => "1",
      "registration_link_text" => "Register Now",
      "event_full_text" => "This event is currently full.",
      "is_monetary" => "0",
      "is_map" => "0",
      "is_active" => "1",
      "is_show_location" => "1",
      "default_role_id" => "1",
      "intro_text" => "",
    ];
  }

}
