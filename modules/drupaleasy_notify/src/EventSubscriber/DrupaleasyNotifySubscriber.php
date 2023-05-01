<?php

namespace Drupal\drupaleasy_notify\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\drupaleasy_repositories\Event\RepoUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * DrupalEasy Notify event subscriber.
 */
class DrupaleasyNotifySubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * Constructs a DrupaleasyNotifySubscriber object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * Kernel request event handler.
   *
   * @param \Drupal\drupaleasy_repositories\Event\RepoUpdatedEvent $event
   *   Repository changed event.
   */
  public function onRepoUpdated(RepoUpdatedEvent $event): void {
    /** @var \Drupal\user\UserInterface $author */
    $author = $event->node->uid->entity;
    $this->messenger->addStatus($this->t('The repository named %repo_name has been @action (@repo_url). The repository node is owned by @author_name (@author_id).', [
      '%repo_name' => $event->node->getTitle(),
      '@repo_url' => $event->node->toLink()->getUrl()->toString(),
      '@action' => $event->action,
      '@author_id' => $event->node->uid->target_id,
      '@author_name' => $author->name->value,
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      RepoUpdatedEvent::REPO_UPDATED => ['onRepoUpdated'],
    ];
  }

}
