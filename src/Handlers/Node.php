<?php

namespace Drupal\embargo_inheritance\Handlers;

use Drupal\Core\Entity\EntityInterface;

/**
 * Handle CUD for node entities.
 */
class Node extends AbstractBase {

  /**
   * {@inheritDoc}
   */
  public function handleCreate(EntityInterface $entity): void {
    // No-op?
  }

  /**
   * {@inheritDoc}
   */
  public function handleUpdate(EntityInterface $entity): void {
    // @todo If there's a change to `field_member_of` and there is any difference
    // in the embargoes visible through them, flag any indirect child entities for
    // reindexing.
  }

  /**
   * {@inheritDoc}
   */
  public function handleDelete(EntityInterface $entity): void {
    // @todo If there's an embargo visible via either this node or this node's
    // `field_member_of` relationship(s), flag indirect child entities for
    // reindexing.
  }

}
