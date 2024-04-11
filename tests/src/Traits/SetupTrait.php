<?php

namespace Drupal\Tests\embargo_inheritance\Traits;

use Drupal\Tests\islandora_member_of_entailment\Traits\SetupTrait as UpstreamTrait;

/**
 * Do common module setup.
 */
trait SetupTrait {

  use UpstreamTrait;

  /**
   * Flag to determine if things have already run.
   *
   * @var bool
   */
  private bool $setup = FALSE;

  /**
   * Do common setup.
   */
  protected function doEmbargoInheritanceSetup() : void {
    if (!$this->setup) {
      $this->setup = TRUE;
      $this->doIslandoraMemberOfEntailmentSetup();
      $this->enableModuleWithDependencies(['embargo_inheritance']);
    }
  }

}
