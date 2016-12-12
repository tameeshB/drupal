<?php

namespace Drupal\Core\Entity\Plugin\DisplayComponent;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Field\WidgetPluginManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\DisplayComponentHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a component handler to manage entity fields.
 *
 * @DisplayComponent(
 *   id = "field"
 * )
 */
class FieldDisplayComponentHandler extends DisplayComponentHandlerBase implements ContainerFactoryPluginInterface {

  /**
   * The field formatter plugin manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatterPluginManager;

  /**
   * The field widget plugin manager.
   *
   * @var \Drupal\Core\Field\WidgetPluginManager
   */
  protected $widgetPluginManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;


  /**
   * Constructs a FieldDisplayComponentHandler object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FormatterPluginManager $formatter_plugin_manager
   *   The field formatter plugin manager.
   * @param \Drupal\Core\Field\WidgetPluginManager $widget_plugin_manager
   *   The field widget plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, FormatterPluginManager $formatter_plugin_manager, WidgetPluginManager $widget_plugin_manager, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->formatterPluginManager = $formatter_plugin_manager;
    $this->widgetPluginManager = $widget_plugin_manager;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('plugin.manager.field.formatter'),
      $container->get('plugin.manager.field.widget'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function massageIn($name, array $options) {
    $field_definition = $this->getFieldDefinition($name);
    if (!isset($field_definition)) {
      // The field in process of removal from display.
      return $options;
    }
    if ($this->context['display_context'] == 'view') {
      return $this->formatterPluginManager->prepareConfiguration($field_definition->getType(), $options);
    }
    else {
      return $this->widgetPluginManager->prepareConfiguration($field_definition->getType(), $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageOut($properties) {
    // Do not store options for fields whose display is not set to be
    // configurable.
    foreach ($this->getDisplayableFields() as $field_name => $definition) {
      if (!$definition->isDisplayConfigurable($this->context['display_context'])) {
        unset($properties['content'][$field_name]);
        unset($properties['hidden'][$field_name]);
      }
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareDisplayComponents(array &$components, array &$hidden_components) {
    if ($this->context['display_context'] == 'view') {
      $plugin_manager = $this->formatterPluginManager;
    }
    else {
      $plugin_manager = $this->widgetPluginManager;
    }

    // Fill in defaults for fields.
    $fields = $this->getDisplayableFields();
    foreach ($fields as $name => $definition) {
      if (!$definition->isDisplayConfigurable($this->context['display_context']) || (!isset($components[$name]) && !isset($hidden_components[$name]))) {
        $options = $definition->getDisplayOptions($this->context['display_context']);

        if (!empty($options['type']) && $options['type'] == 'hidden') {
          $hidden_components[$name] = TRUE;
        }
        elseif ($options) {
          $components[$name] = $plugin_manager->prepareConfiguration($definition->getType(), $options);
        }
        // Note: (base) fields that do not specify display options are not
        // tracked in the display at all, in order to avoid cluttering the
        // configuration that gets saved back.
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderer($name, array $options) {
    if (isset($options['type']) && ($definition = $this->getFieldDefinition($name))) {
      if ($this->context['display_context'] == 'view') {
        $plugin_manager = $this->formatterPluginManager;
        $mode_key = 'view_mode';
      }
      else {
        $plugin_manager = $this->widgetPluginManager;
        $mode_key = 'form_mode';
      }

      return $plugin_manager->getInstance(array(
        'field_definition' => $definition,
        $mode_key => $this->context['mode'],
        // No need to prepare, defaults have been merged when the options were
        // written in the display.
        'prepare' => FALSE,
        'configuration' => $options,
      ));
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasElement($name) {
    $field_definition = $this->getFieldDefinition($name);
    return isset($field_definition);
  }

  /**
   * Returns the field definition of a field.
   */
  protected function getFieldDefinition($field_name) {
    $definitions = $this->getDisplayableFields();
    return isset($definitions[$field_name]) ? $definitions[$field_name] : NULL;
  }

  /**
   * Returns the definitions of the fields that are candidate for display.
   */
  protected function getDisplayableFields() {
    $entity_type = $this->context['entity_type'];
    $bundle = $this->context['bundle'];
    $display_context = $this->context['display_context'];
    $definitions = $this->entityManager->getFieldDefinitions($entity_type, $bundle);

    // The display only cares about fields that specify display options.
    // Discard base fields that are not rendered through formatters / widgets.
    return array_filter($definitions, function (FieldDefinitionInterface $definition) use ($display_context) {
      return $definition->getDisplayOptions($display_context);
    });
  }

}
