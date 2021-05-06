<?php

use CRM_MembersOnlyEvent_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_MembersOnlyEvent_Upgrader extends CRM_MembersOnlyEvent_Upgrader_Base {

  /**
   * Creates membersonlyevent_event_group table
   */
  public function upgrade_0001() {
    $this->ctx->log->info('Applying update 0001');
    $this->executeSqlFile('sql/upgrade_0001.sql');

    return TRUE;
  }

}
