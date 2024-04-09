<?php

namespace Drupal\embargo_inheritance\Handlers;

use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\embargo\EmbargoInterface;
use Drupal\islandora_hierarchical_access\LUTGeneratorInterface;
use Drupal\node\NodeInterface;

/**
 * Handle CUD for embargo entities.
 */
class Embargo extends AbstractBase {

  /**
   * @param \Drupal\node\NodeInterface $node
   *
   * @return \Drupal\Core\Database\StatementInterface
   */
  protected function getRelatedEntities(NodeInterface $node) : StatementInterface {
    $query = $this->database->select(LUTGeneratorInterface::TABLE_NAME, 'ihalut')
      ->fields('ihalut', ['nid', 'mid', 'fid']);
    $hier_lut = $query->leftJoin($this->adapter->getTableName(), 'hier_lut', '%alias.aid = ihalut.nid');
    $query->condition(
      $query->orConditionGroup()
        ->condition("{$hier_lut}.aid", $node->id())
        ->condition('ihalut.nid', $node->id())
    );

    return $query->execute();
  }

  protected function handleBasicChange(EmbargoInterface $entity) : void {
    $results = $this->getRelatedEntities($entity->getEmbargoedNode());
    array_map($this->doTrack(...), $this->entityTypeManager->getStorage('node')->loadMultiple($results->fetchCol(/* 0 */)));
    array_map($this->doTrack(...), $this->entityTypeManager->getStorage('media')->loadMultiple($results->fetchCol(1)));
    array_map($this->doTrack(...), $this->entityTypeManager->getStorage('file')->loadMultiple($results->fetchCol(2)));
  }

  /**
   * {@inheritDoc}
   */
  public function handleCreate(EntityInterface $entity): void {
    // Flag indirect child entities of target node for reindexing.
    assert($entity instanceof EmbargoInterface);
    $this->handleBasicChange($entity);
  }

  /**
   * {@inheritDoc}
   */
  public function handleUpdate(EntityInterface $entity): void {
    // Flag indirect child entities of target node for reindexing.
    assert($entity instanceof EmbargoInterface);
    if ($entity->original && $entity->original->getEmbargoedNode() != $entity->getEmbargoedNode()) {
      $original_results = $this->getRelatedEntities($entity->original->getEmbargoedNode());
      $nodes = $original_results->fetchCol(/* 0 */);
      $media = $original_results->fetchCol(1);
      $files = $original_results->fetchCol(2);
    }
    else {
      $nodes = [];
      $media = [];
      $files = [];
    }

    $current_results = $this->getRelatedEntities($entity->getEmbargoedNode());
    $nodes = array_unique(array_merge($nodes, $current_results->fetchCol(/* 0 */)));
    $media = array_unique(array_merge($media, $current_results->fetchCol(1)));
    $files = array_unique(array_merge($files, $current_results->fetchCol(2)));

    array_map($this->doTrack(...), $this->entityTypeManager->getStorage('node')->loadMultiple($nodes));
    array_map($this->doTrack(...), $this->entityTypeManager->getStorage('media')->loadMultiple($media));
    array_map($this->doTrack(...), $this->entityTypeManager->getStorage('file')->loadMultiple($files));
  }

  /**
   * {@inheritDoc}
   */
  public function handleDelete(EntityInterface $entity): void {
    // Flag indirect child entities of target node for reindexing.
    assert($entity instanceof EmbargoInterface);
    $this->handleBasicChange($entity);
  }

}
