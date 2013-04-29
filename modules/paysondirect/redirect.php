<?php

include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/paysondirect.php');


include_once(_PS_MODULE_DIR_ . 'paysondirect/payson_api/def.payson.php');


$payson = new Paysondirect();



$cart = new Cart(intval($cookie->id_cart));

$address = new Address(intval($cart->id_address_invoice));
$country = new Country(intval($address->id_country));
$state = NULL;
if ($address->id_state)
    $state = new State(intval($address->id_state));
$customer = new Customer(intval($cart->id_customer));

$email = Configuration::get('PAYSON_EMAIL');

$invoiceEnabled = Configuration::get('PAYSON_INVOICE_ENABLED') == 1;
$isInvoicePurchase = isset($_GET["method"]) && ($_GET["method"] == "invoice");

if ($isInvoicePurchase && !$invoiceEnabled)
    die('Cant pay with invoice when invoice isnt enabled');

if (!Validate::isEmail($email))
    die($payson->getL('Payson error: (invalid or undefined business account email)'));

if (!Validate::isLoadedObject($address))
    die($payson->getL('Payson error: (invalid address)'));

if (!Validate::isLoadedObject($customer))
    die($payson->getL('Payson error: (invalid customer)'));


// check currency of payment
$currency_order = new Currency(intval($cart->id_currency));
$currencies_module = $payson->getCurrency();

if (is_array($currencies_module)) {
    foreach ($currencies_module AS $some_currency_module) {
        if ($currency_order->iso_code == $some_currency_module['iso_code']) {
            $currency_module = $some_currency_module;
        }
    }
} else {
    $currency_module = $currencies_module;
}

if ($currency_order->id != $currency_module['id_currency']) {
    $cookie->id_currency = $currency_module['id_currency'];
    $cart->id_currency = $currency_module['id_currency'];
    $cart->update();
}
$currencyCode = $currency_module['iso_code'];


$amount = floatval($cart->getOrderTotal(true, 3));

if ($isInvoicePurchase)
    $amount += $payson->paysonInvoiceFee();


$url = Tools::getHttpHost(false, true) . __PS_BASE_URI__;
$cart_id = intval($cart->id);
$payson_id = intval($payson->id);

// start------------------------------------------------------------------------------------------
$trackingId = time();

$customer = array('name' => $customer->firstname, 'lastname' => $customer->lastname, 'mail' => $customer->email);

$paysonUrl = array(
    'returnUrl' => "http://" . $url . "modules/paysondirect/validation.php?trackingId=" . $trackingId . "&id_cart=" . $cart_id,
    'ipnNotificationUrl' => "http://" . $url . 'modules/paysondirect/ipn_payson.php?id_cart=' . $cart_id,
    'cancelUrl' => "http://" . $url . "index.php?controller=order"
);

$productInfo = array('amount' => $amount, 'fee' => $payson->paysonInvoiceFee(), 'orderitemslist' => orderItemsList($cart));

$shopInfo = array(
    'shopName' => Configuration::get('PS_SHOP_NAME'),
    'localeCode' => Language::getIsoById($cookie->id_lang),
    'currencyCode' => $currency_module['iso_code']
);

$api = $payson->getAPIInstance();


if ($payson->testMode) {
    $receiver = new Receiver('testagent-1@payson.se', $productInfo['amount']);
    $sender = new Sender(Configuration::get('PAYSON_SANDBOX_CUSTOMER_EMAIL'), 'name', 'lastname');
} else {
    $receiver = new Receiver(trim(Configuration::get('PAYSON_EMAIL')), $productInfo['amount']);
    $sender = new Sender(trim($customer['mail']), trim($customer['name']), trim($customer['lastname']));
}
$receivers = array($receiver);

$payData = new PayData($paysonUrl['returnUrl'], $paysonUrl['cancelUrl'], $paysonUrl['ipnNotificationUrl'], $shopInfo['shopName'], $sender, $receivers);
$payData->setCurrencyCode($shopInfo['currencyCode']);
$payData->setLocaleCode($shopInfo['localeCode']);
$payData->setTrackingId(time());

$constraints = $isInvoicePurchase ? $constraints = array(FundingConstraint::INVOICE) : array(Configuration::get('PAYSON_PAYMENTMETHODS'));
$payData->setFundingConstraints($constraints);

$payData->setOrderItems($productInfo['orderitemslist']);
if ($isInvoicePurchase)
    $payData->setInvoiceFee($productInfo['fee']);

$payData->setGuaranteeOffered('NO');

$payResponse = $api->pay($payData);
if ($payResponse->getResponseEnvelope()->wasSuccessful()) {  //ack = SUCCESS och token  = token = N�got
    header("Location: " . $api->getForwardPayUrl($payResponse));
} else {
    $error = $payResponse->getResponseEnvelope()->getErrors();
    if (Configuration::get('PAYSON_LOGS') == 'yes') {
        $message = '<Payson Direct api>' . $error[0]->getErrorId() . '***' . $error[0]->getMessage() . '***' . $error[0]->getParameter();
        Logger::addLog($message, 1, NULL, NULL, NULL, true);
    }
    $payson->paysonApiError($error[0]->getMessage() . ' Please try using a different payment method.');
}

/*
 * @return void
 * @param array $paysonUrl, $productInfo, $shopInfo, $moduleVersionToTracking
 * @disc the function request and redirect Payson API Sandbox
 */

/*
 * @return product list
 * @param int $id_cart
 * @disc 
 */

function orderItemsList($cart) {

    include_once(_PS_MODULE_DIR_ . 'paysondirect/payson/orderitem.php');

    $orderitemslist = array();
    foreach ($cart->getProducts() AS $cartProduct) {

        $my_taxrate = $cartProduct['rate'] / 100;
        $product_price = $cartProduct['price'];
        $attributes_small = isset($cartProduct['attributes_small']) ? $cartProduct['attributes_small'] : '';

        $orderitemslist[] = new OrderItem(
                $cartProduct['name'] . '  ' . $attributes_small, number_format($product_price, 2, '.', ''), $cartProduct['cart_quantity'], number_format($my_taxrate, 3, '.', ''), $cartProduct['id_product']
        );
    }

// check four discounts
    $cartDiscounts = $cart->getDiscounts();

    /*
      $tax_rate = 0;
      $taxDiscount = Cart::getTaxesAverageUsed((int)($cart->id));
      if (isset($taxDiscount) AND $taxDiscount != 1)
      $tax_rate = $taxDiscount * 0.01;
     */

    foreach ($cartDiscounts AS $cartDiscount) {

        $objDiscount = new Discount(intval($cartDiscount['id_discount']));
        $value = $objDiscount->getValue(sizeof($cartDiscounts), $cart->getOrderTotal(true, 1), $cart->getTotalShippingCost(), $cart->id);

        $orderitemslist[] = new OrderItem(
                $cartDiscount['name'], number_format(-$value, 2, '.', ''), 1, 0, 'Rabatt'
        );
    }

    $total_shipping_wt = _PS_VERSION_ >= 1.5 ? floatval($cart->getTotalShippingCost()) : floatval($cart->getOrderShippingCost());

    if ($total_shipping_wt > 0) {
        $carrier = new Carrier($cart->id_carrier, $cart->id_lang);

        $carriertax = Tax::getCarrierTaxRate((int) $carrier->id, $cart->id_address_invoice);
        $carriertax_rate = $carriertax / 100;

        $forward_vat = 1 + $carriertax_rate;
        $total_shipping_wot = $total_shipping_wt / $forward_vat;

        $orderitemslist[] = new OrderItem(
                isset($carrier->name) ? $carrier->name : 'shipping', number_format($total_shipping_wot, 2, '.', ''), 1, number_format($carriertax_rate, 2, '.', ''), 9998
        );
    }


    return $orderitemslist;
}

//ready, -----------------------------------------------------------------------
?>
