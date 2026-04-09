<?php

namespace Drupal\poll_manager\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Poll Question entity.
 *
 * @ContentEntityType(
 *   id = "poll_question",
 *   label = @Translation("Poll Question"),
 *   label_collection = @Translation("Poll Questions"),
 *   label_singular = @Translation("poll question"),
 *   label_plural = @Translation("poll questions"),
 *   label_count = @PluralTranslation(
 *     singular = "@count poll question",
 *     plural = "@count poll questions",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "list_builder" = "Drupal\poll_manager\Entity\PollQuestionListBuilder",
 *     "form" = {
 *       "default" = "Drupal\poll_manager\Form\PollQuestionForm",
 *       "add"     = "Drupal\poll_manager\Form\PollQuestionForm",
 *       "edit"    = "Drupal\poll_manager\Form\PollQuestionForm",
 *       "delete"  = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "poll_question",
 *   admin_permission = "administer poll questions",
 *   entity_keys = {
 *     "id"     = "id",
 *     "uuid"   = "uuid",
 *     "label"  = "title",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical"   = "/admin/poll-manager/questions/{poll_question}",
 *     "add-form"    = "/admin/poll-manager/questions/add",
 *     "edit-form"   = "/admin/poll-manager/questions/{poll_question}/edit",
 *     "delete-form" = "/admin/poll-manager/questions/{poll_question}/delete",
 *     "collection"  = "/admin/poll-manager/questions",
 *   },
 * )
 */
class PollQuestion extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether this poll is open for voting.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 10])
      ->setDisplayConfigurable('form', TRUE);

    $fields['show_results'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Show results after voting'))
      ->setDescription(t('Whether the total votes should be displayed to the user after voting.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 11])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

}
