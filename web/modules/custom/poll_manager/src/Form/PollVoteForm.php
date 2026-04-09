<?php

namespace Drupal\poll_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\poll_manager\Entity\PollQuestion;
use Drupal\poll_manager\Entity\PollChoice;
use Drupal\poll_manager\Service\VoteManager;
use Drupal\poll_manager\Service\PollAvailabilityManager;
use Drupal\poll_manager\Service\PollResultManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Poll form displayed on the CMS poll page.
 */
class PollVoteForm extends FormBase
{

  public function __construct(
    protected VoteManager $voteManager,
    protected PollAvailabilityManager $availabilityManager,
    protected PollResultManager $resultManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('poll_manager.vote_manager'),
      $container->get('poll_manager.availability_manager'),
      $container->get('poll_manager.result_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'poll_manager_vote_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\poll_manager\Entity\PollQuestion|null $poll_question
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?PollQuestion $poll_question = NULL): array
  {
    if (!$poll_question) {
      $form['message'] = ['#markup' => $this->t('Poll not found.')];
      return $form;
    }

    $form['#poll'] = $poll_question;
    $form['poll_id'] = [
      '#type' => 'hidden',
      '#value' => $poll_question->id(),
    ];

    // Global and poll specific availability check.
    if (!$this->availabilityManager->isPollOpen($poll_question)) {
      $form['message'] = ['#markup' => $this->t('This poll is currently closed.')];
      return $form;
    }

    $userId = (int) $this->currentUser()->id();

    // User already voted — show results if allowed.
    if ($this->voteManager->hasVoted($poll_question, $userId)) {
      return $this->buildResultsSection($form, $poll_question);
    }

    // Build choices as radio cards with image + description.
    $storage = \Drupal::entityTypeManager()->getStorage('poll_choice');
    $choices = $storage->loadByProperties(['question_id' => $poll_question->id()]);

    uasort($choices, fn($a, $b) => $a->get('weight')->value <=> $b->get('weight')->value);

    $form['title'] = [
      '#markup' => '<h2 class="poll-question-title">' . htmlspecialchars($poll_question->label(), ENT_QUOTES) . '</h2>',
    ];

    $choiceOptions = [];
    foreach ($choices as $choice) {
      if (!$choice instanceof PollChoice) {
        continue;
      }

      $imageHtml = '';
      $imageField = $choice->get('image');
      if (!$imageField->isEmpty()) {
        /** @var \Drupal\file\FileInterface $file */
        $file = $imageField->entity;
        if ($file) {
          $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          $alt = $imageField->alt ?: $choice->label();
          $imageHtml = '<img src="' . htmlspecialchars($url, ENT_QUOTES) . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '" class="poll-choice-img">';
        }
      }

      $description = $choice->get('description')->value;
      $descHtml = $description
        ? '<span class="poll-choice-desc">' . htmlspecialchars($description, ENT_QUOTES) . '</span>'
        : '';

      $choiceOptions[$choice->id()] = Markup::create(
        '<span class="poll-choice-body">'
        . $imageHtml
        . '<span class="poll-choice-content">'
        . '<span class="poll-choice-title">' . htmlspecialchars($choice->label(), ENT_QUOTES) . '</span>'
        . $descHtml
        . '</span>'
        . '</span>'
      );
    }

    $form['choice_id'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select an option'),
      '#options' => $choiceOptions,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Vote'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];

    $form['back'] = $this->buildBackButton();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    /** @var PollQuestion $poll */
    $poll = $form['#poll'];
    $choiceId = (int) $form_state->getValue('choice_id');
    $userId = (int) $this->currentUser()->id();

    /** @var PollChoice $choice */
    $choice = \Drupal::entityTypeManager()->getStorage('poll_choice')->load($choiceId);

    if (!$choice) {
      $this->messenger()->addError($this->t('Invalid choice selected.'));
      return;
    }

    $result = $this->voteManager->castVote($poll, $choice, $userId, 'cms');

    if ($result['success']) {
      $this->messenger()->addStatus($result['message']);
    } else {
      $this->messenger()->addError($result['message']);
    }
  }

  /**
   * Builds the results display section.
   */
  protected function buildResultsSection(array $form, PollQuestion $question): array
  {
    $form['title'] = [
      '#markup' => '<h2 class="poll-question-title">' . htmlspecialchars($question->label(), ENT_QUOTES) . '</h2>',
    ];

    if (!$this->availabilityManager->canShowResults($question)) {
      $form['message'] = ['#markup' => $this->t('Thank you for voting! Results are hidden for this poll.')];
    } else {
      $results = $this->resultManager->getResults($question);
      $items = [];
      foreach ($results['choices'] as $c) {
        $items[] = $this->t('@title: @count votes (@pct%)', [
          '@title' => $c['title'],
          '@count' => $c['votes'],
          '@pct' => $c['percentage'],
        ]);
      }

      $form['results'] = [
        '#theme' => 'item_list',
        '#title' => $this->t('Current Results (Total: @total votes)', ['@total' => $results['total_votes']]),
        '#items' => $items,
      ];
    }

    $form['back'] = $this->buildBackButton();

    return $form;
  }

  /**
   * Builds a back link to the poll list.
   */
  protected function buildBackButton(): array
  {
    return [
      '#type' => 'link',
      '#title' => $this->t('← Back to polls'),
      '#url' => Url::fromRoute('poll_manager.list'),
      '#attributes' => ['class' => ['poll-back-link']],
      '#weight' => 10,
    ];
  }

}
