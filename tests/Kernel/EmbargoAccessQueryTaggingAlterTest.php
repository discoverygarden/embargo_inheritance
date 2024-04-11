<?php

namespace Drupal\Tests\embargo_inheritance\Kernel;

use Drupal\Tests\embargo\Kernel\EmbargoAccessQueryTaggingAlterTest as Upstream;
use Drupal\Tests\embargo_inheritance\Traits\SetupTrait;

/**
 * Test base tagged query access, with ancestor-aware queries.
 *
 * @group embargo_inheritance
 */
class EmbargoAccessQueryTaggingAlterTest extends Upstream {

  use SetupTrait;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->doEmbargoInheritanceSetup();
  }

}
