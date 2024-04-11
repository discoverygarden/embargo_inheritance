<?php

namespace Drupal\Tests\embargo_inheritance\Kernel\OnceRemoved;

use Drupal\islandora\IslandoraUtils;
use Drupal\node\NodeInterface;
use Drupal\Tests\embargo_inheritance\Kernel\Base\EmbargoAccessQueryTaggingAlterTest as Upstream;

/**
 * Test base tagged query access, with ancestor-aware queries.
 *
 * @group embargo_inheritance
 */
class EmbargoAccessQueryTaggingAlterTest extends Upstream {

  /**
   * An embargoed collection above the target node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected NodeInterface $collectionNode;

  /**
   * {@inheritDoc}
   */
  protected function setupEntities(): void {
    $this->doEmbargoInheritanceSetup();
    parent::setupEntities();

    $this->collectionNode = $this->createNode();
    $this->collectionNode->save();
    $this->embargo->setEmbargoedNode($this->collectionNode);
    $this->embargo->save();

    $this->embargoedNode->set(IslandoraUtils::MEMBER_OF_FIELD, [
      'target_id' => $this->collectionNode->id(),
    ]);
    $this->embargoedNode->save();
  }

}
