<?php

namespace Drupal\poll_manager\Controller\Api;

use Drupal\Core\Controller\ControllerBase;
use Drupal\poll_manager\Service\PollResultManager;
use Drupal\poll_manager\Service\PollAvailabilityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * API controller for displaying poll results.
 */
class PollResultApiController extends ControllerBase
{

  public function __construct(
    protected PollResultManager $resultManager,
    protected PollAvailabilityManager $availabilityManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('poll_manager.result_manager'),
      $container->get('poll_manager.availability_manager'),
    );
  }

  /**
   * GET /api/v1/polls/questions/{uuid}/results
   */
  public function show(string $uuid): JsonResponse
  {
    $questions = $this->entityTypeManager()
      ->getStorage('poll_question')
      ->loadByProperties(['uuid' => $uuid]);

    if (!$questions) {
      return new JsonResponse(['error' => 'Poll not found.', 'error_code' => 'QUESTION_NOT_FOUND'], 404);
    }

    /** @var \Drupal\poll_manager\Entity\PollQuestion $question */
    $question = reset($questions);

    if (!$this->availabilityManager->canShowResults($question)) {
      return new JsonResponse([
        'error' => 'Results are not available for this poll.',
        'error_code' => 'RESULTS_HIDDEN',
      ], 403);
    }

    $results = $this->resultManager->getResults($question);

    return new JsonResponse([
      'id' => $question->uuid(),
      'title' => $question->label(),
      'total_votes' => $results['total_votes'],
      'choices' => $results['choices'],
    ]);
  }

}
