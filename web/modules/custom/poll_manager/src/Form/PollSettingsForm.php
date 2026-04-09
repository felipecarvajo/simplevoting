<?php

namespace Drupal\poll_manager\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Poll Manager global settings.
 */
class PollSettingsForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array
  {
    return ['poll_manager.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'poll_manager_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $config = $this->config('poll_manager.settings');

    $form['voting_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable voting'),
      '#description' => $this->t('When unchecked, all voting is disabled — both in the CMS and via the API.'),
      '#default_value' => (bool) $config->get('voting_enabled'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $this->config('poll_manager.settings')
      ->set('voting_enabled', (bool) $form_state->getValue('voting_enabled'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
