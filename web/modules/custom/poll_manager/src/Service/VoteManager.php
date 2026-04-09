<?php

namespace Drupal\poll_manager\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\poll_manager\Entity\PollQuestion;
use Drupal\poll_manager\Entity\PollChoice;
use Drupal\poll_manager\Entity\PollSubmission;
use Drupal\Core\Database\IntegrityConstraintViolationException;

/**
 * Service to handle voting logic.
 */
class VoteManager
{

    public function __construct(
        protected PollAvailabilityManager $availabilityManager,
        protected EntityTypeManagerInterface $entityTypeManager,
        protected Connection $database,
        protected LoggerChannelFactoryInterface $loggerFactory,
    ) {
    }

    /**
     * Casts a vote.
     *
     * @return array
     *   ['success' => bool, 'message' => string, 'error_code' => string|null]
     */
    public function castVote(PollQuestion $question, PollChoice $choice, int $userId, string $source = 'cms', string $externalIdentifier = ''): array
    {
        $logger = $this->loggerFactory->get('poll_manager');

        // 1. Check availability.
        if (!$this->availabilityManager->isPollOpen($question)) {
            $logger->warning('Vote attempt on closed poll @qid.', ['@qid' => $question->id()]);
            return [
                'success' => FALSE,
                'message' => 'This poll is currently closed.',
                'error_code' => 'QUESTION_CLOSED',
            ];
        }

        // 2. Validate choice belongs to question.
        if ((int) $choice->get('question_id')->target_id !== (int) $question->id()) {
            $logger->error('Choice @cid does not belong to poll @qid.', ['@cid' => $choice->id(), '@qid' => $question->id()]);
            return [
                'success' => FALSE,
                'message' => 'Invalid choice for this poll.',
                'error_code' => 'INVALID_CHOICE',
            ];
        }

        // 3. Application-level duplicate check.
        if ($this->hasVoted($question, $userId, $externalIdentifier)) {
            return [
                'success' => FALSE,
                'message' => 'You have already voted on this poll.',
                'error_code' => 'VOTE_DUPLICATE',
            ];
        }

        // 4. Persist the vote.
        try {
            $submission = PollSubmission::create([
                'question_id' => $question->id(),
                'choice_id' => $choice->id(),
                'user_id' => $userId,
                'external_voter_identifier' => $externalIdentifier,
                'source' => $source,
            ]);
            $submission->save();

            $logger->info('Vote registered: Poll @qid, Choice @cid, User @uid, Source @source.', [
                '@qid' => $question->id(),
                '@cid' => $choice->id(),
                '@uid' => $userId,
                '@source' => $source,
            ]);

            return ['success' => TRUE, 'message' => 'Your vote has been counted.', 'error_code' => NULL];
        } catch (IntegrityConstraintViolationException $e) {
            $logger->notice('Concurrent duplicate vote attempt on poll @qid.', ['@qid' => $question->id()]);
            return [
                'success' => FALSE,
                'message' => 'You have already voted on this poll.',
                'error_code' => 'VOTE_DUPLICATE',
            ];
        } catch (\Exception $e) {
            $logger->error('Failed to register vote: @msg', ['@msg' => $e->getMessage()]);
            return [
                'success' => FALSE,
                'message' => 'An error occurred while registering your vote.',
                'error_code' => 'INTERNAL_ERROR',
            ];
        }
    }

    /**
     * Checks if a user or external identifier has already voted.
     */
    public function hasVoted(PollQuestion $question, int $userId, string $externalIdentifier = ''): bool
    {
        $query = $this->database->select('poll_submission', 'ps')
            ->fields('ps', ['id'])
            ->condition('question_id', $question->id());

        if ($externalIdentifier) {
            $query->condition('external_voter_identifier', $externalIdentifier);
        } else {
            $query->condition('user_id', $userId);
        }

        return (bool) $query->range(0, 1)->execute()->fetchField();
    }

}
