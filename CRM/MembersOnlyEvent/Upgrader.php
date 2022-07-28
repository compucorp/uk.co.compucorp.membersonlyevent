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
   * - Increments event_access_type column to start from 1.
   * - Add is_showing_custom_access_denied_message column.
   * - Add is_showing_login_block column.
   * - Add block_type column.
   * - Add login_block_message column.
   * - Add is_showing_purchase_membership_block column.
   * - Add purchase_membership_body_text column.
   * - Migrate purchase_membership_button column values to
   * is_showing_purchase_membership_block column.
   * - Drop purchase_membership_button column.
   */
  public function upgrade_0002() {
    $this->ctx->log->info('Applying update 0002');
    $this->executeSqlFile('sql/upgrade_0002.sql');

    return TRUE;
  }

}
