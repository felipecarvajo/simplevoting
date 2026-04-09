<?php

namespace Drupal\poll_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\poll_manager\Entity\PollQuestion;

/**
 * Controller for managing choices of a PollQuestion.
 */
class PollChoiceAdminController extends ControllerBase
{

  /**
   * Lists all choices for a given poll question with edit/delete/add links.
   */
  public function listing(PollQuestion $poll_question): array
  {
    $choices = $this->entityTypeManager()
      ->getStorage('poll_choice')
      ->loadByProperties(['question_id' => $poll_question->id()]);

    // Sort by weight.
    uasort($choices, fn($a, $b) => $a->get('weight')->value <=> $b->get('weight')->value);

    $rows = [];
    foreach ($choices as $choice) {
      if (!$choice instanceof \Drupal\poll_manager\Entity\PollChoice) {
        continue;
      }
      $rows[] = [
        $choice->id(),
        $choice->label(),
        $choice->get('description')->value ?: '—',
        [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('entity.poll_choice.edit_form', ['poll_choice' => $choice->id()]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('entity.poll_choice.delete_form', ['poll_choice' => $choice->id()]),
              ],
            ],
          ],
        ],
      ];
    }

    $addUrl = Url::fromRoute('entity.poll_choice.add_form', ['poll_question' => $poll_question->id()]);

    return [
      'add_button' => [
        '#type' => 'link',
        '#title' => $this->t('+ Add Choice'),
        '#url' => $addUrl,
        '#attributes' => ['class' => ['button', 'button--primary', 'button-action']],
        '#weight' => -10,
      ],
      'table' => [
        '#type' => 'table',
        '#caption' => $this->t('Choices for poll: %title', ['%title' => $poll_question->label()]),
        '#header' => [
          $this->t('ID'),
          $this->t('Title'),
          $this->t('Description'),
          $this->t('Operations'),
        ],
        '#rows' => $rows,
        '#empty' => $this->t('No choices created. Click on "+ Add Choice" to start.'),
      ],
      'back' => [
        '#type' => 'link',
        '#title' => $this->t('← Back to poll questions'),
        '#url' => Url::fromRoute('poll_manager.question_list'),
        '#attributes' => ['class' => ['poll-back-link']],
        '#weight' => 10,
      ],
    ];
  }

}
