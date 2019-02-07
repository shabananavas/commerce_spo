<?php

namespace Drupal\commerce_spo\Controller;

use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_spo\Entity\SpoTypeInterface;
use Drupal\commerce_store\CurrentStoreInterface;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IndividualPageController.
 *
 * @package Drupal\commerce_spo\Controller
 */
class IndividualPageController extends ControllerBase {

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
   * Render the individual page order form if this product belongs to an spo.
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
      ->getStorage('spo_type')
      ->loadByProperties([
          'productId' => $product->id()
        ]
      );

    // If this product is not associated with an spo or the spo_type doesn't
    // have the enable_individual_page field checked, display the default
    // product page.
    $spo_type = reset($spo_type);
    if (!$spo_type instanceof SpoTypeInterface || !$spo_type->getEnableIndividualPage()) {
      $product_view = $this
        ->entityTypeManager
        ->getViewBuilder('commerce_product')
        ->view($product);

      return $product_view;
    }

    // Else, we redirect to our individual page order form.
    // First, load an existing cart if it exists, or create a new order.
    $store = $this->currentStore->getStore();
    $order_type = $spo_type->getSelectedOrderType();
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->cartProvider->getCart($order_type, $store);
    if (!$order) {
      $order = $this->cartProvider->createCart($order_type, $store);
    }

    // TODO: Build and render our individual page order form.
  }

}
