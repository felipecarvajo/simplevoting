<?php

namespace Drupal\poll_manager\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Poll Choice entity.
 *
 * Each choice belongs to a PollQuestion and represents one answer option.
 *
 * @ContentEntityType(
 *   id = "poll_choice",
 *   label = @Translation("Poll Choice"),
 *   label_collection = @Translation("Poll Choices"),
 *   label_singular = @Translation("poll choice"),
 *   label_plural = @Translation("poll choices"),
 *   label_count = @PluralTranslation(
 *     singular = "@count poll choice",
 *     plural = "@count poll choices",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "form" = {
 *       "default" = "Drupal\poll_manager\Form\PollChoiceForm",
 *       "add"     = "Drupal\poll_manager\Form\PollChoiceForm",
 *       "edit"    = "Drupal\poll_manager\Form\PollChoiceForm",
 *       "delete"  = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "poll_choice",
 *   admin_permission = "administer poll questions",
 *   entity_keys = {
 *     "id"    = "id",
 *     "uuid"  = "uuid",
 *     "label" = "title",
 *   },
 *   links = {
 *     "add-form"    = "/admin/poll-manager/questions/{poll_question}/choices/add",
 *     "edit-form"   = "/admin/poll-manager/choices/{poll_choice}/edit",
 *     "delete-form" = "/admin/poll-manager/choices/{poll_choice}/delete",
 *   },
 * )
 */
class PollChoice extends ContentEntityBase
{

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array
  {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'poll_question')
      ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Choice Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => 1])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', ['type' => 'string_textarea', 'weight' => 2])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setSettings([
        'file_extensions' => 'png jpg jpeg webp',
        'alt_field' => TRUE,
      ])
      ->setDisplayOptions('form', ['type' => 'image_image', 'weight' => 3])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('Defines the display order of choices.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 4])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    return $fields;
  }

}
