<?php

namespace Drupal\poll_manager\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for adding and editing PollQuestion entities.
 */
class PollQuestionForm extends ContentEntityForm
{

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int
  {
    $status = parent::save($form, $form_state);
    $entity = $this->entity;

    if ($status === SAVED_NEW) {
      $this->messenger()->addStatus($this->t('Poll question %title created.', ['%title' => $entity->label()]));
    } else {
      $this->messenger()->addStatus($this->t('Poll question %title updated.', ['%title' => $entity->label()]));
    }

    $form_state->setRedirect('entity.poll_question.collection');
    return $status;
  }

}
