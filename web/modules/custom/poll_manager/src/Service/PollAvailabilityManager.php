<?php

namespace Drupal\poll_manager\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\poll_manager\Entity\PollQuestion;

/**
 * Service to check poll availability and global settings.
 */
class PollAvailabilityManager
{

    public function __construct(
        protected ConfigFactoryInterface $configFactory,
        protected EntityTypeManagerInterface $entityTypeManager,
    ) {
    }

    /**
     * Returns whether voting is globally enabled.
     */
    public function isGlobalVotingEnabled(): bool
    {
        return (bool) $this->configFactory
            ->get('poll_manager.settings')
            ->get('voting_enabled');
    }

    /**
     * Checks if a specific poll is open for voting.
     */
    public function isPollOpen(PollQuestion $question): bool
    {
        if (!$this->isGlobalVotingEnabled()) {
            return FALSE;
        }
        return (bool) $question->get('status')->value;
    }

    /**
     * Checks if results can be shown for a specific poll.
     */
    public function canShowResults(PollQuestion $question): bool
    {
        return (bool) $question->get('show_results')->value;
    }

}
