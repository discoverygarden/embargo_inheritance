<?php

namespace Drupal\embargo_inheritance\EventSubscriber;

use Drupal\embargo_inheritance\Plugin\search_api\processor\EmbargoProcessor;
use Drupal\search_api\Event\GatheringPluginInfoEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter search_api embargo join processor to be inheritance-aware.
 */
class ProcessorAlterEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      SearchApiEvents::GATHERING_PROCESSORS => 'alterProcessor',
    ];
  }

  /**
   * Event callback; respond to processor gathering event.
   *
   * @param \Drupal\search_api\Event\GatheringPluginInfoEvent $event
   *   The processor gathering event to which we are responding.
   */
  public function alterProcessor(GatheringPluginInfoEvent $event) : void {
    $processors =& $event->getDefinitions();

    $processors['embargo_join_processor']['class'] = EmbargoProcessor::class;
  }

}
