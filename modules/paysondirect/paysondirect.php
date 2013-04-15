<?php

//include_once(_PS_MODULE_DIR_.'paysoninvoice/payson_api/functions.payson.php');
//include(_PS_MODULE_DIR_.'paysondirect/payson_api/functions.payson.php');

class Paysondirect extends PaymentModule {

    private $_html = '';
    private $_postErrors = array();
    private $invoiceAmountMinLimit = 30;

    public function __construct() {
        $this->name = 'paysondirect';
        $this->tab = 'payments_gateways';
        $this->version = '2.1';
        $this->currencies = true;
        $this->author = 'Payson AB';
        $this->module_key = '94873fa691622bfefa41af2484650a2e';
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Payson');
        $this->description = $this->l('Accept payments by Payson via card (Visa, Mastercard), direct bank transfer and invoice');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
    }

    public function install() {
        include(_PS_MODULE_DIR_ . 'paysondirect/payson_api/def.payson.php');

        $table_name_order_events = _DB_PREFIX_ . $paysonDbTableOrderEvents;
        $q = $this->paysonCreateTransOrderEventsTableQuery($table_name_order_events);

        $db = Db::getInstance();
        $result = $db->Execute($q);

        if (!parent::install()
                OR !Configuration::updateValue('PAYSON_EMAIL', Configuration::get('PS_SHOP_EMAIL'))
                OR !Configuration::updateValue('PAYSON_AGENTID', '')
                OR !Configuration::updateValue('PAYSON_MD5KEY', '')
                OR !Configuration::updateValue('PAYSON_SANDBOX_CUSTOMER_EMAIL', 'test-shopper@payson.se')
                OR !Configuration::updateValue('PAYSON_SANDBOX_AGENTID', '1')
                OR !Configuration::updateValue('PAYSON_SANDBOX_MD5KEY', 'fddb19ac-7470-42b6-a91d-072cb1495f0a')
                OR !Configuration::updateValue('PAYSON_PAYMENTMETHODS', 'all')
                OR !Configuration::updateValue('PAYSON_INVOICE_ENABLED', '0')
                OR !Configuration::updateValue('PAYSON_MODE', 'sandbox')
                OR !Configuration::updateValue('PAYSON_GUARANTEE', 'NO')
                OR !Configuration::updateValue('PAYSON_MODULE_VERSION', 'PAYSON-PRESTASHOP-' . $this->version)
                OR !Configuration::updateValue('PAYSON_LOGS', 'no')
                OR !$this->registerHook('payment')
                OR !$this->registerHook('paymentReturn'))
            return false;
        return true;
    }

    public function uninstall() {
        return (parent::uninstall() AND
                Configuration::deleteByName('PAYSON_EMAIL') AND
                Configuration::deleteByName('PAYSON_AGENTID') AND
                Configuration::deleteByName('PAYSON_MD5KEY') AND
                Configuration::deleteByName('PAYSON_PAYMENTMETHODS') AND
                Configuration::deleteByName('PAYSON_INVOICE_ENABLED') AND
                Configuration::deleteByName('PAYSON_GUARANTEE') AND
                Configuration::deleteByName('PAYSON_MODE') AND
                Configuration::deleteByName('PAYSON_LOGS') AND
                Configuration::deleteByName('PAYSON_MODULE_VERSION'));
    }

    public function getContent() {
        $this->_html = '<h2>' . $this->l('Payson') . '</h2>';
        if (isset($_POST['submitPayson'])) {
            if (Configuration::get('PAYSON_MODE') != 'sandbox') {
                if (empty($_POST['email']))
                    $this->_postErrors[] = $this->l('Payson business e-mail address is required.');
                elseif (!Validate::isEmail($_POST['email']))
                    $this->_postErrors[] = $this->l('Payson business must be an e-mail address.');

                if (empty($_POST['md5key']))
                    $this->_postErrors[] = $this->l('Payson Md5 key is required.');
                if (empty($_POST['agentid']))
                    $this->_postErrors[] = $this->l('Payson Agent Id is required.');
            }

            if ($mode = (Tools::getValue('payson_mode') == 'real' ? 'real' : 'sandbox'))
                Configuration::updateValue('PAYSON_MODE', $mode);

            if ($logPayson = (Tools::getValue('payson_log') == 'yes' ? 'yes' : 'no'))
                Configuration::updateValue('PAYSON_LOGS', $logPayson);

            if (!sizeof($this->_postErrors)) {
                Configuration::updateValue('PAYSON_EMAIL', strval($_POST['email']));
                Configuration::updateValue('PAYSON_AGENTID', intval($_POST['agentid']));
                Configuration::updateValue('PAYSON_MD5KEY', strval($_POST['md5key']));
                Configuration::updateValue('PAYSON_PAYMENTMETHODS', strval($_POST['paymentmethods']));

                if (!isset($_POST['enableInvoice']))
                    Configuration::updateValue('PAYSON_INVOICE_ENABLED', '0');
                else
                    Configuration::updateValue('PAYSON_INVOICE_ENABLED', strval($_POST['enableInvoice']));

                $this->displayConf();
            }
            else
                $this->displayErrors();
        }

        $this->displayPayson();
        $this->displayFormSettings();
        return $this->_html;
    }

    public function displayConf() {
        $this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />
			' . $this->l('Settings updated') . '
		</div>';
    }

    public function displayErrors() {
        $nbErrors = sizeof($this->_postErrors);
        $this->_html .= '
		<div class="alert error">
			<h3>' . ($nbErrors > 1 ? $this->l('There are') : $this->l('There is')) . ' ' . $nbErrors . ' ' . ($nbErrors > 1 ? $this->l('errors') : $this->l('error')) . '</h3>
			<ol>';
        foreach ($this->_postErrors AS $error)
            $this->_html .= '<li>' . $error . '</li>';
        $this->_html .= '
			</ol>
		</div>';
    }

    public function displayPayson() {
        global $cookie;
        include(_PS_MODULE_DIR_ . 'paysondirect/payson_api/def.payson.php');


        $this->_html .= '
		<img src="../modules/paysondirect/payson.png" style="float:left; margin-right:15px;" /><br/>
		<b>' . $this->l('This module allows you to accept payments by Payson.') . '</b><br /><br />
		' . $this->l('You need to apply for and be cleared for payments by Payson before using this module.') . '
		<br /><br /><br />';
    }

    public function displayFormSettings() {

        $conf = Configuration::getMultiple(array(
                    'PAYSON_AGENTID',
                    'PAYSON_MD5KEY',
                    'PAYSON_EMAIL',
                    'PAYSON_PAYMENTMETHODS',
                    'PAYSON_INVOICE_ENABLED'
                ));

        $payson_mode_text = 'Currently using <strong>' . Configuration::get('PAYSON_MODE') . '</strong> mode.';

        $agentid = array_key_exists('agentid', $_POST) ? $_POST['agentid'] : (array_key_exists('PAYSON_AGENTID', $conf) ? $conf['PAYSON_AGENTID'] : '');
        $md5key = array_key_exists('md5key', $_POST) ? $_POST['md5key'] : (array_key_exists('PAYSON_MD5KEY', $conf) ? $conf['PAYSON_MD5KEY'] : '');
        $email = array_key_exists('email', $_POST) ? $_POST['email'] : (array_key_exists('PAYSON_EMAIL', $conf) ? $conf['PAYSON_EMAIL'] : '');

        $enableInvoice = array_key_exists('enableInvoice', $_POST) ? $_POST['enableInvoice'] : (array_key_exists('PAYSON_INVOICE_ENABLED', $conf) ? $conf['PAYSON_INVOICE_ENABLED'] : '0');

        $this->_html .= '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" style="clear: both;">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />' . $this->l('Settings') . '</legend>
	
				<div class="warn">
					' . $this->l('Module version' . $this->version) . '
				</div>
	
				<br /><br />
				' . $this->l('Select the mode (Real or Sandbox).') . '<br />
				' . $this->l('Mode:  ') . '
							
				<select name="payson_mode">
						<option value="real"' . (Configuration::get('PAYSON_MODE') == 'real' ? ' selected="selected"' : '') . '>' . $this->l('Real') . '&nbsp;&nbsp;</option>
						<option value="sandbox"' . (Configuration::get('PAYSON_MODE') == 'sandbox' ? ' selected="selected"' : '') . '>' . $this->l('Sandbox') . '&nbsp;&nbsp;</option>
				</select><br />
				
				' . $this->l($payson_mode_text) . '<br /><br />
				
				' . $this->l('Pay with Payson (Visa, Mastercard & Internetbank).') . '<br />
				' . $this->l('Payment methods:  ') . '
				<select name="paymentmethods">
						<option value=""' . (Configuration::get('PAYSON_PAYMENTMETHODS') == '""' ? ' selected="selected"' : '') . '>' . $this->l('CREDITCARD/BANK') . '&nbsp;&nbsp;</option>
						<option value="1"' . (Configuration::get('PAYSON_PAYMENTMETHODS') == '1' ? ' selected="selected"' : '') . '>' . $this->l('CREDITCARD') . '&nbsp;&nbsp;</option>
						<option value="2"' . (Configuration::get('PAYSON_PAYMENTMETHODS') == '2' ? ' selected="selected"' : '') . '>' . $this->l('BANK') . '&nbsp;&nbsp;</option>
				</select><br /><br />
                                
                                
                                
                                ' . $this->l('Enable Payson Invoice.') .
                ' <input type="checkbox" size="45" name="enableInvoice" value="1" ' . ($enableInvoice == "1" ? "checked=checked" : '') . '" /><br />
                                ' . $this->l('Payson Invoice requires a separate contract. Please contact Payson for more information') . '<br /><br />
                                
				' . $this->l('Enter your seller email for Paysondirect.') . '<br />
				' . $this->l('Seller Email:  ') . '
				<input type="text" size="45" name="email" value="' . htmlentities($email, ENT_COMPAT, 'UTF-8') . '" /><br /><br />
			
				' . $this->l('Enter your agent id for Paysondirect.') . '<br />
				' . $this->l('Agent id:  ') . '
				<input type="text" size="10" name="agentid" value="' . htmlentities($agentid, ENT_COMPAT, 'UTF-8') . '" /><br /><br />
			
				' . $this->l('Enter your MD5 key for Paysondirect.') . '<br />
				' . $this->l('MD5 key:  ') . '
				<input type="text" size="45" name="md5key" value="' . htmlentities($md5key, ENT_COMPAT, 'UTF-8') . '" /><br /><br />
				
				
				' . $this->l('Troubleshoot response from Payson Direct.') . '<br />
				' . $this->l('Logg:') . '
							
				<select name="payson_log">
						<option value="yes"' . (Configuration::get('PAYSON_LOGS') == 'yes' ? ' selected="selected"' : '') . '>' . $this->l('Yes') . '&nbsp;&nbsp;</option>
						<option value="no"' . (Configuration::get('PAYSON_LOGS') == 'no' ? ' selected="selected"' : '') . '>' . $this->l('No') . '&nbsp;&nbsp;</option>
				</select><br />
				' . $this->l('You can find your logs in Admin | Advanced Parameter -> Logs.') . '
				<br /><br />
                                ' . $this->l('Current invoice fee incl. VAT: ') .  htmlentities($this->paysonInvoiceFee(), ENT_COMPAT, 'UTF-8') . '<br /><br /><br />

				<strong>' . $this->l('Instructions for the invoice fee:') . '</strong><br /><br />
				' . $this->l('The module retrives the invoice fee as a product from your webshop. You have to create a product with a specific reference called "PS_FA".') . '<br /><br />
				' . $this->l('* Go to catalog-> product in your admin.') . '<br />
				' . $this->l('* Click Add new.') . '<br />
				' . $this->l('* Enter the name, Reference, price, tax, status (Disabled) and save the product.') . '<br /><br />
				' . $this->l('The retail price with tax must be in the range 0 to 40 SEK.') . '<br />
				' . $this->l('The tax must be 25 %.') . '<br /><br /><br />
                                <br /><br />
				
				<center><input type="submit" name="submitPayson" value="' . $this->l('Update settings') . '" class="button" /></center>
		</fieldset>
		</form><br /><br />
		<fieldset class="width3">
			<legend><img src="../img/admin/warning.gif" />' . $this->l('Information') . '</legend>'
                . $this->l('Note that Payson only accept SEK and EUR.') . '<br />
		</fieldset>';
    }

    public function hookPayment($params) {
        global $smarty;
        if (!$this->active)
            return;
        if (!$this->_checkCurrency($params['cart']))
            return;
        
        $paysonInvoiceFee = $this->paysonInvoiceFee();
        
        if ($params['cart']->getOrderTotal(true, Cart::BOTH) >= $this->invoiceAmountMinLimit){
            $smarty->assign('paysonInvoiceAmountMinLimit', true);
        }else
            $smarty->assign('paysonInvoiceAmountMinLimit', false);    

        $smarty->assign('paysonInvoiceFee', number_format($paysonInvoiceFee, 2, '.', ','));
        
        return $this->display(__FILE__, 'paysondirect.tpl');
    }

    public function hookPaymentReturn($params) {
        if (!$this->active)
            return;

        return $this->display(__FILE__, 'confirmation.tpl');
    }

    public function getL($key) {
        include(_PS_MODULE_DIR_ . 'paysondirect/payson_api/def.payson.php');

        $translations = array(
            'Your seller e-mail' => $this->l('Your seller e-mail'),
            'Your agent id' => $this->l('Your agent id'),
            'Your md5 key' => $this->l('Your md5 key'),
            'Payson guarantee' => $this->l('Payson guarantee'),
            'Payson guarantee. See Payson to what the values refer to regarding conditions etc.' => $this->l('Payson guarantee. See Payson to what the values refer to regarding conditions etc.'),
            'Paymentmethods' => $this->l('Paymentmethods'),
            'Choose if all or only some paymentmethods should be available. If set to some, specify below which to use' => $this->l('Choose if all or only some paymentmethods should be available. If set to some, specify below which to use'),
            'Custom message' => $this->l('Custom message'),
            'Update settings' => $this->l('Update settings'),
            'Information' => $this->l('Information'),
            'All PrestaShop currencies must be configured</b> inside Profile > Financial Information > Currency balances' => $this->l('All PrestaShop currencies must be configured</b> inside Profile > Financial Information > Currency balances'),
            'Note that Payson only accept SEK and EUR.' => $this->l('Note that Payson only accept SEK and EUR.'),
            'Payson' => $this->l('Payson'),
            'Accepts payments by Payson' => $this->l('Accepts payments by Payson'),
            'Are you sure you want to delete your details?' => $this->l('Are you sure you want to delete your details?'),
            'Payson business e-mail address is required.' => $this->l('Payson business e-mail address is required.'),
            'Payson business must be an e-mail address.' => $this->l('Payson business must be an e-mail address.'),
            'Payson Agent Id is required.' => $this->l('Payson Agent Id is required.'),
            'Payson Md5 key is required.' => $this->l('Payson Md5 key is required.'),
            'Payson Agent Id is required.' => $this->l('Payson Agent Id is required.'),
            'mc_gross' => $this->l('Payson key \'mc_gross\' not specified, can\'t control amount paid.'),
            'payment' => $this->l('Payment: '),
            'cart' => $this->l('Cart not found'),
            'order' => $this->l('Order has already been placed'),
            'transaction' => $this->l('Payson Transaction ID: '),
            'verified' => $this->l('The Payson transaction could not be VERIFIED.'),
            'Payson error: (invalid or undefined business account email)' => $this->l('Payson error: (invalid or undefined business account email)'),
            'Payson error: (invalid customer)' => $this->l('Payson error: (invalid customer)'),
            'Payson error: (invalid address)' => $this->l('Payson error: (invalid address)')
        );
        return $translations[$key];
    }

    private function _checkCurrency($cart) {
        $currency_order = new Currency(intval($cart->id_currency));
        $currencies_module = $this->getCurrency();
        $currency_default = Configuration::get('PS_CURRENCY_DEFAULT');

        if (strtoupper($currency_order->iso_code) != 'SEK' && strtoupper($currency_order->iso_code) != 'EUR')
            return;

        if (is_array($currencies_module))
            foreach ($currencies_module AS $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;
    }

    private function paysonCreateTransOrderEventsTableQuery($table_name) {
        return " CREATE TABLE IF NOT EXISTS " . $table_name . " (
	          `payson_events_id` int(11) unsigned NOT NULL auto_increment,
			   `order_id` int(15),
			  `added` datetime,
			  `updated` datetime DEFAULT NULL,
			  `valid` tinyint(1),
			  `ipn_status` varchar(65),
			  `token` varchar(40) NOT NULL default '',
			  `sender_email` varchar(50),
			  `currency_code` varchar(5),
			  `tracking_id`  varchar(40) NOT NULL default '',
			  `type` varchar(50),
			  `purchase_id` varchar(50),
			  `invoice_status` varchar(50),
			  `customer` varchar(50),
			  `shippingAddress_name` varchar(50),
			  `shippingAddress_street_ddress` varchar(60),
			  `shippingAddress_postal_code` varchar(20),
			  `shippingAddress_city` varchar(60),
			  `shippingAddress_country` varchar(60),
			   PRIMARY KEY  (`payson_events_id`)
	        ) ENGINE=MyISAM";
    }

    function paysonInvoiceFee() {
        $invoicefee = Db::getInstance()->getRow("SELECT id_product FROM " . _DB_PREFIX_ . "product WHERE reference = 'PS_FA'");
        if (isset($invoicefee['id_product']) AND (int) ($invoicefee['id_product']) > 0) {
            $feeproduct = new Product((int) ($invoicefee['id_product']), true);
            return $feeproduct->getPrice();
        }
        else
            return null;
    }
    
	public function paysonApiError($error) {
			$error_code = '<html>
								<head>
									<script type="text/javascript"> 
										alert("'.$error.'");
										window.location="'.('/index.php?controller=order').'";
									</script>
								</head>
						</html>';
			echo $error_code;
			exit;
			
	}

}
//end class
?>