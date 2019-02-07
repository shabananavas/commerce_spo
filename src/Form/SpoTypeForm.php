<?php

namespace Drupal\commerce_spo\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SpoTypeForm.
 *
 * Form controller for the single page order type form.
 *
 * @package Drupal\commerce_spo\Form
 */
class SpoTypeForm extends EntityForm {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The router builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * Constructs an SpoTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entityTypeManager.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RouteBuilderInterface $router_builder
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\commerce_spo\Entity\SpoType $entity */
    $entity = $this->entity;

    // Label.
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#required' => TRUE,
    ];

    // The machine name.
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_spo\Entity\SpoType::load',
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
    ];

    // Description.
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $entity->getDescription(),
    ];

    $product = $this->entityTypeManager->getStorage('commerce_product')->load($entity->getProductId());
    $form['productId'] = [
      '#type' => 'entity_autocomplete',
      '#title' => t('Product'),
      '#attributes' => [
        'class' => ['container-inline'],
      ],
      '#placeholder' => t('Search by a product name'),
      '#target_type' => 'commerce_product',
      '#required' => TRUE,
      '#selection_settings' => [
        'match_operator' => 'CONTAINS',
      ],
      '#default_value' => $product,
    ];

    $form['enableIndividualPage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable an individual order page for purchasing this product'),
      '#default_value' => $entity->getEnableIndividualPage(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getValue('id');

    // No spaces allowed.
    if (strpos($id, ' ') !== FALSE) {
      $form_state->setErrorByName(
        'id',
        $this->t('The single page order type name should not contain any spaces.')
      );
    }

    // If we have a new entity.
    if ($this->entity->isNew()) {
      // Ensure we don't have the same name.
      if ($this->exists('id', $id)) {
        $form_state->setErrorByName(
          'id',
          $this->t('A single page order type with the same name already exists.')
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();

    // Create a custom field on the commerce_order entity to denote which single
    // page order type each order associated with this product, references.
    $this->createSpoTypeField();

    // Rebuild routes.
    $this->routerBuilder->rebuild();

    $this->messenger()->addMessage($this->t('Saved the %label single page order type.', [
      '%label' => $this->entity->label(),
    ]));

    $form_state->setRedirect('entity.spo_type.collection');
  }

  /**
   * Function to check whether a SpoType config entity exists.
   *
   * @param string $condition
   *   The query condition. Eg. 'id'.
   * @param string $value
   *   The value for the query condition. Eg. 1 if the condition is 'id'.
   *
   * @return bool
   *   Whether the spo type exists or not.
   */
  protected function exists($condition, $value) {
    $ids = $this->entityTypeManager
      ->getStorage('spo_type')
      ->getQuery()
      ->condition($condition, $value)
      ->execute();

    return (bool) $ids;
  }

  /**
   * Create a single page order type field on the commerce_order entity.
   */
  protected function createSpoTypeField() {
    $entity_type = 'commerce_order';
    $bundle = $this->entity->getSelectedOrderType();
    $field_name = 'spo_type';
    if (!$field_storage = FieldStorageConfig::loadByName($entity_type, $field_name)) {
      FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'type' => 'entity_reference',
        'cardinality' => 1,
        'settings' => [
          'target_type' => 'spo_type',
        ],
      ])->save();
    }

    if (!$field = FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
      FieldConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'bundle' => $bundle,
        'label' => $this->t('Single page order type'),
        'cardinality' => 1,
        'required' => TRUE,
        'settings' => [
          'handler' => 'default',
        ],
      ])->save();
    }
  }

}
