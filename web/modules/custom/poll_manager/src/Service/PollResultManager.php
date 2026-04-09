<?php

namespace Drupal\poll_manager\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\poll_manager\Entity\PollQuestion;

/**
 * Service to aggregate and calculate poll results.
 */
class PollResultManager
{

    public function __construct(
        protected EntityTypeManagerInterface $entityTypeManager,
        protected Connection $database,
    ) {
    }

    /**
     * Returns calculated results for a given poll question.
     */
    public function getResults(PollQuestion $question): array
    {
        $questionId = (int) $question->id();

        // Aggregate votes per choice.
        $query = $this->database->select('poll_submission', 'ps');
        $query->addField('ps', 'choice_id');
        $query->addExpression('COUNT(ps.id)', 'total');
        $query->condition('ps.question_id', $questionId);
        $query->groupBy('ps.choice_id');
        $rows = $query->execute()->fetchAllKeyed();

        $totalVotes = array_sum($rows);

        $choices = $this->entityTypeManager
            ->getStorage('poll_choice')
            ->loadByProperties(['question_id' => $questionId]);

        $results = [];
        foreach ($choices as $choice) {
            $count = (int) ($rows[$choice->id()] ?? 0);
            $results[] = [
                'id' => $choice->id(),
                'title' => $choice->label(),
                'votes' => $count,
                'percentage' => $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0,
            ];
        }

        return [
            'total_votes' => $totalVotes,
            'choices' => $results,
        ];
    }

}
