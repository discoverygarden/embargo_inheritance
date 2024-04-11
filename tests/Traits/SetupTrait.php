<?php

namespace Drupal\Tests\embargo_inheritance\Traits;

use Drupal\Tests\islandora_member_of_entailment\Traits\SetupTrait as UpstreamTrait;

/**
 * Do common module setup.
 */
trait SetupTrait {

  use UpstreamTrait;

  /**
   * Do common setup.
   */
  public function doEmbargoInheritanceSetup() : void {
    $this->doIslandoraMemberOfEntailmentSetup();
    $this->enableModuleWithDependencies(['embargo_inheritance']);
  }

}
