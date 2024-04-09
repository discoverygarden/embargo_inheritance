<?php

namespace Drupal\embargo_inheritance\Handlers;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityHandlerBase;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterInterface;
use Drupal\search_api\Plugin\search_api\datasource\ContentEntityTrackingManager;
use Drupal\search_api\Utility\TrackingHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract base handler class.
 */
abstract class AbstractBase extends EntityHandlerBase implements EntityHandlerInterface, HandlerInterface {

  /**
   * Constructor.
   */
  public function __construct(
    protected EntityTypeInterface $entityType,
    protected DatabaseAdapterInterface $adapter,
    protected Connection $database,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ?ContentEntityTrackingManager $trackingManager,
    protected ?TrackingHelperInterface $trackingHelper,
  ) {}

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return (new static(
      $entity_type,
      $container->get('plugin.manager.islandora_member_of_entailment.database_adapter')->getDatabaseAdapterPlugin(),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('search_api.entity_datasource.tracking_manager', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('search_api.tracking_helper', ContainerInterface::NULL_ON_INVALID_REFERENCE),
    ))->setModuleHandler($container->get('module_handler'));
  }

  protected function doTrack(ContentEntityInterface $entity) : void {
    if (!$this->moduleHandler->moduleExists('search_api')) {
      return;
    }
    $this->trackingManager->trackEntityChange($entity);
    $this->trackingHelper->trackReferencedEntityUpdate($entity);
  }
}
