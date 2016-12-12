<?php

namespace Drupal\Core\Entity;

use Drupal\Component\Plugin\PluginBase;

/**
 * Provides a base class for DisplayComponentHandler plugins.
 */
abstract class DisplayComponentHandlerBase extends PluginBase implements DisplayComponentHandlerInterface {

  /**
   * The context in which the handler is being used.
   *
   * @var array
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  public function setContext(array $context) {
    $this->context = $context;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareDisplayComponents(array &$components, array &$hidden_components) {
  }

  /**
   * {@inheritdoc}
   */
  public function hasElement($name) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function massageIn($name, array $options) {
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function massageOut($properties) {
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderer($name, array $options) {
  }

}
