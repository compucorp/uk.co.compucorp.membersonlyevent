<?php

use CRM_MembersOnlyEvent_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_MembersOnlyEvent_Upgrader extends CRM_MembersOnlyEvent_Upgrader_Base {

  /**
   * This upgrade does the following :
   * - Creates membersonlyevent_event_group table
   * - Adds is_groups_only column to membersonlyevent table.
   */
  public function upgrade_0001() {
    $this->ctx->log->info('Applying update 0001');
    $this->executeSqlFile('sql/upgrade_0001.sql');

    return TRUE;
  }

  /**
   * This upgrade does the following :
   * - Renames is_groups_only column to event_access_type.
   * - Changes the type to int unsigned.
   */
  public function upgrade_0002() {
    $this->ctx->log->info('Applying update 0002');
    $this->executeSqlFile('sql/upgrade_0002.sql');

    return TRUE;
  }

}
