<?php

namespace Drupal\commerce_spo\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
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
   * The router builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * Constructs an SinglePageOrderTypeForm object.
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

    $form['enableIndividualPage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable an individual order page for purchasing this product'),
      '#default_value' => $entity->getEnableIndividualPage(),
    ];

    // Label.
    $form['individualPageUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The URL of the individual page'),
      '#description' => $this->t(
        'This is where you denote what the link of this order page should be.
        A relative URL like "/donate-page" is expected.
        <br><strong>Note: The URL should start with a slash. Routes will be rebuilt on submit.</strong></br>'
      ),
      '#maxlength' => 255,
      '#default_value' => $entity->getIndividualPageUrl(),
      '#states' => [
        'visible' => [
          ':input[name="enableIndividualPage"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="enableIndividualPage"]' => ['checked' => TRUE],
        ],
      ],
      '#element_validate' => [
        [$this, 'validateUrl'],
      ],
    ];

    return $form;
  }

  /**
   * Validate handler for the individualPageUrl element.
   */
  public function validateUrl($element, FormStateInterface $form_state, $form) {
    // Return if individual page has been disabled.
    if (!$form_state->getValue('enableIndividualPage')) {
      // Unset the value as well.
      $form_state->setValueForElement($element, '');
      return;
    }

    // Strip trailing slashes for the url and set it as the form_state value.
    $individual_page_url = rtrim($form_state->getValue('individualPageUrl'), '/');
    $form_state->setValueForElement($element, $individual_page_url);

    // Ensure we have a slash at the beginning for the individual page URL.
    if ($individual_page_url && substr($individual_page_url, 0, 1) !== '/') {
      $form_state->setErrorByName(
        'individualPageUrl',
        $this->t('The URL must start with a slash.')
      );
    }

    // Ensure the individualPageUrl doesn't already exist.
    if ($form['individualPageUrl']['#default_value'] == $individual_page_url) {
      return;
    }
    if ($individual_page_url && $this->exists('individualPageUrl', $individual_page_url)) {
      $form_state->setErrorByName(
        'individualPageUrl',
        $this->t('A single page order type with the same url already exists.')
      );
    }
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
    $this->entity;

    $this->entity->save();

    // Rebuild routes.
    $this->routerBuilder->rebuild();

    drupal_set_message($this->t('Saved the %label single page order type.', [
      '%label' => $this->entity->label(),
    ]));

    $form_state->setRedirect('entity.single_page_order_type.collection');
  }

  /**
   * Function to check whether a SinglePageOrderType config entity exists.
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
      ->getStorage('single_page_order_type')
      ->getQuery()
      ->condition($condition, $value)
      ->execute();

    return (bool) $ids;
  }

}
