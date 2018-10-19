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

}
