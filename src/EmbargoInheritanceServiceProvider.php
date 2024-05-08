<?php

namespace Drupal\embargo_inheritance;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service provider/alterer.
 */
class EmbargoInheritanceServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritDoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('embargo.search_api_tracker_helper')) {
      $definition = $container->getDefinition('embargo.search_api_tracker_helper');
      $definition->setClass(SearchApiTracker::class);
    }
  }

}
