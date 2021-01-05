<?php

use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;
use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_MembersOnlyEvent_Form_MembersOnlyEventTab extends CRM_Event_Form_ManageEvent {

  /**
   * 'No' Option is selected for a radio button.
   *
   * @const boolean
   */
  const NO_SELECTED = FALSE;

  /**
   * 'Yes' Option is selected for a radio button.
   *
   * @const boolean
   */
  const YES_SELECTED = TRUE;

  /**
   * Used to specify the type of  operation
   * to be performed on the submitted event data.
   *
   * @const String
   */
  const OPERATION_DO_NOTHING = 'do_nothing';
  const OPERATION_CREATE = 'create';
  const OPERATION_UPDATE = 'update';
  const OPERATION_DOWNGRADE_TO_NORMAL_EVENT = 'downgrade_to_normal_event';

  /**
   * @inheritdoc
   */
  public function buildQuickForm() {
    $this->addFields();

    $this->addFormRule(array($this, 'formRules'));

    parent::buildQuickForm();
  }

  /**
   * Adds the form fields.
   */
  private function addFields() {
    $this->add(
      'checkbox',
      'is_members_only_event',
      ts('Only allow members to register for this event?')
    );

    $priceFields = $this->getEventPriceFields();

    if (!empty($priceFields)) {
      $includePriceFields = &$this->addElement('advmultiselect', 'pricefields_to_hide',
        ts('Select price field to hide from members') . ' ', $priceFields, [
          'size' => 5,
          'style' => 'width:150px',
          'class' => 'advmultiselect',
        ]
      );
      $includePriceFields->setButtonAttributes('add', ['value' => ts('Add >>')]);
      $includePriceFields->setButtonAttributes('remove', ['value' => ts('<< Remove')]);
    }

    $this->addEntityRef(
      'allowed_membership_types',
      ts('Allowed Membership Types'),
      [
        'entity' => 'MembershipType',
        'multiple' => TRUE,
        'placeholder' => ts('- any -'),
        'select' => ['minimumInputLength' => 0],
      ]
    );

    $this->addYesNo(
      'purchase_membership_button',
      ts('Provide Purchase Membership Button when access denied ?')
    );

    $this->add(
      'wysiwyg',
      'notice_for_access_denied',
      ts('Notice for access denied')
    );

    $this->add(
      'text',
      'purchase_membership_button_label',
      ts('Purchase Membership Button Label')
    );

    $this->addRadio(
      'purchase_membership_link_type',
      ts('Purchase Membership Button Link'),
      [0 => 'Link to a Contribution Page', 1 => 'Other URLs']
    );

    $this->addEntityRef(
      'contribution_page_id',
      ts('Contribution Page'),
      [
        'entity' => 'ContributionPage',
        'multiple' => FALSE,
        'placeholder' => ts('- Select -'),
        'select' => ['minimumInputLength' => 0],
      ]
    );

    $this->add(
      'text',
      'purchase_membership_url',
      ts('Purchase Membership URL'),
      ['placeholder' => CRM_Utils_System::baseCMSURL()]
    );
  }

  /**
   * Validates the submitted form values.
   *
   * @param array $values
   *   All the submitted form values
   *
   * @return array|bool
   *   The errors message to report to the user if any or TRUE otherwise.
   */
  public function formRules($values) {
    $errors = array();

    // Skip validation if the event is not members-only
    if (empty($values['is_members_only_event'])) {
      return $errors;
    }

    switch ($values['purchase_membership_button']) {
      case self::NO_SELECTED:
        $this->validateForDisabledPurchaseButton($values, $errors);
        break;

      case self::YES_SELECTED:
        $this->validateForEnabledPurchaseButton($values, $errors);
        break;
    }

    return $errors ?: TRUE;
  }

  /**
   * Validates form fields when 'purchase membership button'
   * is disabled.
   *
   * @param array $values
   * @param array $errors
   */
  private function validateForDisabledPurchaseButton(&$values, &$errors) {
    if (empty($values['notice_for_access_denied'])) {
      $errors['notice_for_access_denied'] = ts('Please set a Notice message for access denied');
    }
  }

  /**
   * Validates form fields when 'purchase membership button'
   * is enabled.
   *
   * @param array $values
   * @param array $errors
   */
  private function validateForEnabledPurchaseButton(&$values, &$errors) {
    if (empty($values['purchase_membership_button_label'])) {
      $errors['purchase_membership_button_label'] = ts('Please set Membership Purchase button label');
    }

    $this->validateMembershipLinkTypeFields($values, $errors);
  }

  /**
   * Validates the fields related to 'link type' field.
   *
   * @param array $values
   * @param array $errors
   */
  private function validateMembershipLinkTypeFields(&$values, &$errors) {
    // if contribution page link type is selected
    if ($values['purchase_membership_link_type'] == MembersOnlyEvent::LINK_TYPE_CONTRIBUTION_PAGE) {
      if (empty($values['contribution_page_id'])) {
        $errors['contribution_page_id'] = ts('Please select a contribution page');
      }
    }

    // if URL link type is selected
    if ($values['purchase_membership_link_type'] == MembersOnlyEvent::LINK_TYPE_URL) {
      if (empty($values['purchase_membership_url'])) {
        $errors['purchase_membership_url'] = ts('Please enter the Membership purchase URL');
      }
      else {
        if (!CRM_Utils_Rule::url($values['purchase_membership_url'])) {
          $errors['purchase_membership_url'] = ts('Please enter a valid URL');
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function setDefaultValues() {
    $defaultValues = array();

    $this->setInitialValues($defaultValues);

    $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($this->_id);
    if ($membersOnlyEvent) {
      $defaultValues['is_members_only_event'] = self::YES_SELECTED;
      $defaultValues['allowed_membership_types'] = EventMembershipType::getAllowedMembershipTypesIDs($membersOnlyEvent->id);
      $defaultValues['purchase_membership_button'] = $membersOnlyEvent->purchase_membership_button;
      $defaultValues['notice_for_access_denied'] = $membersOnlyEvent->notice_for_access_denied;
      $defaultValues['purchase_membership_button_label'] = $membersOnlyEvent->purchase_membership_button_label;
      $defaultValues['purchase_membership_link_type'] = $membersOnlyEvent->purchase_membership_link_type;
      $defaultValues['contribution_page_id'] = $membersOnlyEvent->contribution_page_id;
      $defaultValues['purchase_membership_url'] = $membersOnlyEvent->purchase_membership_url;
    }

    return $defaultValues;
  }

  /**
   * Sets the initial form values
   *
   * @param $defaultValues
   */
  private function setInitialValues(&$defaultValues) {
    $defaultValues['is_members_only_event'] = self::NO_SELECTED;
    $defaultValues['purchase_membership_button'] = self::NO_SELECTED;
    $defaultValues['notice_for_access_denied'] = ts('Access Denied');
    $defaultValues['purchase_membership_button_label'] = ts('Purchase membership to book the event');
    $defaultValues['purchase_membership_link_type'] = MembersOnlyEvent::LINK_TYPE_URL;
  }

  /**
   * @inheritdoc
   */
  public function postProcess() {
    $params = $this->exportValues();
    $params['event_id'] = $this->_id;

    $eventSetToMembersOnly = !empty($params['is_members_only_event']) ? self::YES_SELECTED : self::NO_SELECTED;
    $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($params['event_id']);
    $submitOperation = $this->getSubmitOperation($eventSetToMembersOnly, $membersOnlyEvent);
    switch ($submitOperation) {
      case self::OPERATION_CREATE:
        $this->saveFormData($params);
        break;

      case self::OPERATION_UPDATE:
        $params['id'] = $membersOnlyEvent->id;
        $this->saveFormData($params);
        break;

      case self::OPERATION_DOWNGRADE_TO_NORMAL_EVENT:
        $this->downgradeToNormalEvent($membersOnlyEvent->id);
        break;
    }

    parent::endPostProcess();
  }

  /**
   * Returns the type of submit operation based
   * on the submitted data, there are 4 cases which are :
   * 1- OPERATION_DO_NOTHING : the event is not already a members-only
   *   event & 'Is members-only event ?' field is not checked.
   * 2- OPERATION_DOWNGRADE_TO_NORMAL_EVENT : if the event is currently
   *   a members-only event but we unchecked 'Is members-only event ?' field.
   * 3- OPERATION_UPDATE : if the event is currently a members-only event
   *   and we kept 'Is members-only event ?' field checked.
   * 4- OPERATION_CREATE : if the event is not a members-only event but
   *   we checked 'Is members-only event ?' field.
   *
   * @param boolean $eventSetToMembersOnly
   *   True if Is members-only event ?' field is checked
   *   or False if it's not.
   * @param \CRM_MembersOnlyEvent_BAO_MembersOnlyEvent $membersOnlyEvent
   *   Contains the members-only event configurations if the event is
   *   members-only event.
   *
   * @return string
   *   It may contain one of the OPERATION_* constants
   *   defined at the top of this class.
   */
  private function getSubmitOperation($eventSetToMembersOnly, $membersOnlyEvent = NULL) {
    if (!$membersOnlyEvent && !$eventSetToMembersOnly) {
      return self::OPERATION_DO_NOTHING;
    }

    if ($membersOnlyEvent && !$eventSetToMembersOnly) {
      return self::OPERATION_DOWNGRADE_TO_NORMAL_EVENT;
    }

    if ($membersOnlyEvent && $eventSetToMembersOnly) {
      return self::OPERATION_UPDATE;
    }

    return self::OPERATION_CREATE;
  }

  /**
   * Saves the form data, which will either be
   * an update to already existing members-only event
   * configurations or converting a normal event to
   * members-only event,
   *
   * @param $params
   */
  private function saveFormData($params) {
    $membersOnlyEvent = MembersOnlyEvent::create($params);
    if (!empty($membersOnlyEvent->id)) {
      $allowedMembershipTypesIDs = array();
      if (!empty($params['allowed_membership_types'])) {
        $allowedMembershipTypesIDs = explode(',', $params['allowed_membership_types']);
      }

      EventMembershipType::updateAllowedMembershipTypes($membersOnlyEvent->id, $allowedMembershipTypesIDs);
    }
  }

  /**
   * Downgrades an existing members-only
   * event to normal event.
   *
   * @param $membersOnlyEventID
   *   The Id of the members-only event
   *   to be downgraded.
   */
  private function downgradeToNormalEvent($membersOnlyEventID) {
    $membersOnlyEvent = new MembersOnlyEvent();
    $membersOnlyEvent->id = $membersOnlyEventID;
    $membersOnlyEvent->delete();
  }

  /**
   * Gets price fields from an event ID
   * Empty Price Fields will be returned if an event is not paid event
   * or price set is not been used or one of the price fields
   * is not Radio or Checkbox
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function getEventPriceFields() {
    $priceFieldOptions = [];
    $priceSetId = CRM_Price_BAO_PriceSet::getFor('civicrm_event', $this->_id, NULL);

    if (!$this->isPaidEvent() || !$priceSetId) {
      return $priceFieldOptions;
    }

    $options = civicrm_api3('PriceField', 'get', [
      'sequential' => 1,
      'price_set_id' => $priceSetId,
    ])['values'];

    if (!$this->isPriceFieldValidForMembersOnlyEvent($options)) {
      return $priceFieldOptions;
    }

    foreach ($options as $option) {
      $priceFieldOptions[$option['id']] = $option['label'];
    }

    return $priceFieldOptions;
  }

  /**
   * Check if the event is paid event
   *
   * @return mixed
   * @throws CiviCRM_API3_Exception
   */
  private function isPaidEvent() {
    return civicrm_api3('Event', 'get', [
      'return' => ["is_monetary"],
      'id' => $this->_id,
    ])['values'][$this->_id]['is_monetary'];
  }

  /**
   * Currently Members only event only support Radio and Checkbox
   * elements in price field.
   * This function is checking if select price set is valid
   *
   * @param array $options
   */
  private function isPriceFieldValidForMembersOnlyEvent(array $options) {
    $supportElement = ['CheckBox', 'Radio'];
    foreach ($options as $option) {
      if (!in_array($option['html_type'], $supportElement)) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
