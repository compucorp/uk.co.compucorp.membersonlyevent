<?php

use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;
use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;
use CRM_MembersOnlyEvent_BAO_EventGroup as EventGroup;

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
   * Sets variables up before form is built.
   */
  public function preProcess() {
    parent::preProcess();
    $this->setSelectedChild('membersonlyevent');
  }

  /**
   * @inheritdoc
   */
  public function buildQuickForm() {
    $this->addFields();

    $this->addFormRule([$this, 'formRules']);

    Civi::resources()->addVars('MembersOnlyEvent', [
      'EVENT_ACCESS_TYPE_MEMBERS_ONLY' => MembersOnlyEvent::EVENT_ACCESS_TYPE_MEMBERS_ONLY,
      'EVENT_ACCESS_TYPE_GROUPS_ONLY' => MembersOnlyEvent::EVENT_ACCESS_TYPE_GROUPS_ONLY,
      'EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY' => MembersOnlyEvent::EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY,
    ]);

    parent::buildQuickForm();
  }

  /**
   * Adds the form fields.
   */
  private function addFields() {
    $this->addRadio(
      'event_access_type',
      ts('Who will have access to this event?'),
      [
        MembersOnlyEvent::EVENT_ACCESS_TYPE_MEMBERS_ONLY => ts('Only allow members to register for this event'),
        MembersOnlyEvent::EVENT_ACCESS_TYPE_GROUPS_ONLY => ts('Only allow contacts in groups to register for this event'),
        MembersOnlyEvent::EVENT_ACCESS_TYPE_AUTHENTICATED_ONLY => ts('Only allow authenticated users to register for this event'),
      ],
      [
        'allowClear' => TRUE,
      ],
      '<br>'
    );

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

    $this->addEntityRef(
      'allowed_groups',
      ts('Allowed Groups'),
      [
        'entity' => 'Group',
        'multiple' => TRUE,
        'select' => ['minimumInputLength' => 0],
      ]
    );

    $this->addElement(
      'checkbox',
      'is_showing_custom_access_denied_message',
      NULL,
      ts('Show a custom access denied message')
    );

    $this->add(
      'wysiwyg',
      'notice_for_access_denied',
      ts('Notice for access denied')
    );

    $this->addElement(
      'checkbox',
      'is_showing_login_block',
      NULL,
      ts('Show login block to anonymous users')
    );

    $this->add(
      'select',
      'block_type',
      ts('Block type'),
      $this->getBlockTypes()
    );

    $this->add(
      'wysiwyg',
      'login_block_message',
      ts('Login block message')
    );

    $this->addElement(
      'checkbox',
      'is_showing_purchase_membership_block',
      NULL,
      ts('Show a purchase membership block when access denied')
    );

    $this->add(
      'text',
      'purchase_membership_button_label',
      ts('Purchase Membership Button Label')
    );

    $this->add(
      'wysiwyg',
      'purchase_membership_body_text',
      ts('Body text')
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
    $errors = [];

    // Skip validation if the event is not members-only or not groups-only
    if (empty($values['event_access_type'])) {
      return $errors;
    }

    $event_access_type = (int) $values['event_access_type'];
    $isGroupsOnlyEvent = $event_access_type === MembersOnlyEvent::EVENT_ACCESS_TYPE_GROUPS_ONLY;
    if ($isGroupsOnlyEvent) {
      $this->validateForEmptyAllowedGroups($values, $errors);
    }

    $isMembersOnlyEvent = $event_access_type === MembersOnlyEvent::EVENT_ACCESS_TYPE_MEMBERS_ONLY;
    if ($isMembersOnlyEvent) {
      switch ($values['is_showing_purchase_membership_block']) {
        case self::NO_SELECTED:
          $this->validateForDisabledPurchaseButton($values, $errors);
          break;

        case self::YES_SELECTED:
          $this->validateForEnabledPurchaseButton($values, $errors);
          break;
      }
    }

    return $errors ?: TRUE;
  }

  /**
   * Validates form fields when allowed groups field is empty.
   *
   * @param array $values
   * @param array $errors
   */
  private function validateForEmptyAllowedGroups(&$values, &$errors) {
    if (empty($values['allowed_groups'])) {
      $errors['allowed_groups'] = ts('Please select at least one group');
    }
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
    $defaultValues = [];

    $this->setInitialValues($defaultValues);

    $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($this->_id);
    if ($membersOnlyEvent) {
      $defaultValues['event_access_type'] = $membersOnlyEvent->event_access_type;
      $defaultValues['allowed_membership_types'] = EventMembershipType::getAllowedMembershipTypeIDs($membersOnlyEvent->id);
      $defaultValues['allowed_groups'] = EventGroup::getAllowedGroupIDs($membersOnlyEvent->id);
      $defaultValues['is_showing_custom_access_denied_message'] = $membersOnlyEvent->is_showing_custom_access_denied_message;
      $defaultValues['notice_for_access_denied'] = $membersOnlyEvent->notice_for_access_denied;
      $defaultValues['is_showing_login_block'] = $membersOnlyEvent->is_showing_login_block;
      $defaultValues['block_type'] = $membersOnlyEvent->block_type;
      $defaultValues['login_block_message'] = $membersOnlyEvent->login_block_message;
      $defaultValues['is_showing_purchase_membership_block'] = $membersOnlyEvent->is_showing_purchase_membership_block;
      $defaultValues['purchase_membership_button_label'] = $membersOnlyEvent->purchase_membership_button_label;
      $defaultValues['purchase_membership_body_text'] = $membersOnlyEvent->purchase_membership_body_text;
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
    $defaultValues['event_access_type'] = self::NO_SELECTED;
    $defaultValues['is_showing_custom_access_denied_message'] = self::NO_SELECTED;
    $defaultValues['notice_for_access_denied'] = ts('Access to this event is restricted');
    $defaultValues['is_showing_login_block'] = self::NO_SELECTED;
    $defaultValues['block_type'] = MembersOnlyEvent::BLOCK_TYPE_LOGIN_ONLY;
    $defaultValues['login_block_message'] = ts('To access this event, please login below');
    $defaultValues['is_showing_purchase_membership_block'] = self::NO_SELECTED;
    $defaultValues['purchase_membership_button_label'] = ts('Purchase membership to book the event');
    $defaultValues['purchase_membership_body_text'] = ts('Become a member to get access to this event');
    $defaultValues['purchase_membership_link_type'] = MembersOnlyEvent::LINK_TYPE_URL;
  }

  /**
   * @inheritdoc
   */
  public function postProcess() {
    $params = $this->exportValues();
    $params['event_access_type'] = (int) $params['event_access_type'];
    $params['event_id'] = $this->_id;

    $isTabEnabled = !empty($params['event_access_type']);

    $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($params['event_id']);
    $submitOperation = $this->getSubmitOperation($isTabEnabled, $membersOnlyEvent);
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
   * @param boolean $isTabEnabled
   *   True if 'Is members-only event ?' field or 'Is groups-only event ?' field is checked
   *   or False if it's not.
   * @param \CRM_MembersOnlyEvent_BAO_MembersOnlyEvent $membersOnlyEvent
   *   Contains the members-only event configurations if the event is
   *   members-only event.
   *
   * @return string
   *   It may contain one of the OPERATION_* constants
   *   defined at the top of this class.
   */
  private function getSubmitOperation($isTabEnabled, $membersOnlyEvent = NULL) {
    if (!$membersOnlyEvent && !$isTabEnabled) {
      return self::OPERATION_DO_NOTHING;
    }

    if ($membersOnlyEvent && !$isTabEnabled) {
      return self::OPERATION_DOWNGRADE_TO_NORMAL_EVENT;
    }

    if ($membersOnlyEvent && $isTabEnabled) {
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
    $eventSetToMembersOnly = $params['event_access_type'] === MembersOnlyEvent::EVENT_ACCESS_TYPE_MEMBERS_ONLY;
    $eventSetToGroupsOnly = $params['event_access_type'] === MembersOnlyEvent::EVENT_ACCESS_TYPE_GROUPS_ONLY;

    // The checkbox values are not submitted when unchecked.
    $params['is_showing_custom_access_denied_message'] = $params['is_showing_custom_access_denied_message'] ?? 0;
    $params['is_showing_login_block'] = $params['is_showing_login_block'] ?? 0;
    $params['is_showing_purchase_membership_block'] = $params['is_showing_purchase_membership_block'] ?? 0;

    $membersOnlyEvent = MembersOnlyEvent::create($params);
    if (!empty($membersOnlyEvent->id)) {
      $allowedMembershipTypeIDs = [];
      if ($eventSetToMembersOnly && !empty($params['allowed_membership_types'])) {
        $allowedMembershipTypeIDs = explode(',', $params['allowed_membership_types']);
      }
      EventMembershipType::updateAllowedMembershipTypes($membersOnlyEvent->id, $allowedMembershipTypeIDs);

      $allowedGroupIDs = [];
      if ($eventSetToGroupsOnly && !empty($params['allowed_groups'])) {
        $allowedGroupIDs = explode(',', $params['allowed_groups']);
      }
      EventGroup::updateAllowedGroups($membersOnlyEvent->id, $allowedGroupIDs);
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
   * Gets block types.
   */
  private function getBlockTypes() {
    $block_types = [
      MembersOnlyEvent::BLOCK_TYPE_LOGIN_ONLY => ts('Login only'),
    ];

    if (function_exists('module_exists') && module_exists('ssp_core_user')) {
      $block_types[MembersOnlyEvent::BLOCK_TYPE_LOGIN_OR_REGISTER_BLOCK] = ts('Login or register block');
    }

    return $block_types;
  }

}
