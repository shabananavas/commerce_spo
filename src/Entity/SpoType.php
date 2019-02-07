<?php

namespace Drupal\commerce_spo\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines the single page order type configuration entity.
 *
 * @ConfigEntityType(
 *   id = spo_type,
 *   label = @Translation("Single page order type"),
 *   label_collection = @Translation("Single page order type"),
 *   label_singular = @Translation("single page order type"),
 *   label_plural = @Translation("single page order types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count single page order type",
 *     plural = "@count single page order types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_spo\SpoTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_spo\Form\SpoTypeForm",
 *       "edit" = "Drupal\commerce_spo\Form\SpoTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer spo_type",
 *   config_prefix = "spo_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "productId",
 *     "enableIndividualPage"
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/orders/commerce_spo/order-types/add",
 *     "edit-form" = "/admin/commerce/config/orders/commerce_spo/order-types/{spo_type}/edit",
 *     "delete-form" = "/admin/commerce/config/orders/commerce_spo/order-types/{spo_type}/delete",
 *     "collection" = "/admin/commerce/config/orders/commerce_spo/order-types",
 *   }
 * )
 */
class SpoType extends ConfigEntityBase implements SpoTypeInterface {

  /**
   * The configuration entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * A description for the order type.
   *
   * @var string
   */
  protected $description;

  /**
   * The ID of the product that the single page order is for.
   *
   * @var int
   */
  protected $productId;

  /**
   * If an individual order page should be enabled.
   *
   * @var bool
   */
  protected $enableIndividualPage;

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected function entityTypeManager() {
    return \Drupal::entityTypeManager();
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProductId() {
    return $this->productId;
  }

  /**
   * {@inheritdoc}
   */
  public function setProductId($product_id) {
    $this->productId = $product_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnableIndividualPage() {
    return $this->enableIndividualPage;
  }

  /**
   * {@inheritdoc}
   */
  public function setEnableIndividualPage($enable_individual_page) {
    $this->enableIndividualPage = $enable_individual_page;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectedProduct() {
    return $this->entityTypeManager()->getStorage('commerce_product')
      ->load($this->getProductId());
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectedProductType() {
    /** @var \Drupal\commerce_product\Entity\ProductTypeInterface $product_type */
    return $this->entityTypeManager()->getStorage('commerce_product_type')
      ->load($this->getSelectedProduct()->bundle());
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectedProductVariationType() {
    /** @var \Drupal\commerce_product\Entity\ProductVariationTypeInterface $product_variation_type */
    return $this->entityTypeManager()->getStorage('commerce_product_variation_type')
      ->load($this->getSelectedProductType()->getVariationTypeId());
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectedOrderItemType() {
    /** @var \Drupal\commerce_order\Entity\OrderItemTypeInterface $order_item_type */
    return $this->entityTypeManager()->getStorage('commerce_order_item_type')
      ->load($this->getSelectedProductVariationType()->getOrderItemTypeId());
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectedOrderType() {
    // We finally have the order type.
    return $this->getSelectedOrderItemType()->getOrderTypeId();
  }

}
