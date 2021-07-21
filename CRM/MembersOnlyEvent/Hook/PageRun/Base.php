<?php

/**
 * Abstract class for PageRun Hook
 */
abstract class CRM_MembersOnlyEvent_Hook_PageRun_Base {

  /**
   * Handle the hook
   *
   * @param object $page
   */
  public function handle(&$page) {
    $pageName = get_class($page);
    if (!$this->shouldHandle($pageName, $page)) {
      return;
    }

    $this->pageRun($page);
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param $pageName
   * @param $page
   */
  abstract protected function shouldHandle($pageName, &$page);

  /**
   * @param $page
   */
  abstract protected function pageRun(&$page);

}
