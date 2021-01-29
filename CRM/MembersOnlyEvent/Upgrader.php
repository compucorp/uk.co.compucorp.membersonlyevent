<?php

use CRM_MembersOnlyEvent_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_MembersOnlyEvent_Upgrader extends CRM_MembersOnlyEvent_Upgrader_Base {

  /**
   * Adds non_member_price_field_value table to DB.
   */
  public function upgrade_2000() {
    $this->ctx->log->info('Applying update 2000');
    $this->executeSqlFile('sql/event_price_field_value_table_install.sql');

    return TRUE;
  }

}
