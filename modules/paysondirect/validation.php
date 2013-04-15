<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/paysondirect.php');
include(dirname(__FILE__) . '/../../header.php');

if (_PS_VERSION_ >> 1.5) {
    $context = Context::getContext();
    $cart = $context->cart;
}

$trackingId = $_GET["trackingId"];

$payson = new Paysondirect();

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$payson->active)
    Tools::redirectLink(__PS_BASE_URI__ . 'order.php?step=1');


// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
$authorized = false;
foreach (Module::getPaymentModules() as $module)
    if ($module['name'] == 'paysondirect') {
        $authorized = true;
        break;
    }
if (!$authorized)
    die(Tools::displayError('This payment method Payson direct is not available.'));

$db = Db::getInstance();
$q = "SELECT * FROM " . _DB_PREFIX_ . "payson_order_event WHERE tracking_id = " . $trackingId;
$res = $db->getRow($q);

if ($cart->OrderExists() == 0) {
    $customer = new Customer($cart->id_customer);
    $currency = new Currency($cookie->id_currency);
    $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

    if ($res['ipn_status'] == 'COMPLETED' && $res['type'] == 'TRANSFER' && $res['valid'] == 1) {

        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        $payson->validateOrder((int) $cart->id, _PS_OS_PAYMENT_, $total, $payson->displayName, $payson->l('Payson reference:  ') . $res['purchase_id'] . '<br />', array(), (int) $currency->id, false, $customer->secure_key);

        $order = new Order($payson->currentOrder);
        Tools::redirectLink(__PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . $cart->id . '&id_module=' . $payson->id . '&id_order=' . $payson->currentOrder . '&key=' . $customer->secure_key);
    }elseif ($res['ipn_status'] == 'PENDING' && $res['invoice_status'] == 'ORDERCREATED' && $res['valid'] == 1) {


        //since this in an invoice, we need to create shippingadress
        $address = new Address(intval($cart->id_address_delivery));
        $address->firstname = $res['shippingAddress_name'];
        $address->lastname = ' ';
        $address->address1 = $res['shippingAddress_street_ddress'];
        $address->address2 = '';
        $address->city = $res['shippingAddress_city'];
        $address->postcode = $res['shippingAddress_postal_code'];
        $address->country = $res['shippingAddress_country'];
        $address->id_customer = $cart->id_customer;
        $address->alias = "Payson account address";

        $address->update();

        $payson->validateOrder((int) $cart->id, _PS_OS_PAYMENT_, $total, $payson->displayName, $payson->l('Payson reference:  ') . $res['purchase_id'] . '<br />', array(), (int) $currency->id, false, $customer->secure_key);
        $order = new Order($payson->currentOrder);
        Tools::redirectLink(__PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . $cart->id . '&id_module=' . $payson->id . '&id_order=' . $payson->currentOrder . '&key=' . $customer->secure_key);
    } elseif ($res['ipn_status'] == 'ERROR') {
        $customer = new Customer($cart->id_customer);

        $payson->validateOrder((int) $cart->id, _PS_OS_CANCELED_, $total, $payson->displayName, $payson->l('Payson reference:  ') . $res['purchase_id'] . '   ' . $payson->l('Order denied.') . '<br />', array(), (int) $currency->id, false, $customer->secure_key);
        //Tools::redirectLink('history.php');
        $payson->paysonApiError('The payment was denied. Please try using a different payment method.');
    } else {
        $payson->validateOrder((int) $cart->id, _PS_OS_ERROR_, 0, $payson->displayName, $payson->l('The transaction could not be completed.') . '<br />', array(), (int) $currency->id, false, $customer->secure_key);
        Tools::redirectLink('history.php');
    }
}
?>