<?php
/**
 * 2019 Payson AB
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 *  @author    Payson AB <integration@payson.se>
 *  @copyright 2019 Payson AB
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class PaysonCheckout1ConfirmationModuleFrontController extends ModuleFrontController
{

    public $ssl = false;
    
    public function __construct()
    {
        parent::__construct();

        if (Configuration::get('PS_SSL_ENABLED')) {
            $this->ssl = true;
        }
    }

    public function init()
    {
        parent::init();

        PaysonCheckout1::paysonAddLog('* ' . __FILE__ . ' -> ' . __METHOD__ . ' *');
        PaysonCheckout1::paysonAddLog('Query: ' . print_r($_REQUEST, true));
        
        try {
            require_once(_PS_MODULE_DIR_ . 'paysoncheckout1/paysoncheckout1.php');
            $payson = new PaysonCheckout1();
            
            $cartId = (int) Tools::getValue('id_cart');
            if (!isset($cartId)) {
                throw new Exception($this->module->l('Unable to show confirmation.', 'confirmation') . ' ' . $this->module->l('Missing cart ID.', 'confirmation'));
            }

            // Get checkout ID from query
            if (Tools::getIsset('TOKEN') && Tools::getValue('TOKEN') != null) {
                $checkoutId = Tools::getValue('TOKEN');
                PaysonCheckout1::paysonAddLog('Got checkout ID: ' . $checkoutId . ' from query.');
            } else {
                // Unable to get checkout ID
                throw new Exception($this->module->l('Unable to show confirmation.', 'confirmation') . ' ' . $this->module->l('Missing checkout ID.', 'confirmation'));
            }
            
            $paysonApi = $payson->getPaysonApiInstance();

            $paymentDetails = $paysonApi->paymentDetails(new PaymentDetailsData($checkoutId))->getPaymentDetails();

            $cart = new Cart($cartId);

            if (!$cart->checkQuantities()) {
                Tools::redirect($this->context->link->getPageLink('cart', null, null, array('action' => 'show')));
            }

            $customer = new Customer($cart->id_customer);
            
            $checkoutStatus = Tools::strtolower($paymentDetails->getStatus());
            $checkoutType = Tools::strtolower($paymentDetails->getType());
            $invoiceStatus = Tools::strtolower($paymentDetails->getInvoiceStatus());
            $guaranteeStatus = Tools::strtolower($paymentDetails->getGuaranteeStatus());
            PaysonCheckout1::paysonAddLog('Cart ID: ' . $cart->id);
            PaysonCheckout1::paysonAddLog('Checkout Type: ' . $checkoutType);
            PaysonCheckout1::paysonAddLog('Checkout Status: ' . $checkoutStatus);
            PaysonCheckout1::paysonAddLog('Invoice Status: ' . $invoiceStatus);
            PaysonCheckout1::paysonAddLog('Guarantee Status: ' . $guaranteeStatus);
            PaysonCheckout1::paysonAddLog('Purchase ID: ' . $paymentDetails->getPurchaseId());
            
            $newOrderId = false;
                
            // For testing
            //$checkout->status = 'aborted';

            switch ($checkoutType) {
                case 'transfer':
                    switch ($checkoutStatus) {
                        case 'completed':
                            if ($cart->OrderExists() == false) {
                                // Create PS order
                                $newOrderId = $payson->createOrderPS($cart->id, $paymentDetails);
                            }
                            break;
                        case 'error':
                            throw new Exception($this->module->l('Your payment was declined. Please try again or select a different payment method.', 'confirmation') . ' 1001');
                        default:
                            throw new Exception($this->module->l('Unable to process payment. Please try again or select a different payment method.', 'confirmation') . ' 1002');
                    }
                    break;
                case 'invoice':
                    switch ($invoiceStatus) {
                        case 'ordercreated':
                            if ($cart->OrderExists() == false) {
                                try {
                                    // Add invoice product to cart
                                    $invoiceProduct = $payson->getProductFromRef('PS_FA');
                                    PaysonCheckout1::paysonAddLog('Invoice fee product ID ' . $invoiceProduct->id);
                                    if ($invoiceProduct != false && $invoiceProduct->price > 0) {
                                        $cart->updateQty(1, (int) $invoiceProduct->id, null, false, 'up', 0, new Shop((int) $cart->id_shop), false);
                                    }
                                } catch (Exception $xx) {
                                    PaysonCheckout1::paysonAddLog('Unable to add invoice fee product to cart. ' . $xx->getMessage());
                                }
                                
                                // Create PS order
                                $newOrderId = $payson->createOrderPS($cart->id, $paymentDetails);
                            }
                            break;
                        case 'denied':
                            throw new Exception($this->module->l('Your payment was declined. Please try again or select a different payment method.', 'confirmation') . ' 1003');
                        default:
                            throw new Exception($this->module->l('Unable to process payment. Please try again or select a different payment method.', 'confirmation') . ' 1004');
                    }
                    break;
                case 'guarantee':
                    break;
                default:
                    break;
            }
            
            if ($newOrderId != false) {
                $order = new Order((int) $newOrderId);
            } elseif ($cart->OrderExists()) {
                $order = Order::getOrderByCartId($cart->id);
            } else {
                throw new Exception($this->module->l('Unable to process payment. Please try again or select a different payment method.', 'confirmation') . ' 1005');
            }
            
            $this->context->cookie->__set('id_customer', $order->id_customer);
            
            $this->displayConfirmation($cart, $order, $customer);
        } catch (Exception $ex) {
            // Log error message
            PaysonCheckout1::paysonAddLog('Confirmation error: ' . $ex->getMessage(), 2);
            
            // Set message
            $this->context->cookie->__set('payson_order_error', $ex->getMessage());
            
            // Redirect to cart
            Tools::redirect('order.php?step=3');
        }
    }
    
    // Display order confirmation page
    protected function displayConfirmation($cart, $order, $customer)
    {
        PaysonCheckout1::paysonAddLog('Confirmation link is: ' . 'order-confirmation.php?key=' . $customer->secure_key . '&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $order->id);
        Tools::redirect('order-confirmation.php?key=' . $customer->secure_key . '&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $order->id);
    }
}
