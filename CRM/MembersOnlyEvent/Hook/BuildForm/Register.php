<?php

use CRM_MembersOnlyEvent_Hook_BuildForm_Base as BuildFormBase;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEventPriceFieldValue as MembersOnlyEventPriceFieldValue;
use CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess as MembersOnlyEventAccessService;

/**
 * Class for Price Field Form BuildForm Hook
 */
class CRM_MembersOnlyEvent_Hook_BuildForm_Register extends BuildFormBase {

  /**
   * @var \CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess
   */
  private $membersOnlyEventAccessService;

  /**
   * CRM_MembersOnlyEvent_Hook_BuildForm_Register constructor.
   */
  public function __construct() {
    $this->membersOnlyEventAccessService = new MembersOnlyEventAccessService();
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param $formName
   * @param $form
   *
   * @return bool
   */
  protected function shouldHandle($formName, &$form) {
    if ($formName === CRM_Event_Form_Registration_Register::class
      && $form->getAction() === CRM_Core_Action::ADD
      && $this->membersOnlyEventAccessService->userHasEventAccess()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param $formName
   * @param CRM_Event_Form_Registration_Register $form
   *
   * @throws \CRM_Core_Exception
   */
  protected function buildForm($formName, &$form) {
    $membersOnlyEventID = $this->membersOnlyEventAccessService->getMembersOnlyEvent()->id;
    $nonMemberPriceFieldValueIDs = MembersOnlyEventPriceFieldValue::getNonMemberPriceFieldValueIDs($membersOnlyEventID);
    $hasMembership = $this->membersOnlyEventAccessService->hasMembership();

    foreach ($form->_elementIndex as $elementName => $i) {
      if (strpos($elementName, 'price_') === 0) {
        $eGroup =& $form->getElement($elementName);
        if (is_a($eGroup, 'HTML_QuickForm_group')) {
          $elements = [];
          foreach ($eGroup->getElements() as $element) {
            $isCheckboxElement = is_a($element, 'HTML_QuickForm_checkbox');
            $isRadioElement = is_a($element, 'HTML_QuickForm_radio');

            if (!$isCheckboxElement && !$isRadioElement) {
              continue 2;
            }

            $priceFieldValueID = $element->getAttribute('value');
            $isNonMemberPriceFieldValue = in_array($priceFieldValueID, $nonMemberPriceFieldValueIDs);

            if (!$hasMembership && $isNonMemberPriceFieldValue) {
              $elements[] = $element;
            }

            if ($hasMembership && !$isNonMemberPriceFieldValue) {
              $elements[] = $element;
            }
          }

          $eGroup->setElements($elements);
        }
      }
    }

  }

}
