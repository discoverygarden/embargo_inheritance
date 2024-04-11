<?php

namespace Drupal\Tests\embargo_inheritance\Kernel;

use Drupal\Tests\embargo\Kernel\FileEmbargoTest as Upstream;
use Drupal\Tests\embargo_inheritance\Traits\SetupTrait;

/**
 * Test base file embargo access, with ancestor-aware queries.
 *
 * @group embargo_inheritance
 */
class FileEmbargoTest extends Upstream {

  use SetupTrait;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->doEmbargoInheritanceSetup();
  }

}
