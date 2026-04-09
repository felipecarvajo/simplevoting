<?php

namespace Drupal\poll_manager\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\poll_manager\Service\PollAvailabilityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\poll_manager\Form\PollVoteForm;

/**
 * Provides the Poll Vote block.
 *
 * Displays the most recently active poll with its voting form.
 *
 * @Block(
 *   id = "poll_manager_vote_block",
 *   admin_label = @Translation("Poll Vote Form"),
 *   category = @Translation("Poll Manager"),
 * )
 */
class PollVoteBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    protected PollAvailabilityManager $availabilityManager,
    protected FormBuilderInterface $formBuilder,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('poll_manager.availability_manager'),
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array
  {
    if (!$this->availabilityManager->isGlobalVotingEnabled()) {
      return ['#markup' => $this->t('Voting is currently unavailable.')];
    }

    // Load the most recent active poll.
    $questions = \Drupal::entityTypeManager()
      ->getStorage('poll_question')
      ->loadByProperties(['status' => 1]);

    if (empty($questions)) {
      return ['#markup' => $this->t('No active polls available.')];
    }

    // Use the last created active question.
    uasort($questions, fn($a, $b) => $a->get('created')->value <=> $b->get('created')->value);
    $question = end($questions);

    return $this->formBuilder->getForm(PollVoteForm::class, $question);
  }

}
