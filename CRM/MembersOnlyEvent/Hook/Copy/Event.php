<?php

/**
 * Class for for Copy Hook for Event object
 */
class CRM_MembersOnlyEvent_Hook_Copy_Event {

  /**
   * Handle Hook Pre Event
   *
   * @param object $object
   * @param  $object
   *
   * @throws CRM_Core_Exception
   */
  public function handle(&$object) {
    $templateId = CRM_Utils_Request::retrieve('template_id', 'Int');
    if (empty(templateId)) {
      return;
    }
    $eventFromTemplateCreator = new CRM_MembersOnlyEvent_Hook_Copy_EventFromTemplateCreator($object->id, $templateId);
    $eventFromTemplateCreator->create();
  }

}
