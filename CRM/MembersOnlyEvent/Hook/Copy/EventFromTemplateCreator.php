<?php

use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MemberOnlyEvent;

/**
 * Class CRM_MembersOnlyEvent_Hook_Copy_EventFromTemplateCreator
 */
class CRM_MembersOnlyEvent_Hook_Copy_EventFromTemplateCreator {

  /**
   * @var templateId
   */
  private $templateId;

  /**
   * @var eventId
   */
  private $eventId;

  /**
   * CRM_MembersOnlyEvent_Hook_Copy_EventFRomTemplateCreator constructor.
   *
   * @param $eventId
   * @param $templateId
   */
  public function __construct($eventId, $templateId) {
    $this->eventId = $eventId;
    $this->templateId = $templateId;
  }

  /**
   * Creates default membership only setting from Event Template
   */
  public function create() {
    $memberOnlyEventTemplate = MemberOnlyEvent::getMembersOnlyEvent($this->templateId);

    if (empty($memberOnlyEventTemplate)) {
      return;
    }

    $params = [
      'event_id' => $this->eventId,
      'is_showing_custom_access_denied_message' => $memberOnlyEventTemplate->is_showing_custom_access_denied_message,
      'notice_for_access_denied' => strip_tags($memberOnlyEventTemplate->notice_for_access_denied),
      'is_showing_login_block' => $memberOnlyEventTemplate->is_showing_login_block,
      'block_type' => $memberOnlyEventTemplate->block_type,
      'login_block_message' => strip_tags($memberOnlyEventTemplate->login_block_message),
      'is_showing_purchase_membership_block' => $memberOnlyEventTemplate->is_showing_purchase_membership_block,
      'contribution_page_id' => $memberOnlyEventTemplate->contribution_page_id,
      'purchase_membership_url' => $memberOnlyEventTemplate->purchase_membership_url,
      'purchase_membership_button_label' => $memberOnlyEventTemplate->purchase_membership_button_label,
      'purchase_membership_body_text' => strip_tags($memberOnlyEventTemplate->purchase_membership_body_text),
      'purchase_membership_link_type' => $memberOnlyEventTemplate->purchase_membership_link_type,
      'event_access_type' => $memberOnlyEventTemplate->event_access_type,
    ];

    $membersOnlyEvent = MemberOnlyEvent::create($params);
    $this->setAllowMembershipTypeIDs($membersOnlyEvent->id, $memberOnlyEventTemplate->id);

  }

  /**
   * Sets allowed membership type IDs if applicable
   *
   * @param $memberOnlyEventId
   * @param $memberOnlyEventTemplateId
   */
  private function setAllowMembershipTypeIDs($memberOnlyEventId, $memberOnlyEventTemplateId) {
    $allowedMembershipTypes = EventMembershipType::getAllowedMembershipTypeIDs($memberOnlyEventTemplateId);
    if (empty($allowedMembershipTypes)) {
      return;
    }
    EventMembershipType::updateAllowedMembershipTypes($memberOnlyEventId, $allowedMembershipTypes);
  }

}
