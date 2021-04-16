<?php

use CRM_MembersOnlyEvent_Hook_PageRun_Base as PageRunBase;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;
use CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess as MembersOnlyEventAccessService;
use CRM_MembersOnlyEvent_BAO_MembersOnlyEventPriceFieldValue as MembersOnlyEventPriceFieldValue;

/**
 * Class for Event Info PageRun Hook
 */
class CRM_MembersOnlyEvent_Hook_PageRun_Register extends PageRunBase {

  /**
   * @var \CRM_MembersOnlyEvent_Service_MembersOnlyEventAccess
   */
  private $membersOnlyEventAccessService;

  /**
   * @var array
   */
  private $nonMemberPriceFieldValueIDs;

  /**
   * @var bool
   */
  private $hasMembership;

  /**
   * @var array
   */
  private $feeItems;

  /**
   * @var int
   */
  private $feeBlockIndex;

  /**
   * CRM_MembersOnlyEvent_Hook_BuildForm_Register constructor.
   */
  public function __construct() {
    $this->membersOnlyEventAccessService = new MembersOnlyEventAccessService();
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param $pageName
   * @param $page
   *
   * @return bool
   */
  protected function shouldHandle($pageName, &$page) {
    if ($pageName === CRM_Event_Page_EventInfo::class && $this->membersOnlyEventAccessService->getMembersOnlyEvent()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Callback for event info page
   *
   * @param $page
   */
  protected function pageRun(&$page) {
    $this->showSessionMessageWhenRegisteringAnotherParticipant();
    $this->handleFeeBlock();
  }

  /**
   * Handles fee block in Event Info page.
   *
   * Ensures showing/hiding the right price field values depending on the current
   * user's membership. Alters the Smarty variable feeBlock because the values are
   * already proccessed.
   */
  private function handleFeeBlock() {
    $membersOnlyEvent = $this->membersOnlyEventAccessService->getMembersOnlyEvent();
    $this->nonMemberPriceFieldValueIDs = MembersOnlyEventPriceFieldValue::getNonMemberPriceFieldValueIDs($membersOnlyEvent->id);
    $this->hasMembership = $this->membersOnlyEventAccessService->hasMembership();

    $smarty = CRM_Core_Smarty::singleton();
    $this->feeItems = $this->getFeeItems($membersOnlyEvent->event_id);

    // We cannot access the array index in the callback so we will use feeBlockIndex instead.
    $this->feeBlockIndex = 0;
    $feeItemsThatNeedToBeRemoved = array_reduce(
      $smarty->_tpl_vars['feeBlock']['label'],
      [$this, 'getFeeItemsThatNeedToBeRemoved'],
      []);

    $this->removeFeeItems($smarty, $feeItemsThatNeedToBeRemoved);
  }

  /**
   * Gets fee items that need to be removed.
   *
   * - If the fee item is for the price field then do nothing.
   * - if the fee item is for the price field value then return its index depending
   *   on the user having a membership.
   * - if the price field has 'Admin' visiblitiy and the user is anonymous then
   *   the feeItems will have more values than feeBlock hence we need to increament
   *   the feeBlockIndex.
   *
   * @return array
   */
  private function getFeeItemsThatNeedToBeRemoved($feeItemsThatNeedToBeRemoved, $label) {
    $this->feeBlockIndex++;
    $priceFieldValueID = NULL;
    while ($this->feeBlockIndex <= count($this->feeItems)) {
      $priceField = $this->feeItems[$this->feeBlockIndex] ?? NULL;
      if (!$priceField || ($priceField && $priceField['isPriceField'])) {
        return $feeItemsThatNeedToBeRemoved;
      }
      if ($priceField['label'] === $label) {
        $priceFieldValueID = $priceField['id'];
        break;
      }
      else {
        $this->feeBlockIndex++;
      }
    }

    $isNonMemberPriceFieldValue = in_array($priceFieldValueID, $this->nonMemberPriceFieldValueIDs);

    // If the current user is not a member, remove any member price field value
    if (!$this->hasMembership && !$isNonMemberPriceFieldValue) {
      $feeItemsThatNeedToBeRemoved[] = $this->feeBlockIndex;
    }

    // If the current user is a member, remove any non-member price field value
    if ($this->hasMembership && $isNonMemberPriceFieldValue) {
      $feeItemsThatNeedToBeRemoved[] = $this->feeBlockIndex;
    }

    return $feeItemsThatNeedToBeRemoved;
  }

  /**
   * @return array
   */
  private function getFeeItems($EventId) {
    $priceSetId = CRM_Price_BAO_PriceSet::getFor('civicrm_event', $EventId, NULL);
    // Get only the values of the active price fields and ordered by weight because this
    // is how CiviCRM fetch them
    // see https://github.com/civicrm/civicrm-core/blob/5.35.1/CRM/Event/Page/EventInfo.php#L97
    // and https://github.com/civicrm/civicrm-core/blob/5.35.1/CRM/Price/BAO/PriceSet.php#L422
    $priceFields = civicrm_api3('PriceField', 'get', [
      'sequential' => 1,
      'price_set_id' => $priceSetId,
      'is_active' => 1,
      'return' => ['id', 'label'],
      'options' => ['sort' => 'weight asc'],
      'api.PriceFieldValue.get' => [
        'is_active' => 1,
        'return' => ['id', 'label'],
        'options' => ['sort' => 'weight, label'],
      ],
    ])['values'];

    // Add an empty value to start the array index from 1
    $feeItems = [''];
    foreach ($priceFields as $priceField) {
      $feeItems[] = [
        'id' => $priceField['id'],
        'label' => $priceField['label'],
        'isPriceField' => TRUE,
      ];
      foreach ($priceField['api.PriceFieldValue.get']['values'] as $priceFieldValue) {
        $feeItems[] = [
          'id' => $priceFieldValue['id'],
          'label' => $priceFieldValue['label'],
          'isPriceField' => FALSE,
        ];
      }
    }

    return $feeItems;
  }

  /**
   * Removes fee items from feeBlock
   *
   * @param \CRM_Core_Smarty $smarty
   * @param array $feeItemIDs
   */
  private function removeFeeItems(&$smarty, $feeItemIDs) {
    foreach ($feeItemIDs as $key) {
      unset($smarty->_tpl_vars['feeBlock']['value'][$key]);
      unset($smarty->_tpl_vars['feeBlock']['label'][$key]);
      unset($smarty->_tpl_vars['feeBlock']['lClass'][$key]);
      unset($smarty->_tpl_vars['feeBlock']['isDisplayAmount'][$key]);
    }

    // Change array key to start from 1 instead of 0
    foreach (['value', 'label', 'lClass', 'isDisplayAmount'] as $key) {
      array_unshift($smarty->_tpl_vars['feeBlock'][$key], "");
      unset($smarty->_tpl_vars['feeBlock'][$key][0]);
    }
  }

  /**
   * Handle session message if the user is trying
   * to register another participant.
   */
  private function showSessionMessageWhenRegisteringAnotherParticipant() {
    $isEventForMembersOnly = $this->membersOnlyEventAccessService->getMembersOnlyEvent();

    $session = CRM_Core_Session::singleton();
    $statusMessages = $session->get('status');
    if (empty($statusMessages)) {
      return;
    }

    foreach ($statusMessages as $k => $msg) {
      if (strpos($msg['text'], 'register another participant')) {
        $statusMessages[$k]['text'] = ts("It looks like you are already registered for this event. If you want to change your registration, or you feel that you've gotten this message in error, please contact the site administrator.");
      }
    }
    $session->set('status', $statusMessages);
  }

  /**
   * Adds membership purchase button based
   * on the members-only event configurations to
   * the header and the footer of the event info page.
   *
   * @param \CRM_MembersOnlyEvent_DAO_MembersOnlyEvent $membersOnlyEvent
   */
  private function addMembershipPurchaseButtonToEventInfoPage($membersOnlyEvent) {
    switch ($membersOnlyEvent->purchase_membership_link_type) {
      case MembersOnlyEvent::LINK_TYPE_CONTRIBUTION_PAGE:
        $contributionPageID = $membersOnlyEvent->contribution_page_id;
        $path = 'civicrm/contribute/transact';
        $params = 'reset=1&id=' . $contributionPageID;
        $membershipPurchaseURL = CRM_Utils_System::url($path, $params);
        break;

      case MembersOnlyEvent::LINK_TYPE_URL:
      default:
        $membershipPurchaseURL = $membersOnlyEvent->purchase_membership_url;
        break;
    }

    $buttonText = $membersOnlyEvent->purchase_membership_button_label;

    $this->addActionButtonToEventInfoPage($membershipPurchaseURL, $buttonText);
  }

  /**
   * Adds a button with the specified
   * url and text to the header and the footer
   * of the event info page.
   *
   * @param $url
   * @param $buttonText
   */
  public function addActionButtonToEventInfoPage($url, $buttonText) {
    $buttonToAdd = [
      'template' => 'CRM/Event/Page/members-event-button.tpl',
      'button_text' => ts($buttonText),
      'position' => 'top',
      'url' => $url,
      'weight' => -10,
    ];

    CRM_Core_Region::instance('event-page-eventinfo-actionlinks-top')
      ->add($buttonToAdd);

    $buttonToAdd['position'] = 'bottom';
    CRM_Core_Region::instance('event-page-eventinfo-actionlinks-bottom')
      ->add($buttonToAdd);
  }

}
