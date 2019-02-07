<?php

namespace Drupal\commerce_spo\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Interface SpoTypeInterface.
 *
 * Defines the interface for the SpoType entity.
 *
 * @package Drupal\commerce_spo\Entity
 */
interface SpoTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Sets the product that the single page order is for.
   *
   * @param int $product_id
   *   The ID of the product.
   *
   * @return $this
   */
  public function setProductId($product_id);

  /**
   * Gets the product ID.
   *
   * @return int|null
   *   The product ID, or null.
   */
  public function getProductId();

  /**
   * Set whether this entity should have an individual order page.
   *
   * @param bool $enable_individual_page
   *   TRUE if this entity should have an individual order page.
   *
   * @return $this
   */
  public function setEnableIndividualPage($enable_individual_page);

  /**
   * Return TRUE if this entity should have an individual order page.
   *
   * @return bool
   *   TRUE if this entity should have an individual order page.
   */
  public function getEnableIndividualPage();

  /**
   * Returns the selected product entity.
   *
   * @return \Drupal\commerce_product\Entity\ProductInterface
   *   The loaded product entity.
   */
  public function getSelectedProduct();

  /**
   * Returns the product type of the selected product.
   *
   * @return \Drupal\commerce_product\Entity\ProductTypeInterface
   *   The loaded product type entity.
   */
  public function getSelectedProductType();

  /**
   * Returns the product variation type of the selected product.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationTypeInterface
   *   The loaded product variation type entity.
   */
  public function getSelectedProductVariationType();

  /**
   * Returns the order item type of the selected product.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemTypeInterface
   *   The loaded order item type entity.
   */
  public function getSelectedOrderItemType();

  /**
   * Returns the order type of the selected product.
   *
   * @return \Drupal\commerce_order\Entity\OrderTypeInterface
   *   The loaded order type entity.
   */
  public function getSelectedOrderType();

}
