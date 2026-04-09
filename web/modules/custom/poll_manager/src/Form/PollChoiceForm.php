<?php

namespace Drupal\poll_manager\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for adding and editing PollChoice entities.
 */
class PollChoiceForm extends ContentEntityForm
{

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    // If a poll_question was passed via the route (add-form path), pre-fill
    // the question_id field and hide it — no need to select it again.
    $route_match = \Drupal::routeMatch();
    $poll_question = $route_match->getParameter('poll_question');
    if ($poll_question && $this->entity->isNew()) {
      $this->entity->set('question_id', $poll_question);
    }

    $form = parent::buildForm($form, $form_state);

    // Hide the question_id widget when it's already determined by context.
    if ($poll_question) {
      $form['question_id']['#access'] = FALSE;

      // Resolve the question entity (route parameter can be an int or entity).
      $question_entity = is_object($poll_question)
        ? $poll_question
        : \Drupal::entityTypeManager()->getStorage('poll_question')->load($poll_question);

      if ($question_entity) {
        $form['question_context'] = [
          '#markup' => '<div class="poll-choice-question-context">'
            . '<span class="poll-choice-question-label">' . $this->t('Poll Question') . ':</span> '
            . '<span class="poll-choice-question-title">' . htmlspecialchars($question_entity->label(), ENT_QUOTES) . '</span>'
            . '</div>',
          '#weight' => -20,
        ];
      }
    }
    // On edit form, show the question title from the already-set entity value.
    elseif (!$this->entity->isNew()) {
      $question_entity = $this->entity->get('question_id')->entity;
      if ($question_entity) {
        $form['question_context'] = [
          '#markup' => '<div class="poll-choice-question-context">'
            . '<span class="poll-choice-question-label">' . $this->t('Poll Question') . ':</span> '
            . '<span class="poll-choice-question-title">' . htmlspecialchars($question_entity->label(), ENT_QUOTES) . '</span>'
            . '</div>',
          '#weight' => -20,
        ];
        $form['question_id']['#access'] = FALSE;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int
  {
    $status = parent::save($form, $form_state);
    $entity = $this->entity;

    if ($status === SAVED_NEW) {
      $this->messenger()->addStatus($this->t('Choice %title created successfully.', ['%title' => $entity->label()]));
    } else {
      $this->messenger()->addStatus($this->t('Choice %title updated successfully.', ['%title' => $entity->label()]));
    }

    // Redirect back to the question's choice listing page.
    $question_id = $entity->get('question_id')->target_id;
    $form_state->setRedirect('poll_manager.choice_listing', ['poll_question' => $question_id]);
    return $status;
  }

}
