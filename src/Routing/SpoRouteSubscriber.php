<?php

namespace Drupal\commerce_spo\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;

use Symfony\Component\Routing\RouteCollection;

/**
 * Class SpoRouteSubscriber.
 *
 * Listens to the product page route events and redirects to our custom
 * controller.
 */
class SpoRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('entity.commerce_product.canonical');
    if (!$route) {
      return;
    }

    $route->setDefault('_controller', '\Drupal\commerce_spo\Controller\IndividualPageController::productPage');
  }

}
