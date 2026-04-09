<?php

namespace Drupal\poll_manager\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * List builder for PollQuestion entities.
 */
class PollQuestionListBuilder extends EntityListBuilder
{

  /**
   * {@inheritdoc}
   */
  public function render(): array
  {
    $build = parent::render();

    $build['add_button'] = [
      '#type' => 'link',
      '#title' => $this->t('+ Add Poll Question'),
      '#url' => Url::fromRoute('entity.poll_question.add_form'),
      '#attributes' => [
        'class' => ['button', 'button--primary', 'button-action'],
      ],
      '#weight' => -10,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array
  {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['status'] = $this->t('Status');
    $header['show_results'] = $this->t('Show Results');
    $header['choices'] = $this->t('Choices');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array
  {
    /** @var \Drupal\poll_manager\Entity\PollQuestion $entity */
    $row['id'] = $entity->id();
    $row['title'] = Link::createFromRoute(
      $entity->label(),
      'entity.poll_question.edit_form',
      ['poll_question' => $entity->id()]
    );
    $row['status'] = $entity->get('status')->value ? $this->t('Yes') : $this->t('No');
    $row['show_results'] = $entity->get('show_results')->value ? $this->t('Yes') : $this->t('No');
    $row['choices'] = Link::createFromRoute(
      $this->t('Manage Choices'),
      'poll_manager.choice_listing',
      ['poll_question' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
