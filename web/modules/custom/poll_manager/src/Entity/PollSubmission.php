<?php

namespace Drupal\poll_manager\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Poll Submission entity.
 *
 * Stores each individual vote cast by a user or external identifier.
 *
 * @ContentEntityType(
 *   id = "poll_submission",
 *   label = @Translation("Poll Submission"),
 *   label_collection = @Translation("Poll Submissions"),
 *   label_singular = @Translation("poll submission"),
 *   label_plural = @Translation("poll submissions"),
 *   label_count = @PluralTranslation(
 *     singular = "@count poll submission",
 *     plural = "@count poll submissions",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *   },
 *   base_table = "poll_submission",
 *   admin_permission = "administer poll questions",
 *   entity_keys = {
 *     "id"   = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class PollSubmission extends ContentEntityBase
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
      ->setSetting('target_type', 'poll_question');

    $fields['choice_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Choice'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'poll_choice');

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'user');

    $fields['external_voter_identifier'] = BaseFieldDefinition::create('string')
      ->setLabel(t('External Voter Identifier'))
      ->setDescription(t('Unique identifier for votes coming from the API.'))
      ->setSetting('max_length', 128)
      ->setDefaultValue('');

    $fields['source'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Source'))
      ->setDescription(t('Where the vote came from: "cms" or "api".'))
      ->setSetting('max_length', 10)
      ->setDefaultValue('cms');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    return $fields;
  }

}
