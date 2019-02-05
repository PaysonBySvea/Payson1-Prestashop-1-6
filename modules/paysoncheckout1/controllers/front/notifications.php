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

class PaysonCheckout1NotificationsModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        parent::init();
        $postData = Tools::file_get_contents("php://input");
        
        // Give order confirmation a chance to finish
        sleep(2);
        
        PaysonCheckout1::paysonAddLog('* ' . __FILE__ . ' -> ' . __METHOD__ . ' *');
        PaysonCheckout1::paysonAddLog('IPN notification query: ' . print_r($_REQUEST, true));

        $call = Tools::getValue('call');

        if ($call == 'notification') {
            $newOrderId = false;
            $cartId = (int) Tools::getValue('id_cart');
            if (!isset($cartId) || $cartId == null) {
                PaysonCheckout1::paysonAddLog('IPN notification unable to get cart ID. Return 500.', 2);
                var_dump(http_response_code(500));
                exit();
            }
            
            $cart = new Cart((int) $cartId);
            
            require_once(_PS_MODULE_DIR_ . 'paysoncheckout1/paysoncheckout1.php');
            $payson = new PaysonCheckout1();
            $paysonApi = $payson->getPaysonApiInstance();

            // Validate the request
            $response = $paysonApi->validate($postData);

            if ($response->isVerified()) {
                $paymentDetails = $response->getPaymentDetails();

                $checkoutStatus = Tools::strtolower($paymentDetails->getStatus());
                $checkoutType = Tools::strtolower($paymentDetails->getType());
                $invoiceStatus = Tools::strtolower($paymentDetails->getInvoiceStatus());
                $guaranteeStatus = Tools::strtolower($paymentDetails->getGuaranteeStatus());
                PaysonCheckout1::paysonAddLog('IPN Cart ID: ' . $cart->id);
                PaysonCheckout1::paysonAddLog('IPN Checkout Type: ' . $checkoutType);
                PaysonCheckout1::paysonAddLog('IPN Checkout Status: ' . $checkoutStatus);
                PaysonCheckout1::paysonAddLog('IPN Invoice Status: ' . $invoiceStatus);
                PaysonCheckout1::paysonAddLog('IPN Guarantee Status: ' . $guaranteeStatus);
                PaysonCheckout1::paysonAddLog('IPN Purchase ID: ' . $paymentDetails->getPurchaseId());

                // For testing
                //$checkout->status = 'aborted';
                
                if ($checkoutStatus != 'aborted') {
                    switch ($checkoutType) {
                        case 'transfer':
                            switch ($checkoutStatus) {
                                case 'completed':
                                    if ($cart->OrderExists() == false) {
                                        // Create PS order
                                        $newOrderId = $payson->createOrderPS($cart->id, $paymentDetails);
                                    } else {
                                        PaysonCheckout1::paysonAddLog('IPN order already exists.');
                                    }
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 'invoice':
                            switch ($invoiceStatus) {
                                case 'ordercreated':
                                    PaysonCheckout1::paysonAddLog('IPN start invoice order');
                                    if ($cart->OrderExists() == false) {
                                        try {
                                            $invoiceProduct = $payson->getProductFromRef('PS_FA');
                                            PaysonCheckout1::paysonAddLog('IPN Invoice fee product ID ' . $invoiceProduct->id);
                                            if ($invoiceProduct != false && $invoiceProduct->price > 0) {
                                                $cart->updateQty(1, (int) $invoiceProduct->id, null, false, 'up', 0, new Shop((int) $cart->id_shop), false);
                                            }
                                        } catch (Exception $xx) {
                                            PaysonCheckout1::paysonAddLog('IPN Unable to add invoice fee product to cart. ' . $xx->getMessage(), 3);
                                        }

                                        // Create PS order
                                        $newOrderId = $payson->createOrderPS($cart->id, $paymentDetails);
                                    } else {
                                        PaysonCheckout1::paysonAddLog('IPN invoice order already exists.');
                                    }
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 'guarantee':
                            break;
                        default:
                            break;
                    }
                }

                //$newOrderId = $payson->createOrderPS($cartId, $paymentDetails);
            } else {
                PaysonCheckout1::paysonAddLog('IPN notification unable to verify response. Return 500.', 2);
                var_dump(http_response_code(500));
                exit();
            }
        }
        PaysonCheckout1::paysonAddLog('IPN notification will return 200.');
        var_dump(http_response_code(200));
        exit();
    }
}
