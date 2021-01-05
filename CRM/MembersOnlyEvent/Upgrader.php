<?php
use CRM_MembersOnlyEvent_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_MembersOnlyEvent_Upgrader extends CRM_MembersOnlyEvent_Upgrader_Base {

  public function upgrade_2000() {
    $this->ctx->log->info('Applying update 2000');
    $this->executeSqlFile('sql/upgrade_2000.sql');

    return TRUE;
  }

}
