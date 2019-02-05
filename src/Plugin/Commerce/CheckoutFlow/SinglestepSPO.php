<?php

namespace Drupal\commerce_spo\Plugin\Commerce\CheckoutFlow;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowWithPanesBase;

/**
 * Provides a single step checkout flow.
 *
 * @CommerceCheckoutFlow(
 *   id = "single_step_spo",
 *   label = "Single Step - Single Page Order",
 * )
 */
class SinglestepSPO extends CheckoutFlowWithPanesBase {

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    return [
      'order_information' => [
        'label' => $this->t('Order information'),
        'has_sidebar' => TRUE,
        'previous_label' => $this->t('Go back'),
      ],
    ];
  }

}
