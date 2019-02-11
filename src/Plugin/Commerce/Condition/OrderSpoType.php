<?php

namespace Drupal\commerce_spo\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the spo_type condition for orders.
 *
 * @CommerceCondition(
 *   id = "order_spo_type",
 *   label = @Translation("Single page order type"),
 *   display_label = @Translation("Single page order type"),
 *   category = @Translation("Order"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderSpoType extends ConditionBase implements ContainerFactoryPluginInterface {
  /**
   * The spo_type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $spoTypeStorage;

  /**
   * Constructs a new OrderSpoType object.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->spoTypeStorage = $entity_type_manager->getStorage('spo_type');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // The spo_type ids.
      'spo_types' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $default_value = [];
    foreach ($this->configuration['spo_types'] as $spo_type_id) {
      $spo_types = $this->spoTypeStorage->loadByProperties(['id' => $spo_type_id]);
      if ($spo_types) {
        $spo_type = reset($spo_types);
        $default_value[] = $spo_type->id();
      }
    }

    $form['spo_types'] = [
      '#type' => 'commerce_entity_select',
      '#title' => $this->t('Single page order types'),
      '#default_value' => $default_value,
      '#target_type' => 'spo_type',
      '#hide_single_entity' => FALSE,
      '#multiple' => TRUE,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $spo_types = $this->spoTypeStorage->loadMultiple($values['spo_types']);
    $this->configuration['spo_types'] = [];
    foreach ($spo_types as $spo_type) {
      $this->configuration['spo_types'][] = $spo_type->id();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;

    /** @var \Drupal\commerce_spo\Entity\SpoType $spo_type */
    $spo_type = $order->get('spo_type');
    if (empty($spo_type)) {
      return TRUE;
    }

    return in_array($spo_type->id(), $this->configuration['spo_types']);
  }

}
