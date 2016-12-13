<?php

namespace Drupal\Core\Entity\Plugin\DisplayComponent;

use Drupal\Core\Entity\DisplayComponentHandlerBase;

/**
 * Provides a component handler to manage entity extra fields.
 *
 * @DisplayComponent(
 *   id = "extra_field"
 * )
 */
class ExtraFieldDisplayComponentHandler extends DisplayComponentHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function prepareDisplayComponents(array &$components, array &$hidden_components) {
    // Fill in defaults for extra fields.
    $extra_fields = $this->fetchExtraFields();
    foreach ($extra_fields as $name => $definition) {
      if (!isset($components[$name]) && !isset($hidden_components[$name])) {
        // Extra fields are visible by default unless they explicitly say so.
        if (!isset($definition['visible']) || $definition['visible'] == TRUE) {
          $components[$name] = array(
            'weight' => $definition['weight']
          );
        }
        else {
          $hidden_components[$name] = TRUE;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasElement($name) {
    $extra_fields = $this->fetchExtraFields();
    return isset($extra_fields[$name]);
  }

  /**
   * Fetches all the extra fields.
   */
  protected function fetchExtraFields() {
    $context = $this->context['display_context'] == 'view' ? 'display' : $this->context['display_context'];
    $extra_fields = \Drupal::entityManager()->getExtraFields($this->context['entity_type'], $this->context['bundle']);
    return isset($extra_fields[$context]) ? $extra_fields[$context] : array();
  }

}
