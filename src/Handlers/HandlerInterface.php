<?php

namespace Drupal\embargo_inheritance\Handlers;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for entity CUD handling.
 */
interface HandlerInterface {

  const KEY = 'embargo_inheritance_cud';

  /**
   * Handle a create event for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The created entity.
   */
  public function handleCreate(EntityInterface $entity) : void;

  /**
   * Handle an update event for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The updated entity.
   */
  public function handleUpdate(EntityInterface $entity) : void;

  /**
   * Handle the deletion of the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The deleted entity.
   */
  public function handleDelete(EntityInterface $entity) : void;

}
