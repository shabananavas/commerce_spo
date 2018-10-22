<?php

namespace Drupal\commerce_spo\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Interface SinglePageOrderTypeInterface.
 *
 * Defines the interface for the SinglePageOrderType entity.
 *
 * @package Drupal\commerce_spo\Entity
 */
interface SinglePageOrderTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

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
   * Set the individual order URL for this entity.
   *
   * @param bool $individual_page_url
   *   The relative URL of the individual page.
   *
   * @return $this
   */
  public function setIndividualPageUrl($individual_page_url);

  /**
   * Gets the individual page URL.
   *
   * @return string
   *   The relative URL of the individual page.
   */
  public function getIndividualPageUrl();

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
