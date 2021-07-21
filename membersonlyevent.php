<?php

require_once 'membersonlyevent.civix.php';

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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function membersonlyevent_civicrm_postInstall() {
  _membersonlyevent_civix_civicrm_postInstall();
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
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function membersonlyevent_civicrm_angularModules(&$angularModules) {
  _membersonlyevent_civix_civicrm_angularModules($angularModules);
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
  $listeners = [
    new CRM_MembersOnlyEvent_Hook_Tabset_Event(),
  ];
  foreach ($listeners as $currentListener) {
    $currentListener->handle($tabsetName, $tabs, $context);
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
  $listeners = [
    new CRM_MembersOnlyEvent_Hook_PageRun_Register(),
  ];
  foreach ($listeners as $currentListener) {
    $currentListener->handle($page);
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
  $listeners = [
    new CRM_MembersOnlyEvent_Hook_PreProcess_Register(),
  ];
  foreach ($listeners as $currentListener) {
    $currentListener->handle($formName, $form);
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
    'permission' => 'administer CiviCRM,edit all events',
    'operator' => NULL,
    'separator' => NULL,
  ];

  _membersonlyevent_civix_insert_navigation_menu($menu, 'Administer/CiviEvent', $membersonlyeventMenu);
}

/**
 * Implements hook_civicrm_entityTypes().
 */
function membersonlyevent_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = [
    'name' => 'MembersOnlyEvent',
    'class' => 'CRM_MembersOnlyEvent_DAO_MembersOnlyEvent',
    'table' => 'membersonlyevent',
  ];
  $entityTypes[] = [
    'name' => 'EventGroup',
    'class' => 'CRM_MembersOnlyEvent_DAO_EventGroup',
    'table' => 'membersonlyevent_event_group',
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
