<?php

namespace Drupal\embargo_inheritance;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\embargo\EmbargoStorageInterface;
use Drupal\embargo\EmbargoStorageTrait;
use Drupal\file\FileInterface;
use Drupal\islandora_hierarchical_access\LUTGeneratorInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extended, inherited embargo storage service.
 */
class EmbargoStorage extends SqlContentEntityStorage implements EmbargoStorageInterface {

  use EmbargoStorageTrait;

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) : self {
    return parent::createInstance($container, $entity_type)
      ->setRequest($container->get('request_stack')->getCurrentRequest())
      ->setUser($container->get('current_user'));
  }

  /**
   * {@inheritDoc}
   */
  public function getApplicableEmbargoes(EntityInterface $entity): array {
    $query = $this->database->select('embargo', 'e')
      ->fields('e', ['id'])
      ->distinct();
    $member_lut = $query->leftJoin('islandora_member_of_entailment', 'imoe', '%alias.aid = e.embargoed_node');

    if ($entity instanceof NodeInterface) {
      $query->condition(
        $query->orConditionGroup()
          ->condition('e.embargoed_node', $entity->id())
          ->condition("{$member_lut}.nid", $entity->id())
      );
    }
    elseif ($entity instanceof MediaInterface || $entity instanceof FileInterface) {
      $lut_alias = $query->join(LUTGeneratorInterface::TABLE_NAME, 'lut', "%alias.nid = e.embargoed_node OR %alias.nid = {$member_lut}.nid");
      $key = $entity instanceof MediaInterface ? 'mid' : 'fid';
      $query->condition("{$lut_alias}.{$key}", $entity->id());
    }
    else {
      throw new \InvalidArgumentException("Unrecognized type: {$entity->getEntityTypeId()}");
    }

    $ids = $query->execute()->fetchCol();
    return $this->loadMultiple($ids);
  }

}
