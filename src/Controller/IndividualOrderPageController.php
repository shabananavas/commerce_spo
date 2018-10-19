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
class IndividualOrderPageController extends ControllerBase {

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
  public function individualOrderPage() {
    // Get the cart as using the cart allows us to easily pick up the associated
    // order for the current user, instead of creating a new order on every page
    // load.
    $store = $this->currentStore->getStore();

    // Get the single page order type from the URL.
    $spo_type = $this->routeMatch->getParameter('single_page_order_type');

    if (!$spo_type instanceof SinglePageOrderTypeInterface) {
      return [];
    }

    // Get the order type from the product the spo_type references.
    $product = $this->entityTypeManager->getStorage('commerce_product')
      ->load($spo_type->getProductId());
    /** @var \Drupal\commerce_product\Entity\ProductTypeInterface $product_type */
    $product_type = $this->entityTypeManager->getStorage('commerce_product_type')
      ->load($product->bundle());
    /** @var \Drupal\commerce_product\Entity\ProductVariationTypeInterface $product_variation_type */
    $product_variation_type = $this->entityTypeManager->getStorage('commerce_product_variation_type')
      ->load($product_type->getVariationTypeId());
    /** @var \Drupal\commerce_order\Entity\OrderItemTypeInterface $order_item_type */
    $order_item_type = $this->entityTypeManager->getStorage('commerce_order_item_type')
      ->load($product_variation_type->getOrderItemTypeId());

    $order_type = $order_item_type->getOrderTypeId();
    $order = $this->cartProvider->getCart($order_type, $store);
    if (!$order) {
      $order = $this->cartProvider->createCart($order_type, $store);
    }

    return $this->entityFormBuilder()->getForm($order, 'commerce_spo');
  }

}
