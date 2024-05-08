<?php

namespace Drupal\embargo_inheritance;

use Drupal\embargo\SearchApiTracker as UpstreamTracker;
use Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterInterface;
use Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Override upstream.
 */
class SearchApiTracker extends UpstreamTracker {

  /**
   * The IMoE database adapter manager service.
   *
   * @var \Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterManagerInterface
   */
  protected DatabaseAdapterManagerInterface $databaseAdapterManager;

  /**
   * IMoE's adapter for the current database.
   *
   * @var \Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterInterface
   */
  protected DatabaseAdapterInterface $adapter;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) : self {
    $instance = parent::create($container);

    assert($instance instanceof SearchApiTracker);

    $instance->databaseAdapterManager = $container->get('plugin.manager.islandora_member_of_entailment.database_adapter');
    $instance->adapter = $instance->databaseAdapterManager->getDatabaseAdapterPlugin();

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function propagateChildren(NodeInterface $node) : void {
    parent::propagateChildren($node);

    $descendant_ids = $this->database->select($this->adapter->getTableName(), 'd')
      ->fields('d', ['nid'])
      ->condition('aid', $node->id())
      ->execute()
      ->fetchCol();

    /** @var \Drupal\node\NodeInterface $descendant */
    foreach ($this->entityTypeManager->getStorage('node')->loadMultiple($descendant_ids) as $descendant) {
      $this->doTrack($descendant);
      parent::propagateChildren($descendant);
    }
  }

}
