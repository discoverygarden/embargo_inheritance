<?php

namespace Drupal\embargo_inheritance\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\embargo\Event\EmbargoEvents;
use Drupal\embargo\Event\TagExclusionEvent;
use Drupal\embargo\Event\TagInclusionEvent;
use Drupal\islandora_member_of_entailment\Plugin\DatabaseAdapterManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Query tagging event subscriber.
 */
class TaggingEventSubscriber implements EventSubscriberInterface, ContainerInjectionInterface {

  use DependencySerializationTrait;

  public function __construct(
    protected Connection $database,
    protected DatabaseAdapterManagerInterface $databaseAdapterManager,
  ) {
    // No-op.
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('database'),
      $container->get('plugin.manager.islandora_member_of_entailment.database_adapter'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      EmbargoEvents::TAG_INCLUSION => 'inclusion',
      EmbargoEvents::TAG_EXCLUSION => ['exclusion', 10],
    ];
  }

  /**
   * Helper; build out ancestor embargo existence query.
   *
   * @param string $embargo_alias
   *   The alias against which to attach the existence query.
   * @param array $target_aliases
   *   The node aliases from which to base the query.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The ancestor embargo existence query.
   */
  protected function buildExistenceQuery(string $embargo_alias, array $target_aliases) : SelectInterface {
    $ancestor_existence = $this->database->select($this->databaseAdapterManager->getDatabaseAdapterPlugin()->getTableName(), 'alut');
    $ancestor_existence->addExpression(1, 'ancestor_existence');
    $ancestor_existence->where("{$embargo_alias}.embargoed_node = alut.aid");
    $ancestor_existence->where(strtr('alut.nid IN (!targets)', [
      '!targets' => implode(', ', $target_aliases),
    ]));

    return $ancestor_existence;
  }

  /**
   * Event handler; tagging inclusion event.
   *
   * @param \Drupal\embargo\Event\TagInclusionEvent $event
   *   The event being handled.
   */
  public function inclusion(TagInclusionEvent $event) : void {
    $event->getCondition()->exists($this->buildExistenceQuery(
      $event->getEmbargoAlias(),
      $event->getTargetAliases(),
    ));
  }

  /**
   * Event handler; tagging exclusion event.
   *
   * @param \Drupal\embargo\Event\TagExclusionEvent $event
   *   The event being handled.
   */
  public function exclusion(TagExclusionEvent $event) : void {
    $event->getCondition()
      ->where(strtr('!field IN (!targets)', [
        '!field' => "{$event->getUnexpiredAlias()}.embargoed_node",
        '!targets' => implode(', ', $event->getTargetAliases()),
      ]))
      ->exists($this->buildExistenceQuery(
        $event->getUnexpiredAlias(),
        $event->getTargetAliases(),
      ));

    // This expects to replace the base implementation.
    $event->stopPropagation();
  }

}
