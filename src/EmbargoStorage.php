<?php

namespace Drupal\embargo_inheritance;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\embargo\EmbargoStorageInterface;
use Drupal\embargo\EmbargoStorageTrait;
use Drupal\file\FileInterface;
use Drupal\islandora_hierarchical_access\LUTGeneratorInterface;
use Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterManagerInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extended, inherited embargo storage service.
 */
class EmbargoStorage extends SqlContentEntityStorage implements EmbargoStorageInterface {

  use EmbargoStorageTrait;

  /**
   * Database adapter plugin manager.
   *
   * @var \Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterManagerInterface
   */
  protected DatabaseAdapterManagerInterface $adapterManager;

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) : self {
    $instance = parent::createInstance($container, $entity_type)
      ->setRequest($container->get('request_stack')->getCurrentRequest())
      ->setUser($container->get('current_user'));

    $instance->adapterManager = $container->get('plugin.manager.islandora_member_of_entailment.database_adapter');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function getApplicableEmbargoes(EntityInterface $entity): array {
    if (!in_array($entity->getEntityTypeId(), EmbargoStorageInterface::APPLICABLE_ENTITY_TYPES)) {
      return [];
    }

    $base_query = $this->database->select('embargo', 'e')
      ->fields('e', ['id']);

    if ($entity instanceof NodeInterface) {
      $member_query = (clone $base_query);
      $member_lut = $member_query->join($this->adapterManager->getDatabaseAdapterPlugin()->getTableName(), 'imoe', '%alias.aid = e.embargoed_node');
      $member_query->condition("{$member_lut}.nid", $entity->id());
      $query = (clone $base_query)
        ->condition('e.embargoed_node', $entity->id())
        ->union($member_query);
    }
    elseif ($entity instanceof MediaInterface || $entity instanceof FileInterface) {
      $iha_query = $this->database->select(LUTGeneratorInterface::TABLE_NAME, 'lut')
        ->fields('lut', ['nid']);
      $key = $entity instanceof MediaInterface ? 'mid' : 'fid';
      $iha_query->condition("lut.{$key}", $entity->id());

      $member_query = (clone $base_query);
      $member_lut = $member_query->join($this->adapterManager->getDatabaseAdapterPlugin()->getTableName(), 'imoe', '%alias.aid = e.embargoed_node');
      $member_query->condition("{$member_lut}.nid", $iha_query, 'IN');
      $query = (clone $base_query)
        ->condition('e.embargoed_node', $iha_query, 'IN')
        ->union($member_query);
    }
    else {
      throw new \InvalidArgumentException("Unrecognized type: {$entity->getEntityTypeId()}");
    }

    $ids = array_unique($query->execute()->fetchCol());
    return $this->loadMultiple($ids);
  }

}
