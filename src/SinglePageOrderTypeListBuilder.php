<?php

namespace Drupal\commerce_spo;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\Core\Link;
use Drupal\Core\Url;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SpoTypeListBuilder.
 *
 * Defines the list builder for single page order type.
 *
 * @package Drupal\commerce_spo
 */
class SpoTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * The entity storage class.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Information about the entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($entity_type, $storage);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(
    ContainerInterface $container,
    EntityTypeInterface $entity_type
  ) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Single page order type');
    $header['type'] = $this->t('Machine name');
    $header['product'] = $this->t('Product');
    $header['enable_individual_page'] = $this->t('Enable Individual Page');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_spo\Entity\SpoTypeInterface $entity */

    $row['name'] = $entity->label();
    $row['type'] = $entity->id();

    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $product = $this->entityTypeManager->getStorage('commerce_product')
      ->load($entity->getProductId());
    $product_title = $product->getTitle();

    $enable_individual_page = $this->t('No');
    if ($entity->getEnableIndividualPage()) {
      // Link the title to the product page.
      $product_title = Link::fromTextAndUrl(
        $this->t($product_title),
        Url::fromRoute('entity.commerce_product.canonical', [
          'commerce_product' => $product->id(),
        ])
      );
      $enable_individual_page = $this->t('Yes');
    }

    $row['product'] = $product_title;
    $row['enable_individual_page'] = $enable_individual_page;

    return $row + parent::buildRow($entity);
  }

}
