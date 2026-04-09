<?php

namespace Drupal\poll_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\poll_manager\Service\VoteManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the public poll listing page.
 */
class PollListController extends ControllerBase
{

  public function __construct(
    protected VoteManager $voteManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('poll_manager.vote_manager'),
    );
  }

  /**
   * Renders a listing of all active poll questions.
   */
  public function listing(): array
  {
    $questions = $this->entityTypeManager()
      ->getStorage('poll_question')
      ->loadByProperties(['status' => 1]);

    if (empty($questions)) {
      return [
        '#markup' => $this->t('No polls available at the moment.'),
      ];
    }

    $userId = (int) $this->currentUser()->id();

    $items = [];
    foreach ($questions as $question) {
      if (!$question instanceof \Drupal\poll_manager\Entity\PollQuestion) {
        continue;
      }
      $alreadyVoted = $this->voteManager->hasVoted($question, $userId);
      $url = Url::fromRoute('poll_manager.vote', ['poll_question' => $question->id()]);

      if ($alreadyVoted) {
        $linkLabel = $this->t('View results');
        $linkClass = ['poll-vote-link', 'poll-vote-link--voted'];
      } else {
        $linkLabel = $this->t('Vote');
        $linkClass = ['poll-vote-link'];
      }

      $items[] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['poll-question-item']],
        'title' => [
          '#markup' => '<span class="poll-question-title">' . htmlspecialchars($question->label(), ENT_QUOTES, 'UTF-8') . '</span>',
        ],
        'link' => [
          '#type' => 'link',
          '#title' => $linkLabel,
          '#url' => $url,
          '#attributes' => ['class' => $linkClass],
        ],
      ];
    }

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['poll-list']],
      '#cache' => [
        'contexts' => ['user'],
      ],
      'heading' => [
        '#markup' => '<h2 class="poll-list-title">' . $this->t('Available Polls') . '</h2>',
      ],
      'items' => $items,
    ];
  }

}
