<?php

namespace Drupal\Core\Entity;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages entity display component handlers.
 *
 * The handlers are typically shared for the whole request. getInstance() holds
 * the instantiated plugins and only instantiates one of each type.
 *
 * @see hook_display_component_handler_info_alter()
 */
class DisplayComponentHandlerPluginManager extends DefaultPluginManager {

  /**
   * The handlers that have already been instantiated by getInstance().
   *
   * @var array
   */
  protected $plugins = array();

  /**
   * Constructs a DisplayComponentHandlerPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/DisplayComponent', $namespaces, $module_handler, 'Drupal\Core\Entity\DisplayComponentHandlerInterface' , 'Drupal\Core\Entity\Annotation\DisplayComponent');
    $this->alterInfo('display_component_handler_info');
    $this->setCacheBackend($cache_backend, 'display_component_handlers');
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    $plugin_id = $options['type'];

    if (!isset($this->plugins[$plugin_id]) && !array_key_exists($plugin_id, $this->plugins)) {
      $this->plugins[$plugin_id] = $this->getDiscovery()->getDefinition($plugin_id) ? $this->createInstance($plugin_id) : NULL;
    }

    return $this->plugins[$plugin_id];
  }

}
