<?php

namespace Drupal\embargo_inheritance\Plugin\search_api\processor;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\embargo\Plugin\search_api\processor\EmbargoJoinProcessor;
use Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterManagerInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Query\QueryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A search_api processor to add embargo related info.
 *
 * @SearchApiProcessor(
 *   id = "embargo_inheritance_join_processor",
 *   label = @Translation("Embargo inheritance, join-wise"),
 *   description = @Translation("Add information regarding embargo access constraints from nodes and their ancestors."),
 *   stages = {
 *     "add_properties" = 0,
 *     "pre_index_save" = 0,
 *     "preprocess_query" = 0,
 *   },
 *   locked = false,
 *   hidden = false,
 * )
 */
class EmbargoProcessor extends EmbargoJoinProcessor {

  const NODE_FIELD = 'embargo_inheritance__nodes';
  const EMBARGO_FIELD_FILE = 'embargo_inheritance__nodes__file';
  const EMBARGO_FIELD_NODE = 'embargo_inheritance__nodes__node';

  /**
   * The member of entailment database adapter manager.
   *
   * @var \Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterManagerInterface
   */
  protected DatabaseAdapterManagerInterface $adapterManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $instance->adapterManager = $container->get('plugin.manager.islandora_member_of_entailment.database_adapter');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  protected function findRelatedNodes(EntityInterface $entity): array {
    $direct_nodes = parent::findRelatedNodes($entity);
    $ancestor_nodes = $this->database->select($this->adapterManager->getDatabaseAdapterPlugin()->getTableName(), 'lut')
      ->fields('lut', ['aid'])
      ->condition('nid', $direct_nodes)
      ->execute()
      ->fetchCol();

    return array_merge(
      $direct_nodes,
      $ancestor_nodes,
    );

  }

}
