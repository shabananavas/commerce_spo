<?php

namespace Drupal\commerce_spo\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class SinglePageOrderTypeRouteSubscriber.
 *
 * Listens to the dynamic single page order type route events.
 */
class SinglePageOrderTypeRouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Load all single page order types and generate the route for them.
    $entity_type = 'single_page_order_type';
    $spo_types = $this->entityTypeManager->getStorage($entity_type)
      ->loadByProperties([
        'enableIndividualPage' => TRUE,
      ]
    );

    foreach ($spo_types as $spo_type) {
      // Create a custom route for this individual order page.
      $route = new Route(
        $spo_type->getIndividualPageUrl(),
        [
          '_title' => $spo_type->label(),
          '_controller' => '\Drupal\commerce_spo\Controller\IndividualOrderPageController::individualOrderPage',
        ],
        [
          '_permission' => 'view ' . $spo_type->id(),
        ]
      );

      // Add our route to the collection with a unique key.
      $collection->add('commerce_spo.single_page_order.' . $spo_type->id(), $route);
    }
  }

}
