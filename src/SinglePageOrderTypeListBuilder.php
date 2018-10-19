<?php

namespace Drupal\commerce_spo;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SinglePageOrderTypeListBuilder.
 *
 * Defines the list builder for single page order type.
 *
 * @package Drupal\commerce_spo
 */
class SinglePageOrderTypeListBuilder extends ConfigEntityListBuilder {

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
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer
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
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Single page order type');
    $header['type'] = $this->t('Machine name');
    $header['product'] = $this->t('Product');
    $header['individual_page_url'] = $this->t('Individual Page URL');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_spo\Entity\SinglePageOrderTypeInterface $entity */

    $row['name'] = $entity->label();
    $row['type'] = $entity->id();

    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $product = $this->entityTypeManager->getStorage('commerce_product')
      ->load($entity->getProductId());
    $row['product'] = $product->getTitle();

    $url = Url::fromRoute('commerce_spo.single_page_order.' . $entity->id(), ['single_page_order_type' => $entity->id()]);
    $row['individual_page_url'] = Link::fromTextAndUrl($entity->getIndividualPageUrl(), $url)->toString();

    return $row + parent::buildRow($entity);
  }

}
