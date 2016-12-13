<?php

namespace Drupal\Core\Entity;

/**
 * Provides a base class for DisplayComponent plugins.
 */
interface DisplayComponentHandlerInterface {

  /**
   * Checks if the component handler should process the passed component.
   *
   * @param string $name
   *   The name of a display component.
   *
   * @return bool
   *   TRUE if the display component handler provides the component.
   */
  public function hasElement($name);

  /**
   * Prepares the options before they are stored.
   *
   * @param string $name
   *   The name of a display component.
   * @param array $options
   *   The default options for this component.
   *
   * @return array
   *   Massaged component options.
   */
  public function massageIn($name, array $options);

  /**
   * Sets the context for the rendering component.
   *
   * @param array $context
   *   A keyed array containing the current entity display context. The
   *   following parameters should be set:
   *   - entity_type: The type of entity.
   *   - bundle: The entity bundle.
   *   - view_mode: The entity view mode (default, full).
   *   - display_context: The type of the display to use (view or form).
   */
  public function setContext(array $context);

  /**
   * Returns the render plugin for the display component.
   *
   * @param string $name
   *   The name of a display component.
   * @param array $options
   *   An array of configuration options to instantiate the render plugin.
   *
   * @return mixed
   *   The object to render the component or null.
   */
  public function getRenderer($name, array $options);

  /**
   * Prepares components when the display gets created.
   *
   * @param array $components
   *   The visible display components, passed by reference.
   * @param array $hidden_components
   *   The hidden display components, passed by reference.
   */
  public function prepareDisplayComponents(array &$components, array &$hidden_components);

  /**
   * Prepares the display options after they are retrieved from the storage.
   *
   * @param array $properties
   *   The entity display properties.
   *
   * return @array
   *   An associative array of the display components with following keys:
   *   - content: Configured components to render.
   *   - hidden: Configured components to hide from render.
   */
  public function massageOut($properties);

}
