<?php

namespace Drupal\commerce_spo\Controller;

use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_spo\Entity\SinglePageOrderTypeInterface;
use Drupal\commerce_store\CurrentStoreInterface;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IndividualOrderPageController.
 *
 * @package Drupal\commerce_spo\Controller
 */
class SinglePageOrderController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new CartController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    CartProviderInterface $cart_provider,
    CurrentStoreInterface $current_store,
    RouteMatchInterface $route_match
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cartProvider = $cart_provider;
    $this->currentStore = $current_store;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_store.current_store'),
      $container->get('current_route_match')
    );
  }

  /**
   * Outputs a cart form for the first non-empty cart of the current user.
   *
   * @return mixed
   *   A render array.
   */
  public function productPage() {
    // Get the single page order type from the product.
    /** @var \Drupal\commerce_product\Entity\Product $product */
    $product = $this->routeMatch->getParameter('commerce_product');
    $spo_type = $this
      ->entityTypeManager
      ->getStorage('single_page_order_type')
      ->loadByProperties([
        'productId' => $product->id()
        ]
    );

    // If this product is not associated with a Single page order, redirect to
    // the default product page.
    $spo_type = reset($spo_type);
    if (!$spo_type instanceof SinglePageOrderTypeInterface) {
      $product_view = $this
        ->entityTypeManager
        ->getViewBuilder('commerce_product')
        ->view($product);

      return $product_view;
    }

    // Else, we redirect to our single page order page.
    // Now, load an existing cart if it exists, or create a new one.
    $store = $this->currentStore->getStore();
    $order_type = $spo_type->getSelectedOrderType();
    $order = $this->cartProvider->getCart($order_type, $store);
    if (!$order) {
      $order = $this->cartProvider->createCart($order_type, $store);
    }

    // Build and render our single page order form.
    /** @var \Drupal\commerce_spo\Form\SinglePageOrderForm $form_object */
    $form_object = $this->entityTypeManager->getFormObject('commerce_order', 'commerce_spo');
    $form_object->setEntity($order);
    $form_object->setSinglePageOrderTypeEntity($spo_type);

    return $this->formBuilder()->getForm($form_object);
  }

}
