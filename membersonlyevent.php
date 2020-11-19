<?php

//----------------------------------------------------------------------------//
//                             File Organization                              //
//                                                                            //
// To keep this file organized, it is split into 2 sections: CiviCRM Hooks    //
// and Helper Functions. The former has all the civicrm hooks implementations //
// used by this extension, whereas the latter, has all the helper functions   //
// used by those hooks.                                                       //
//                                                                            //
// If you're adding new things here, please keep this organization in mind.   //
//                                                                            //
//----------------------------------------------------------------------------//

use CRM_MembersOnlyEvent_BAO_MembersOnlyEvent as MembersOnlyEvent;
use CRM_MembersOnlyEvent_BAO_EventMembershipType as EventMembershipType;
use CRM_MembersOnlyEvent_Configurations as Configurations;

require_once 'membersonlyevent.civix.php';


//----------------------------------------------------------------------------//
//                           CiviCRM Hooks                                    //
//----------------------------------------------------------------------------//

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function membersonlyevent_civicrm_config(&$config) {
  _membersonlyevent_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function membersonlyevent_civicrm_xmlMenu(&$files) {
  _membersonlyevent_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function membersonlyevent_civicrm_install() {
  return _membersonlyevent_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function membersonlyevent_civicrm_uninstall() {
  return _membersonlyevent_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function membersonlyevent_civicrm_enable() {
  return _membersonlyevent_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function membersonlyevent_civicrm_disable() {
  return _membersonlyevent_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function membersonlyevent_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _membersonlyevent_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function membersonlyevent_civicrm_managed(&$entities) {
  return _membersonlyevent_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function membersonlyevent_civicrm_caseTypes(&$caseTypes) {
  _membersonlyevent_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function membersonlyevent_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _membersonlyevent_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function membersonlyevent_civicrm_permission(&$permissions) {
  $prefix = ts('Members-Only Event') . ': ';
  $permissions['members only event registration'] = $prefix . ts('Can register for members-only events irrespective of membership status');
}

/**
 * Implements hook_civicrm_tabset().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_tabset/
 */
function membersonlyevent_civicrm_tabset($tabsetName, &$tabs, $context) {
  // check if the tabset is 'Manage Event' page
  if ($tabsetName == 'civicrm/event/manage') {
    if (empty($context['event_id'])) {
      return;
    }

    $eventID = $context['event_id'];
    $url = CRM_Utils_System::url(
      'civicrm/event/manage/membersonlyevent',
      'reset=1&id=' . $eventID . '&action=update&component=event');

    $tab['membersonlyevent'] = array(
      'title' => ts('Members only event settings'),
      'link' => $url,
      'valid' => _membersonlyevent_is_tab_valid($eventID),
      'active' => TRUE,
      'current' => FALSE,
      'class' => 'ajaxForm',
    );

    //Insert this tab into position 4 (after `Online Registration` tab)
    $tabs = array_merge(
      array_slice($tabs, 0, 4),
      $tab,
      array_slice($tabs, 4)
    );
  }
}

/**
 * Implements hook_civicrm_pageRun().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pageRun/
 *
 * Handler for pageRun hook.
 */
function membersonlyevent_civicrm_pageRun(&$page) {
  $f = '_' . __FUNCTION__ . '_' . get_class($page);
  if (function_exists($f)) {
    $f($page);
  }
}

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess/
 *
 * Handler for preProcess hook.
 */
function membersonlyevent_civicrm_preProcess($formName, &$form) {
  $f = '_' . __FUNCTION__ . '_' . $formName;
  if (function_exists($f)) {
    $f($form);
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function membersonlyevent_civicrm_navigationMenu(&$menu) {
  $membersonlyeventMenu = [
    'label' => ts('Members Only Event Extension Configurations'),
    'name' => 'membersonlyevent_configurations',
    'url' => 'civicrm/admin/membersonlyevent',
    'permission' => 'administer CiviCRM,access CiviEvent',
    'operator' => NULL,
    'separator' => NULL,
  ];

  _membersonlyevent_civix_insert_navigation_menu($menu, 'Administer/CiviEvent', $membersonlyeventMenu);
}

//----------------------------------------------------------------------------//
//                               Helper Functions                             //
//----------------------------------------------------------------------------//

/**
 * Checks if the members-only settings tab
 * should be valid or not. Currently it is valid
 * only if the event is members-only event and
 * online registration is enabled.
 *
 * @param int $eventID
 *
 * @return bool
 *
 */
function _membersonlyevent_is_tab_valid($eventID) {
  $isOnlineRegistrationEnabled = FALSE;
  $event = civicrm_api3('Event', 'get', array(
    'sequential' => 1,
    'return' => array('is_online_registration'),
    'id' => $eventID,
  ));
  if (!empty($event['values'][0]['is_online_registration'])) {
    $isOnlineRegistrationEnabled = TRUE;
  }

  $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($eventID);

  if ($isOnlineRegistrationEnabled && $membersOnlyEvent) {
    return TRUE;
  }

  return FALSE;
}

/**
 * Callback for event info page
 */
function _membersonlyevent_civicrm_pageRun_CRM_Event_Page_EventInfo(&$page) {
  $eventID = $page->_id;

  _membersonlyevent_event_info_page_session_handler($eventID);

  $userHasEventAccess = _membersonlyevent_user_has_event_access($eventID);
  if ($userHasEventAccess) {
    // skip early and show the page if the user has access to the members-only event.
    return;
  }

  _membersonlyevent_hide_event_info_page_register_button();

  _membersonlyevent_handle_access_option_for_user($eventID);

}

/**
 * Handle session message if the user is trying
 * to register another participant.
 *
 * @param int $eventID
 *
 */
function _membersonlyevent_event_info_page_session_handler($eventID) {
  if (!_membersonly_is_event_for_members_only($eventID)) {
    return;
  }

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
 * Checks if the logged-in user has
 * an access to the specified event or not.
 *
 * @param int $eventID
 *
 * @return bool
 *   True if has access or False otherwise
 */
function _membersonlyevent_user_has_event_access($eventID) {
  $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($eventID);
  if (!$membersOnlyEvent) {
    // the member is not a members-only event so nothing to check
    return TRUE;
  }

  $contactID = CRM_Core_Session::getLoggedInContactID();
  if (!$contactID) {
    // the user is not logged-in so he cannot access the event
    return FALSE;
  }

  if (CRM_Core_Permission::check('members only event registration')) {
    // any user with 'members only event registration' permission
    // can access any members-only event.
    return TRUE;
  }

  $contactActiveAllowedMemberships = _membersonlyevent_get_contact_active_allowed_memberships($membersOnlyEvent->id, $contactID);

  if (!$contactActiveAllowedMemberships) {
    // the users does not have any active membership
    // so he cannot access the event.
    return FALSE;
  }

  return _membersonlyevent_is_memberships_duration_valid_during_event($eventID, $contactActiveAllowedMemberships);
}

/**
 * Checks if the logged-in user has
 * an access to the specified event or not.
 *
 * @param $eventID
 * @return CRM_MembersOnlyEvent_DAO_MembersOnlyEvent|FALSE
 */
function _membersonly_is_event_for_members_only($eventID) {
  return MembersOnlyEvent::getMembersOnlyEvent($eventID);
}

/**
 * Gets the memberships for the specified
 * contact in case he has any active membership
 * with a membership type allowed to access the
 * provided members-only event.
 *
 * @param int $membersOnlyEventID
 * @param int $contactID
 *
 * @return array
 *   List of contact Memberships or empty array if nothing found
 */
function _membersonlyevent_get_contact_active_allowed_memberships($membersOnlyEventID, $contactID) {
  $params = array(
    'sequential' => 1,
    'contact_id' => $contactID,
    'active_only' => 1,
  );

  $allowedMembershipTypes = EventMembershipType::getAllowedMembershipTypesIDs($membersOnlyEventID);
  if (!empty($allowedMembershipTypes)) {
    $params['membership_type_id'] = array('IN' => $allowedMembershipTypes);
  }

  $contactActiveMemberships = civicrm_api3('Membership', 'get', $params);

  if ($contactActiveMemberships['count']) {
    return $contactActiveMemberships['values'];
  }

  return array();
}

/**
 * Checks if any of a list of allowed an active memberships
 * is valid (active) during the  period of
 * the specified event in case 'membership duration check'
 * is enabled.
 * The membership is valid during an event if :
 * 1- The membership end date is empty.
 * 2- The event start date is empty.
 * 3- The membership end data is > the event start date.
 *
 * @param int $eventID
 * @param array $activeAllowedMemberships
 *   A list of allowed and active memberships to be checked
 *   if they are valid during the specified event period.
 *
 * @return bool
 *   True if there is any valid membership during the event period
 *   or false otherwise.
 */
function _membersonlyevent_is_memberships_duration_valid_during_event($eventID, $activeAllowedMemberships) {
  $configs = Configurations::get();
  if (empty($configs['membership_duration_check'])) {
    // the 'membership duration check' is not enabled
    // so the user should be able to access the event.
    return TRUE;
  }

  $eventStartDate = _membersonlyevent_get_event_start_date($eventID);
  foreach ($activeAllowedMemberships as $membership) {
    $membershipEndDate = !(empty($membership['end_date'])) ? $membership['end_date'] : '';
    if (empty($membershipEndDate) || empty($eventStartDate) || ($membershipEndDate >= $eventStartDate)) {
      // the user has an active allowed membership for this event
      // so the user should be able to access the event.
      return TRUE;
    }
  }

  // since 'membership duration check' is enabled but
  // the user does not have any active allowed membership
  // for this event so he will not be able to access the event.
  return FALSE;
}

/**
 * Gets the event start date in Y-m-d format if exist.
 *
 * @param $eventID
 *
 * @return string
 *   Event start date in y-md format
 *   or empty string if no start date exist.
 */
function _membersonlyevent_get_event_start_date($eventID) {
  $eventStartDate = '';

  $eventInfo = civicrm_api3('event', 'get',
    array('id' => $eventID, 'return' => array('start_date'), 'sequential' => 1))['values'][0];

  if (!empty($eventInfo['start_date'])) {
    $date = new DateTime($eventInfo['start_date']);
    $eventStartDate = $date->format('Y-m-d');
  }

  return $eventStartDate;
}

/**
 * Hides the event info page action links which contain
 * the event register link.
 */
function _membersonlyevent_hide_event_info_page_register_button() {
  CRM_Core_Region::instance('event-page-eventinfo-actionlinks-top')->update('default', array(
    'disabled' => TRUE,
  ));
  CRM_Core_Region::instance('event-page-eventinfo-actionlinks-bottom')->update('default', array(
    'disabled' => TRUE,
  ));
}

/**
 * Handles access options for logged / anonymous user.
 *
 * @param $eventID
 *
 */
function _membersonlyevent_handle_access_option_for_user($eventID) {
  $membersOnlyEvent = MembersOnlyEvent::getMembersOnlyEvent($eventID);
  if ($membersOnlyEvent->purchase_membership_button) {
    _membersonlyevent_add_membership_purchase_button_to_event_info_page($membersOnlyEvent);
    $userLoggedIn = CRM_Core_Session::getLoggedInContactID();
    if ($userLoggedIn) {
      return;
    }
    $loginURL = CRM_Core_Config::singleton()->userSystem->getLoginURL();
    $infoText = 'This event is for members only, if you have a current, pending or former membership
                 please log in before purchase membership. If you are not a current member you will be charged
                 an additional membership fee. <a href="' . $loginURL . '">Click here to login </a>';
    CRM_Core_Session::setStatus(ts($infoText));

  }
  else {
    // Purchase membership button is disabled, so we will just show the configured notice message
    CRM_Core_Session::setStatus($membersOnlyEvent->notice_for_access_denied);
  }
}

/**
 * Adds membership purchase button based
 * on the members-only event configurations to
 * the header and the footer of the event info page.
 *
 * @param \CRM_MembersOnlyEvent_BAO_MembersOnlyEvent $membersOnlyEvent
 */
function _membersonlyevent_add_membership_purchase_button_to_event_info_page($membersOnlyEvent) {
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

  _membersonlyevent_add_action_button_to_event_info_page($membershipPurchaseURL, $buttonText);
}

/**
 * Adds a button with the specified
 * url and text to the header and the footer
 * of the event info page.
 */
function _membersonlyevent_add_action_button_to_event_info_page($url, $buttonText) {
  $buttonToAdd = array(
    'template' => 'CRM/Event/Page/members-event-button.tpl',
    'button_text' => ts($buttonText),
    'position' => 'top',
    'url' => $url,
    'weight' => -10,
  );

  CRM_Core_Region::instance('event-page-eventinfo-actionlinks-top')->add($buttonToAdd);

  $buttonToAdd['position'] = 'bottom';
  CRM_Core_Region::instance('event-page-eventinfo-actionlinks-bottom')->add($buttonToAdd);
}

/**
 * Callback for event registration page
 *
 * Hence that users are supposed to register for events
 * from the info page, so in case the user tired to access
 * the registration page directly we will just redirect him
 * to the main page instead of showing any error or buttons to
 * login or buy membership.
 *
 * @param $form
 */
function _membersonlyevent_civicrm_preProcess_CRM_Event_Form_Registration_Register(&$form) {
  $eventID = $form->_eventId;
  $userHasEventAccess = _membersonlyevent_user_has_event_access($eventID);
  if (!$userHasEventAccess) {
    // if the user has no access, redirect to the main page
    CRM_Utils_System::redirect('/');
  }
  if (_membersonly_is_event_for_members_only($eventID)) {
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
    CRM_Core_Resources::singleton()->addStyle('.crm-not-you-message { display: none; }');
    if (isset($cid)) {
      CRM_Core_Session::setStatus('You have already registered for this event! You cannot register other users.');
      $id = CRM_Utils_Request::retrieve('id', 'Positive');
      $params = 'id=' . $id;
      if ($reset = CRM_Utils_Request::retrieve('reset', 'Positive')) {
        $params .= '&reset=' . $reset;
      }
      $url = CRM_Utils_System::url(CRM_Utils_System::currentPath(), $params);
      CRM_Utils_System::redirect($url);
      $form->_skipDupeRegistrationCheck = TRUE;
    }
  }
}

/**
 * Implements hook_civicrm_entityTypes().
 */
function membersonlyevent_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = [
    'name'  => 'MembersOnlyEvent',
    'class' => 'CRM_MembersOnlyEvent_DAO_MembersOnlyEvent',
    'table' => 'membersonlyevent',
  ];
}

/**
 * Implements hook_civicrm_copy().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_copy/
 */
function membersonlyevent_civicrm_copy($objectName, &$object) {
  if ($objectName != 'Event') {
    return;
  }
  $listeners = [
    new CRM_MembersOnlyEvent_Hook_Copy_Event(),
  ];
  foreach ($listeners as $currentListener) {
    $currentListener->handle($object);
  }
}
