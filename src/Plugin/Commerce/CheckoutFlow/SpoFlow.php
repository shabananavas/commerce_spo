<?php

namespace Drupal\commerce_spo\Plugin\Commerce\CheckoutFlow;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowWithPanesBase;

/**
 * Provides a default checkout flow for single page orders.
 *
 * @CommerceCheckoutFlow(
 *   id = "spo_flow",
 *   label = "Single Page Order Checkout Flow",
 * )
 */
class SpoFlow extends CheckoutFlowWithPanesBase {

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    return [
      'spo' => [
        'label' => $this->t('Single Page Order'),
        'has_sidebar' => TRUE,
        'previous_label' => $this->t('Go back'),
      ],
    ];
  }

}
