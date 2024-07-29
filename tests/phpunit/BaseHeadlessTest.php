<?php

use Civi\Test;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * An abstract BaseHeadlessTest class.
 */
abstract class BaseHeadlessTest extends TestCase implements HeadlessInterface, TransactionalInterface, Test\HookInterface {

  /**
   * Sets up Headless, use stock schema,, install extensions.
   */
  public function setUpHeadless() {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

}
