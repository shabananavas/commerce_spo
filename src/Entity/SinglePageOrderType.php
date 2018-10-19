<?php

namespace Drupal\commerce_spo\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the single page order type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "single_page_order_type",
 *   label = @Translation("single page order type"),
 *   label_collection = @Translation("single page order type"),
 *   label_singular = @Translation("single page order type"),
 *   label_plural = @Translation("single page order types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count single page order type",
 *     plural = "@count single page order types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_spo\SinglePageOrderTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_spo\Form\SinglePageOrderTypeForm",
 *       "edit" = "Drupal\commerce_spo\Form\SinglePageOrderTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer single_page_order_type",
 *   config_prefix = "single_page_order_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "productId",
 *     "enableIndividualPage",
 *     "individualPageUrl",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/orders/commerce_spo/order-types/add",
 *     "edit-form" = "/admin/commerce/config/orders/commerce_spo/order-types/{single_page_order_type}/edit",
 *     "delete-form" = "/admin/commerce/config/orders/commerce_spo/order-types/{single_page_order_type}/delete",
 *     "collection" = "/admin/commerce/config/orders/commerce_spo/order-types",
 *   }
 * )
 */
class SinglePageOrderType extends ConfigEntityBase implements SinglePageOrderTypeInterface {

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
   * The individual page url.
   *
   * @var string
   */
  protected $individualPageUrl;

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
  public function getIndividualPageUrl() {
    return $this->individualPageUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function setIndividualPageUrl($individual_page_url) {
    $this->individualPageUrl = $individual_page_url;
    return $this;
  }

}
