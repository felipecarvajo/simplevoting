<?php

namespace Drupal\poll_manager\Controller\Api;

use Drupal\Core\Controller\ControllerBase;
use Drupal\poll_manager\Service\PollAvailabilityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * API controller for listing and retrieving poll questions.
 */
class PollQuestionApiController extends ControllerBase
{

  public function __construct(
    protected PollAvailabilityManager $availabilityManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('poll_manager.availability_manager'),
    );
  }

  /**
   * GET /api/v1/polls/questions
   */
  public function list(): JsonResponse
  {
    if (!$this->availabilityManager->isGlobalVotingEnabled()) {
      return new JsonResponse([
        'error' => 'Voting is currently disabled.',
        'error_code' => 'VOTING_DISABLED',
      ], 503);
    }

    $questions = $this->entityTypeManager()
      ->getStorage('poll_question')
      ->loadByProperties(['status' => 1]);

    $data = [];
    foreach ($questions as $question) {
      if (!$question instanceof \Drupal\poll_manager\Entity\PollQuestion) {
        continue;
      }
      $data[] = [
        'id' => $question->uuid(),
        'title' => $question->label(),
        'voting_enabled' => $this->availabilityManager->isPollOpen($question),
        'results_visible' => $this->availabilityManager->canShowResults($question),
      ];
    }

    return new JsonResponse($data);
  }

  /**
   * GET /api/v1/polls/questions/{uuid}
   */
  public function detail(string $uuid): JsonResponse
  {
    $questions = $this->entityTypeManager()
      ->getStorage('poll_question')
      ->loadByProperties(['uuid' => $uuid]);

    if (!$questions) {
      return new JsonResponse(['error' => 'Poll not found.', 'error_code' => 'QUESTION_NOT_FOUND'], 404);
    }

    /** @var \Drupal\poll_manager\Entity\PollQuestion $question */
    $question = reset($questions);

    $choices = $this->entityTypeManager()
      ->getStorage('poll_choice')
      ->loadByProperties(['question_id' => $question->id()]);

    $choicesData = [];
    foreach ($choices as $choice) {
      if (!$choice instanceof \Drupal\poll_manager\Entity\PollChoice) {
        continue;
      }
      $choicesData[] = [
        'id' => (int) $choice->id(),
        'title' => $choice->label(),
        'description' => $choice->get('description')->value ?? '',
      ];
    }

    return new JsonResponse([
      'id' => $question->uuid(),
      'title' => $question->label(),
      'voting_enabled' => $this->availabilityManager->isPollOpen($question),
      'results_visible' => $this->availabilityManager->canShowResults($question),
      'choices' => $choicesData,
    ]);
  }

}
