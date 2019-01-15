<?php

namespace Drupal\commerce_spo\Form;

use Drupal\commerce_payment\PaymentOptionsBuilderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsStoredPaymentMethodsInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\ProductLazyBuilders;

use Drupal\commerce_spo\Entity\SinglePageOrderTypeInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SinglePageOrderForm.
 *
 * Form controller for the Single Page Order form.
 *
 * @package Drupal\commerce_spo\Form
 */
class SinglePageOrderForm extends ContentEntityForm {

  /**
   * The single_page_order_type entity.
   *
   * @var \Drupal\commerce_spo\Entity\SinglePageOrderTypeInterface
   */
  protected $spoType;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The payment options builder.
   *
   * @var \Drupal\commerce_payment\PaymentOptionsBuilderInterface
   */
  protected $paymentOptionsBuilder;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The commerce product lazy builder.
   *
   * @var \Drupal\commerce_product\ProductLazyBuilders
   */
  protected $lazyBuilders;

  /**
   * Constructs a new IndividualOrderPageForm object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\commerce_payment\PaymentOptionsBuilderInterface $payment_options_builder
   *   The payment options builder.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\commerce_product\ProductLazyBuilders $lazy_builders
   *   The commerce product lazy builder.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    EntityManagerInterface $entity_manager,
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    TimeInterface $time,
    DateFormatterInterface $date_formatter,
    AccountProxyInterface $current_user,
    PaymentOptionsBuilderInterface $payment_options_builder,
    MessengerInterface $messenger,
    LoggerInterface $logger,
    ProductLazyBuilders $lazy_builders
  ) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);

    $this->routeMatch = $route_match;
    $this->entityManager = $entity_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->currentUser = $current_user->getAccount();
    $this->paymentOptionsBuilder = $payment_options_builder;
    $this->messenger = $messenger;
    $this->logger = $logger;
    $this->lazyBuilders = $lazy_builders;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('date.formatter'),
      $container->get('current_user'),
      $container->get('commerce_payment.options_builder'),
      $container->get('messenger'),
      $container->get('logger.factory')->get(COMMERCE_SPO_LOGGER_CHANNEL),
      $container->get('commerce_product.lazy_builders')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->spoType->id() . '_form';
  }

  /**
   * Sets the single page order type entity.
   *
   * @param \Drupal\commerce_spo\Entity\SinglePageOrderTypeInterface $spo_type
   *   The single page order type.
   */
  public function setSinglePageOrderTypeEntity(SinglePageOrderTypeInterface $spo_type) {
    $this->spoType = $spo_type;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Build the parent form.
    //$form = parent::buildForm($form, $form_state)

    // Add the add to cart form.
    $form += $this->lazyBuilders->addToCartForm(
      $this->spoType->getProductId(),
      'cart',
      TRUE,
      Language::LANGCODE_NOT_SPECIFIED
    );

    // Add our payment method form.
    /*$form += $this->buildPaymentMethodForm($form, $form_state);

    // Alter form fields.
    $this->alterFormFields($form, $form_state);*/

    return $form;
  }

  /**
   * Builds the payment method form for the selected payment option.
   *
   * @param array $form
   *   The individual order page form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the parent form.
   *
   * @return array
   *   The form array.
   */
  public function buildPaymentMethodForm(
    array $form,
    FormStateInterface $form_state
  ) {
    $order = $this->entity;

    /** @var \Drupal\commerce_payment\PaymentGatewayStorageInterface $payment_gateway_storage */
    $payment_gateway_storage = $this
      ->entityTypeManager
      ->getStorage('commerce_payment_gateway');
    // Load the payment gateways. This fires an event for filtering the
    // available gateways, and then evaluates conditions on all remaining ones.
    $payment_gateways = $payment_gateway_storage->loadMultipleForOrder($order);
    // Can't proceed without any payment gateways.
    if (empty($payment_gateways)) {
      $this->messenger->addError($this->noPaymentGatewayErrorMessage());
      return $form;
    }

    $options = $this
      ->paymentOptionsBuilder
      ->buildOptions($order, $payment_gateways);
    $default_option = $this
      ->paymentOptionsBuilder
      ->selectDefaultOption($order, $options);

    $default_payment_gateway_id = $default_option->getPaymentGatewayId();
    $payment_gateway = $payment_gateways[$default_payment_gateway_id];
    if ($payment_gateway->getPlugin() instanceof SupportsStoredPaymentMethodsInterface) {
      /** @var \Drupal\commerce_payment\PaymentMethodStorageInterface $payment_method_storage */
      $payment_method_storage = $this->entityTypeManager->getStorage('commerce_payment_method');
      $billing_profile = $order->getBillingProfile();
      if (!$billing_profile) {
        $profile_storage = $this->entityTypeManager->getStorage('profile');
        $billing_profile = $profile_storage->create([
          'type' => 'customer',
          'uid' => $order->getCustomerId(),
        ]);
      }

      // Get the payment method types available for this gateway and select the
      // first one as the default.
      $payment_method_types = $payment_gateway->getPlugin()
        ->getPaymentMethodTypes();
      /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeInterface $payment_method_type */
      $payment_method_type = reset($payment_method_types);

      // Create the payment method.
      $payment_method = $payment_method_storage->create([
        'type' => $payment_method_type->getPluginId(),
        'payment_gateway' => $default_option->getPaymentGatewayId(),
        'uid' => $order->getCustomerId(),
        'billing_profile' => $billing_profile,
      ]);

      $form['payment'] = [
        '#type' => 'fieldgroup',
        '#title' => $this->t('<h3 class="form-required">Payment Details</h3>'),
        '#weight' => 10,
        '#suffix' => $this->t('<h4 class="form-required">Credit Card Information</h4>'),
      ];
      $form['payment_method'] = [
        '#type' => 'hidden',
        '#value' => $default_option->getId(),
      ];
      $form['#payment_options'] = $options;
      $form['add_payment_method'] = [
        '#type' => 'commerce_payment_gateway_form',
        '#operation' => 'add-payment-method',
        '#default_value' => $payment_method,
        '#weight' => 11,
      ];
    }

    return $form;
  }

  /**
   * Alters the fields in the individual order page form.
   *
   * @param array $form
   *   The individual order page form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the parent form.
   */
  public function alterFormFields(array &$form, FormStateInterface $form_state) {
    $form['product_select']['#weight'] = -100;
    $form['adjustments']['#weight'] = 100;
    $form['adjustments']['widget']['#weight'] = 100;
    $form['coupons']['#weight'] = 101;
    $form['order_items']['#access'] = FALSE;
    $form['cart']['#access'] = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm(
      $form,
      $form_state
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm(
      $form,
      $form_state
    );

    // Add the order item.
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->entity;

    $product_variations = $form_state->getValue('product_variations');
    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $purchased_product_variation */
    $purchased_product_variation = $product_variations[$form_state->getValue('product_select')];
    $order_item = $this->entityTypeManager->getStorage('commerce_order_item')->create([
      'type' => $this->spoType->getSelectedOrderItemType(),
      'title' => $purchased_product_variation->getTitle(),
      'unit_price' => $purchased_product_variation->getPrice(),
    ]);
    $order->addItem($order_item);
    $order->setEmail($this->currentUser->getEmail());

    // Complete the payment.
    $this->completePayment($form, $form_state);

    // Place the order.
    $transition = $order->getState()->getWorkflow()->getTransition('place');
    $order->getState()->applyTransition($transition);
    $order->save();
  }

  /**
   * Set the order's payments to completed.
   *
   * @param array $form
   *   The individual order page form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the parent form.
   */
  public function completePayment(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->entity;
    $values = $form_state->getValue($form['#parents']);

    /** @var \Drupal\commerce_price\Entity\CurrencyInterface $amount */
    $amount = $form_state->getValue('amount');
    $amount_value = $amount === 'other'
      ? $form_state->getValue('amount_other')['number']
      : $amount;

    /** @var \Drupal\commerce_payment\PaymentOption $selected_option */
    $selected_option = $form['#payment_options'][$values['payment_method']];
    /** @var \Drupal\commerce_payment\PaymentGatewayStorageInterface $payment_gateway_storage */
    $payment_gateway_storage = $this->entityTypeManager->getStorage('commerce_payment_gateway');
    /** @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface $payment_gateway */
    $payment_gateway = $payment_gateway_storage->load($selected_option->getPaymentGatewayId());
    if (!$payment_gateway) {
      return;
    }

    if ($payment_gateway->getPlugin() instanceof SupportsStoredPaymentMethodsInterface) {
      if (!empty($selected_option->getPaymentMethodTypeId())) {
        // The payment method was just created.
        $payment_method = $values['add_payment_method'];
      }
      else {
        /** @var \Drupal\commerce_payment\PaymentMethodStorageInterface $payment_method_storage */
        $payment_method_storage = $this->entityTypeManager->getStorage('commerce_payment_method');
        $payment_method = $payment_method_storage->load($selected_option->getPaymentMethodId());
      }

      /** @var \Drupal\commerce_payment\Entity\PaymentMethodInterface $payment_method */
      $order->set('payment_gateway', $payment_gateway);
      $order->set('payment_method', $payment_method);
      // Set the billing profile the same as the payment method billing as we're
      // not displaying the order billing_profile field in this form.
      $order->setBillingProfile($payment_method->getBillingProfile());
    }

    // Create the actual payment on the order.
    $payment = $this->entityTypeManager->getStorage('commerce_payment')
      ->create([
        'payment_gateway' => $payment_gateway->id(),
        'payment_method' => $payment_method->id(),
        'order_id' => $this->entity->id(),
        'amount' => new Price($amount_value, $order->getStore()
          ->getDefaultCurrency()
          ->getCurrencyCode()),
      ]);
    $payment_gateway->getPlugin()->createPayment($payment);

    // Trigger the order placed transition.
    $transition = $order->getState()->getWorkflow()->getTransition('place');
    $order->getState()->applyTransition($transition);
    $order->save();
  }

  /**
   * Returns an error message in case there are no available payment gateways.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The error message.
   */
  public function noPaymentGatewayErrorMessage() {
    // Log the specific error message and return a general message to the user.
    $message = sprintf(
      'No payment gateways were available when preparing the order 
      form for the order with ID %s.',
      $this->entity->id()
    );
    $this->logger->error($message);

    return $this->t('An unexpected error has occurred, please try again.
     If the error persists don\'t hesitate to contact us.');
  }

}
