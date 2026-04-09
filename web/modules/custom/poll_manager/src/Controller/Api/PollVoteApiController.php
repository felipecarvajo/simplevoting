<?php

namespace Drupal\poll_manager\Controller\Api;

use Drupal\Core\Controller\ControllerBase;
use Drupal\poll_manager\Service\VoteManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * API controller for registering votes.
 */
class PollVoteApiController extends ControllerBase
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
   * POST /api/v1/polls/questions/{uuid}/vote
   *
   * Expected JSON body: { "choice_id": 1, "voter_id": "optional-external-id" }
   */
  public function submit(string $uuid, Request $request): JsonResponse
  {
    $body = json_decode($request->getContent(), TRUE);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($body['choice_id'])) {
      return new JsonResponse([
        'error' => 'Invalid request body. Expected "choice_id".',
        'error_code' => 'INVALID_REQUEST',
      ], 400);
    }

    $choiceId = (int) $body['choice_id'];
    $externalId = $body['voter_id'] ?? '';

    $questions = $this->entityTypeManager()
      ->getStorage('poll_question')
      ->loadByProperties(['uuid' => $uuid]);

    if (!$questions) {
      return new JsonResponse(['error' => 'Poll not found.', 'error_code' => 'QUESTION_NOT_FOUND'], 404);
    }

    /** @var \Drupal\poll_manager\Entity\PollQuestion $question */
    $question = reset($questions);

    /** @var \Drupal\poll_manager\Entity\PollChoice $choice */
    $choice = $this->entityTypeManager()
      ->getStorage('poll_choice')
      ->load($choiceId);

    if (!$choice) {
      return new JsonResponse(['error' => 'Choice not found.', 'error_code' => 'INVALID_CHOICE'], 422);
    }

    $userId = (int) $this->currentUser()->id();

    $result = $this->voteManager->castVote(
      $question,
      $choice,
      $userId,
      'api',
      (string) $externalId
    );

    if ($result['success']) {
      return new JsonResponse(['message' => $result['message']], 201);
    }

    return new JsonResponse([
      'error' => $result['message'],
      'error_code' => $result['error_code'],
    ], 422);
  }

}
