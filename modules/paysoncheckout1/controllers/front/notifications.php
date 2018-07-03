<?php
/**
 * 2018 Payson AB
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 *  @author    Payson AB <integration@payson.se>
 *  @copyright 2018 Payson AB
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class PaysonCheckout1NotificationsModuleFrontController extends ModuleFrontController
{

    public function init()
    {

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
            if ($cart->OrderExists() == false) {
                require_once(_PS_MODULE_DIR_ . 'paysoncheckout1/paysoncheckout1.php');
                $payson = new PaysonCheckout1();
                $paysonApi = $payson->getPaysonApiInstance();

                // Validate the request
                $response = $paysonApi->validate($postData);

                if ($response->isVerified()) {
                    $paymentDetails = $response->getPaymentDetails();
                    $newOrderId = $payson->createOrderPS($cartId, $paymentDetails);
                } else {
                    PaysonCheckout1::paysonAddLog('IPN notification unable to verify response. Return 500.', 2);
                    var_dump(http_response_code(500));
                    exit();
                }
            }
            PaysonCheckout1::paysonAddLog('IPN notification order already created.');
        }
        PaysonCheckout1::paysonAddLog('IPN notification will return 200.');
        var_dump(http_response_code(200));
        exit();
    }
}
