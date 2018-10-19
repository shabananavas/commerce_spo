<?php

namespace Drupal\commerce_spo\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SinglePageOrderTypeForm.
 *
 * Form controller for the single page order type form.
 *
 * @package Drupal\commerce_spo\Form
 */
class SinglePageOrderTypeForm extends EntityForm {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an SinglePageOrderTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\commerce_spo\Entity\SinglePageOrderType $entity */
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
        'exists' => '\Drupal\commerce_spo\Entity\SinglePageOrderType::load',
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

    if ($this->entity->isNew() && $this->exists($id)) {
      $form_state->setErrorByName(
        'id',
        $this->t('A single page order type with the same name already exists.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity;

    $this->entity->save();

    drupal_set_message($this->t('Saved the %label single page order type.', [
      '%label' => $this->entity->label(),
    ]));

    $form_state->setRedirect('entity.single_page_order_type.collection');
  }

  /**
   * Function to check whether a SinglePageOrderType config entity exists.
   *
   * @param string $id
   *   The ID (machine name) of the spo type.
   *
   * @return bool
   *   Whether the spo type exists or not.
   */
  protected function exists($id) {
    $ids = $this->entityTypeManager
      ->getStorage('single_page_order_type')
      ->getQuery()
      ->condition('id', $id)
      ->execute();

    return (bool) $ids;
  }

}
