<?php
/**
 * @copyright 2010 Payson
 */
//Minimal order values
$paysonInvoiceMinimalOrderValue = 30;

//Default values in array position 0
$paysonCurrenciesSupported = array('SEK', 'EUR');
$paysonInvoiceCurrenciesSupported = array('SEK');
$paysonLanguagesSupported = array('SV', 'EN', 'FI');
$paysonFeesPayerSupported = array('EACHRECEIVER', 'PRIMARYRECEIVER', 'SENDER', 'SECONDARYONLY');

//URL for POST, GET or CURL
$paysonTokenRequestURL = "https://api.payson.se/1.0/Pay/";
$paysonBrowserRedirectURL = "https://www.payson.se/paySecure/?token=";
$paysonBrowserPostURL = "https://www.payson.se/paySecure/";
$paysonPaymentDetailsURL = "https://api.payson.se/1.0/PaymentDetails/";
$paysonIpnMessageValidationURL = "https://api.payson.se/1.0/Validate/";
$paysonPaymentUpdateURL = "https://api.payson.se/1.0/PaymentUpdate/";
//LINKs
$paysonSignInLink = "https://www.payson.se/SignIn/";
$paysonSignUpLink = "https://www.payson.se/account/signup/";
$paysonApiDocLink = "https://api.payson.se/";
$paysonInfoLink = "https://www.payson.se";

//shop texts, language dependent
$paysonShop['EN']['mark_button_img'] = "https://www.payson.se/sites/all/files/images/external/payson-72x29.jpg";
$paysonShop['SV']['mark_button_img'] = "https://www.payson.se/sites/all/files/images/external/payson-72x29.jpg";
$paysonShop['FI']['mark_button_img'] = "https://www.payson.se/sites/all/files/images/external/payson-72x29.jpg";

$paysonShop['EN']['inv_mark_button_img'] = "https://www.payson.se/sites/all/files/images/external/payson-72x29.jpg";
$paysonShop['SV']['inv_mark_button_img'] = "https://www.payson.se/sites/all/files/images/external/payson-72x29.jpg";
$paysonShop['FI']['inv_mark_button_img'] = "https://www.payson.se/sites/all/files/images/external/payson-72x29.jpg";

$paysonShop['EN']['check_out_w_payson'] = 'Checkout with Payson';
$paysonShop['SV']['check_out_w_payson'] = 'Betala med Payson';
$paysonShop['FI']['check_out_w_payson'] = 'Betala med Payson';

$paysonShop['EN']['inv_check_out_w_payson'] = 'Checkout with Payson Invoice';
$paysonShop['SV']['inv_check_out_w_payson'] = 'Betala med Payson Faktura';
$paysonShop['FI']['inv_check_out_w_payson'] = 'Betala med Payson Faktura';


$paysonShop['EN']['read_more_link'] = '<a href="' . $paysonInfoLink . '" target="_blank">Read more</a>';
$paysonShop['SV']['read_more_link'] = '<a href="' . $paysonInfoLink . '" target="_blank">Läs mer</a>';
$paysonShop['FI']['read_more_link'] = '<a href="' . $paysonInfoLink . '" target="_blank">Läs mer</a>';

$paysonShop['EN']['order_id_from_text'] = 'Order: %s from ';
$paysonShop['SV']['order_id_from_text'] = 'Order: %s från ';
$paysonShop['FI']['order_id_from_text'] = 'Order: %s från ';

$paysonShop['EN']['order_id_from_text_short'] = 'Order: %s';
$paysonShop['SV']['order_id_from_text_short'] = 'Order: %s';
$paysonShop['FI']['order_id_from_text_short'] = 'Order: %s';


$paysonShop['EN']['mailtext_paysonreference'] = 'Payment Approved by Payson with Referece Number';
$paysonShop['SV']['mailtext_paysonreference'] = 'Betalning har genomförts via Payson med Referensnummer';
$paysonShop['FI']['mailtext_paysonreference'] = 'Betalning har genomförts via Payson med Referensnummer';

$paysonShop['EN']['inv_mailtext_paysonreference'] = 'Invoice is sent from Payson upon delivery. Reference Number';
$paysonShop['SV']['inv_mailtext_paysonreference'] = 'Faktura från Payson skickas när varorna skickats. Referensnummer';
$paysonShop['FI']['inv_mailtext_paysonreference'] = 'Faktura från Payson skickas när varorna skickats. Referensnummer';


//do not increase text length on this, Prestashop use a max length of 32 chars and the Payson payment ref must also be included.
$paysonShop['EN']['paysonreference_ps'] = 'Payson RefNr: ';
$paysonShop['SV']['paysonreference_ps'] = 'Payson Refnr: ';
$paysonShop['FI']['paysonreference_ps'] = 'Payson Refnr: ';

$paysonShop['EN']['inv_paysonreference_ps'] = 'Payson Invoice RefNr: ';
$paysonShop['SV']['inv_paysonreference_ps'] = 'Payson Faktura Refnr: ';
$paysonShop['FI']['inv_paysonreference_ps'] = 'Payson Faktura Refnr: ';



//admin texts,
$paysonAdmin['EN']['text_admin_title'] = "Payson New API";
$paysonAdmin['SV']['text_admin_title'] = "Payson Nytt API";
$paysonAdmin['FI']['text_admin_title'] = "Payson Nytt API";

$paysonAdmin['EN']['inv_text_admin_title'] = "Payson Invoice New API";
$paysonAdmin['SV']['inv_text_admin_title'] = "Payson Faktura Nytt API";
$paysonAdmin['FI']['inv_text_admin_title'] = "Payson Faktura Nytt API";


$paysonAdmin['EN']['text_catalog_title'] = "Payson";
$paysonAdmin['SV']['text_catalog_title'] = "Payson";
$paysonAdmin['FI']['text_catalog_title'] = "Payson";


$paysonAdmin['EN']['inv_text_catalog_title'] = "Payson Invoice";
$paysonAdmin['SV']['inv_text_catalog_title'] = "Payson Faktura";
$paysonAdmin['FI']['inv_text_catalog_title'] = "Payson Faktura";


$paysonAdmin['EN']['config_instruction1'] = '<strong>Payson</strong><br /><a href="' . $paysonSignInLink . '" target="_blank">Manage your Payson account.</a><br /><br /><font color="green">Configuration Instructions:</font><br />
  1. <a href="' . $paysonSignUpLink . '" target="_blank">Sign up for your Payson account - click here.</a><br />';
$paysonAdmin['SV']['config_instruction1'] = '<strong>Payson</strong><br /><a href="' . $paysonSignInLink . '" target="_blank">Hantera ditt Paysonkonto.</a><br /><br /><font color="green">Konfigureringsinstruktioner:</font><br />
  1. <a href="' . $paysonSignUpLink . '" target="_blank">Skapa ditt Paysonkonto - klicka här.</a><br />';
$paysonAdmin['SV']['config_instruction1'] = '<strong>Payson</strong><br /><a href="' . $paysonSignInLink . '" target="_blank">Hantera ditt Paysonkonto.</a><br /><br /><font color="green">Konfigureringsinstruktioner:</font><br />
  1. <a href="' . $paysonSignUpLink . '" target="_blank">Skapa ditt Paysonkonto - klicka här.</a><br />';

$paysonAdmin['EN']['config_instruction2'] = '2. ...and click "install" above to enable Payson support... and "edit" your Payson settings.';
$paysonAdmin['SV']['config_instruction2'] = '2. ...och klicka "install" ovan för att aktivera Payson support... och "edit" dina Paysoninställningar.';
$paysonAdmin['FI']['config_instruction2'] = '2. ...och klicka "install" ovan för att aktivera Payson support... och "edit" dina Paysoninställningar.';


$paysonAdmin['EN']['config_instruction2_vm'] = '2. ...and fill in form below to enable Payson support... and "save" your Payson settings.';
$paysonAdmin['SV']['config_instruction2_vm'] = '2. ...och fyll i formulär nedan för att aktivera Payson support... och "spara" dina Paysoninställningar.';
$paysonAdmin['FI']['config_instruction2_vm'] = '2. ...och fyll i formulär nedan för att aktivera Payson support... och "spara" dina Paysoninställningar.';




$paysonAdmin['EN']['config_instruction3'] = '</ul><font color="green"><hr /><strong>Requirements:</strong></font><br /><hr />*<strong>Payson Account</strong> (<a href="' . $paysonSignUpLink . '" target="_blank">click to signup</a>)<br />*<strong>*<strong>Port 80</strong> is used for bidirectional communication with the gateway, so must be open on your host\'s router/firewall<br />*<strong>Settings</strong> must be configured as described above.';
$paysonAdmin['SV']['config_instruction3'] = '</ul><font color="green"><hr /><strong>Krav:</strong></font><br /><hr />*<strong>Paysonkonto</strong> (<a href="' . $paysonSignUpLink . '" target="_blank">klicka här för att skapa</a>)<br />*<strong>*<strong>Port 80</strong> används för dubbelriktad kommunikation med Paysons server, så den måste vara öppen i din host\'s router/firewall<br />*<strong>Inställningar</strong> måste konfigureras enligt ovan beskrivet.';
$paysonAdmin['FI']['config_instruction3'] = '</ul><font color="green"><hr /><strong>Krav:</strong></font><br /><hr />*<strong>Paysonkonto</strong> (<a href="' . $paysonSignUpLink . '" target="_blank">klicka här för att skapa</a>)<br />*<strong>*<strong>Port 80</strong> används för dubbelriktad kommunikation med Paysons server, så den måste vara öppen i din host\'s router/firewall<br />*<strong>Inställningar</strong> måste konfigureras enligt ovan beskrivet.';

$paysonAdmin['EN']['vm_invoiceFee_text'] = 'To use invoiceFee(max 30 SEK), apply a negative discount on this payment method.';
$paysonAdmin['SV']['vm_invoiceFee_text'] = 'För att använda fakturaavgift(max 30 SEK), använd en negativ rabatt för denna betalmetod';
$paysonAdmin['FI']['vm_invoiceFee_text'] = 'För att använda fakturaavgift(max 30 SEK), använd en negativ rabatt för denna betalmetod';

$paysonAdmin['EN']['accept_payson'] = 'Do you want to accept Payson payments?';
$paysonAdmin['SV']['accept_payson'] = 'Vill du ta emot betalningar med Payson?';
$paysonAdmin['FI']['accept_payson'] = 'Vill du ta emot betalningar med Payson?';


$paysonAdmin['EN']['inv_accept_payson'] = 'Do you want to accept Payson Invoice payments?';
$paysonAdmin['SV']['inv_accept_payson'] = 'Vill du ta emot betalningar med Payson Faktura?';
$paysonAdmin['FI']['inv_accept_payson'] = 'Vill du ta emot betalningar med Payson Faktura?';



$paysonAdmin['EN']['enable_payson'] = 'Enable Payson Module';
$paysonAdmin['SV']['enable_payson'] = 'Aktivera Paysonmodul';
$paysonAdmin['FI']['enable_payson'] = 'Aktivera Paysonmodul';

$paysonAdmin['EN']['inv_enable_payson'] = 'Enable Payson Invoice Module';
$paysonAdmin['SV']['inv_enable_payson'] = 'Aktivera Payson fakturamodul';
$paysonAdmin['FI']['inv_enable_payson'] = 'Aktivera Payson fakturamodul';


$paysonAdmin['EN']['inv_fee'] = 'Invoice Fee';
$paysonAdmin['SV']['inv_fee'] = 'Faktureringsavgift';
$paysonAdmin['FI']['inv_fee'] = 'Faktureringsavgift';




$paysonAdmin['EN']['agentid_head'] = 'Agent Id';
$paysonAdmin['SV']['agentid_head'] = 'Agentid';
$paysonAdmin['FI']['agentid_head'] = 'Agentid';

$paysonAdmin['EN']['agentid_text'] = 'Agent Id for your Payson account.';
$paysonAdmin['SV']['agentid_text'] = 'AgentId för ditt Paysonkonto.';
$paysonAdmin['FI']['agentid_text'] = 'AgentId för ditt Paysonkonto.';

$paysonAdmin['EN']['selleremail_head'] = 'Seller Email';
$paysonAdmin['EN']['selleremail_text'] = 'Email address for your Payson account.<br />NOTE: This must match <strong>EXACTLY </strong>the primary email address on your Payson account settings.';
$paysonAdmin['SV']['selleremail_head'] = 'Säljarens Email';
$paysonAdmin['SV']['selleremail_text'] = 'Emailadress för ditt Paysonkonto.<br />OBS: Denna måste vara <strong>identisk </strong>med den emailadress som för ditt Paysonkonto.';
$paysonAdmin['FI']['selleremail_head'] = 'Säljarens Email';
$paysonAdmin['FI']['selleremail_text'] = 'Emailadress för ditt Paysonkonto.<br />OBS: Denna måste vara <strong>identisk </strong>med den emailadress som för ditt Paysonkonto.';

$paysonAdmin['EN']['md5key_head'] = 'MD5 Key';
$paysonAdmin['EN']['md5key_text'] = 'MD5 Key for your Payson account.';
$paysonAdmin['SV']['md5key_head'] = 'MD5nyckel';
$paysonAdmin['SV']['md5key_text'] = 'MD5nyckel för ditt Paysonkonto';
$paysonAdmin['FI']['md5key_head'] = 'MD5nyckel';
$paysonAdmin['FI']['md5key_text'] = 'MD5nyckel för ditt Paysonkonto';

$paysonAdmin['EN']['paymentmethods_head'] = 'Payment methods';
$paysonAdmin['EN']['paymentmethods_text'] = 'Whether all or some Payment Methods should be available at Payson';
$paysonAdmin['SV']['paymentmethods_head'] = 'Betalningsmöjligheter';
$paysonAdmin['SV']['paymentmethods_text'] = 'Om alla eller endast ett urval av betalningsmöjligheter skall erbjudas hos Payson';
$paysonAdmin['FI']['paymentmethods_head'] = 'Betalningsmöjligheter';
$paysonAdmin['FI']['paymentmethods_text'] = 'Om alla eller endast ett urval av betalningsmöjligheter skall erbjudas hos Payson';

$paysonAdmin['EN']['paymentmethods_all'] = 'All';
$paysonAdmin['SV']['paymentmethods_all'] = 'Alla';
$paysonAdmin['FI']['paymentmethods_all'] = 'Alla';

$paysonAdmin['EN']['paymentmethods_some'] = 'Some, as below';
$paysonAdmin['SV']['paymentmethods_some'] = 'Några enligt nedan';
$paysonAdmin['FI']['paymentmethods_some'] = 'Några enligt nedan';

$paysonAdmin['EN']['vm_extrainfo_text'] = 'If the Payment Extra Info field is blank you must click this button below!';
$paysonAdmin['SV']['vm_extrainfo_text'] = 'Om fältet Payment Extra Info nedan är tomt måste du klicka på knappen nedan!';
$paysonAdmin['FI']['vm_extrainfo_text'] = 'Om fältet Payment Extra Info nedan är tomt måste du klicka på knappen nedan!';

$paysonAdmin['EN']['vm_extrainfo_button_text'] = 'Populate field below automatic';
$paysonAdmin['SV']['vm_extrainfo_button_text'] = 'Fyll i fältet nedan automatiskt';
$paysonAdmin['FI']['vm_extrainfo_button_text'] = 'Fyll i fältet nedan automatiskt';


$paysonAdmin['EN']['paymethoditems_head'] = 'Select Payment methods';
$paysonAdmin['EN']['paymethoditems_text'] = 'Check the Payment Methods that should be available at Payson';
$paysonAdmin['SV']['paymethoditems_head'] = 'Välj betalmöjligheter';
$paysonAdmin['SV']['paymethoditems_text'] = 'Markera de betalningsmöjligheter som skall erbjudas hos Payson';
$paysonAdmin['FI']['paymethoditems_head'] = 'Välj betalmöjligheter';
$paysonAdmin['FI']['paymethoditems_text'] = 'Markera de betalningsmöjligheter som skall erbjudas hos Payson';

$paysonAdmin['EN']['paysonguarantee_head'] = 'Payson Guarantee';
$paysonAdmin['EN']['paysonguarantee_text'] = 'Whether Payson Guarantee is offered or not.';
$paysonAdmin['SV']['paysonguarantee_head'] = 'Paysongaranti';
$paysonAdmin['SV']['paysonguarantee_text'] = 'Om Paysongaranti skall användas eller ej';
$paysonAdmin['FI']['paysonguarantee_head'] = 'Paysongaranti';
$paysonAdmin['FI']['paysonguarantee_text'] = 'Om Paysongaranti skall användas eller ej';

$paysonAdmin['EN']['custommess_head'] = 'Custom message';
$paysonAdmin['EN']['custommess_text'] = 'Custom message, common for all orders.';
$paysonAdmin['SV']['custommess_head'] = 'Meddelande';
$paysonAdmin['SV']['custommess_text'] = 'Meddelande, likadant för alla ordrar.';
$paysonAdmin['FI']['custommess_head'] = 'Meddelande';
$paysonAdmin['FI']['custommess_text'] = 'Meddelande, likadant för alla ordrar.';

$paysonAdmin['EN']['inv_module_uninstalled'] = 'Payson Invoice Uninstalled';
$paysonAdmin['SV']['inv_module_uninstalled'] = 'Payson Faktura avinstallerad';
$paysonAdmin['FI']['inv_module_uninstalled'] = 'Payson Faktura avinstallerad';

$paysonAdmin['EN']['inv_module_installed'] = 'Payson Invoice Installed';
$paysonAdmin['SV']['inv_module_installed'] = 'Payson Faktura installerad';
$paysonAdmin['FI']['inv_module_installed'] = 'Payson Faktura installerad';


$paysonAdmin['EN']['module_uninstalled'] = 'Payson Uninstalled';
$paysonAdmin['SV']['module_uninstalled'] = 'Payson avinstallerad';
$paysonAdmin['FI']['module_uninstalled'] = 'Payson avinstallerad';

$paysonAdmin['EN']['module_installed'] = 'Payson Installed';
$paysonAdmin['SV']['module_installed'] = 'Payson installerad';
$paysonAdmin['FI']['module_installed'] = 'Payson installerad';

$paysonAdmin['EN']['inv_fee_title'] = 'Payson Invoice Fee';
$paysonAdmin['SV']['inv_fee_title'] = 'Payson Faktureringsavgift';
$paysonAdmin['FI']['inv_fee_title'] = 'Payson Faktureringsavgift';


$paysonAdmin['EN']['inv_fee_desc'] = 'Possibility to add an invoice fee for Payson Invoice';
$paysonAdmin['SV']['inv_fee_desc'] = 'Möjlighet att lägga på faktureringsavgift för Payson Faktura';
$paysonAdmin['FI']['inv_fee_desc'] = 'Möjlighet att lägga på faktureringsavgift för Payson Faktura';


$paysonAdmin['EN']['inv_fee_enable_head'] = 'Do you want to add invoice fee?';
$paysonAdmin['SV']['inv_fee_enable_head'] = 'Vill du lägga på faktureringavgift?';
$paysonAdmin['FI']['inv_fee_enable_head'] = 'Vill du lägga på faktureringavgift?';

$paysonAdmin['EN']['inv_fee_enable_text'] = 'An invoice fee between 0-30 SEK could be added';
$paysonAdmin['SV']['inv_fee_enable_text'] = 'En faktureringsavgift mellan 0-30 SEK kan läggas till';
$paysonAdmin['FI']['inv_fee_enable_text'] = 'En faktureringsavgift mellan 0-30 SEK kan läggas till';

$paysonAdmin['EN']['inv_fee_amount_head'] = 'Invoice Fee';
$paysonAdmin['SV']['inv_fee_amount_head'] = 'Faktureringsavgift';
$paysonAdmin['FI']['inv_fee_amount_head'] = 'Faktureringsavgift';

$paysonAdmin['EN']['inv_fee_amount_text'] = 'Invoice Fee';
$paysonAdmin['SV']['inv_fee_amount_text'] = 'Faktureringsavgift';
$paysonAdmin['FI']['inv_fee_amount_text'] = 'Faktureringsavgift';

//inv updating in admin
$paysonAdmin['EN']['inv_nosuchorder'] = 'Can not find an Payson Invoice buy on that order';
$paysonAdmin['SV']['inv_nosuchorder'] = 'Hittar inget Payson fakturaköp för denna order';
$paysonAdmin['FI']['inv_nosuchorder'] = 'Hittar inget Payson fakturaköp för denna order';

$paysonAdmin['EN']['inv_cant_update'] = 'Can not update invoice to desired invoice status';
$paysonAdmin['SV']['inv_cant_update'] = 'Kan inte uppdatera fakturastatus till önskad status';
$paysonAdmin['FI']['inv_cant_update'] = 'Kan inte uppdatera fakturastatus till önskad status';

$paysonAdmin['EN']['inv_update_fail'] = 'Failed update status to ';
$paysonAdmin['SV']['inv_update_fail'] = 'Misslyckades att uppdatera status till ';
$paysonAdmin['FI']['inv_update_fail'] = 'Misslyckades att uppdatera status till ';

$paysonAdmin['EN']['inv_update_ok'] = 'Updated the invoice status of the Payson Invoice to ';
$paysonAdmin['SV']['inv_update_ok'] = 'Uppdaterade Paysonfakturans status till ';
$paysonAdmin['FI']['inv_update_ok'] = 'Uppdaterade Paysonfakturans status till ';

$paysonAdmin['EN']['inv_statuschange_hint_head'] = 'Payson Invoice Status Change Hints';
$paysonAdmin['SV']['inv_statuschange_hint_head'] = 'Payson Faktura, hjälp för statusändring';
$paysonAdmin['FI']['inv_statuschange_hint_head'] = 'Payson Faktura, hjälp för statusändring';

$paysonAdmin['EN']['inv_statuschange_hint1'] = 'Update order status to %s will change the Payson Invoice to %s';
$paysonAdmin['SV']['inv_statuschange_hint1'] = 'Uppdatering av orderstatus till %s kommer att ändra Payson Fakturan till %s';
$paysonAdmin['FI']['inv_statuschange_hint1'] = 'Uppdatering av orderstatus till %s kommer att ändra Payson Fakturan till %s';

$paysonAdmin['EN']['inv_current_status_head'] = 'Current Payson Invoice status: ';
$paysonAdmin['SV']['inv_current_status_head'] = 'Nuvarande Payson Faktura status: ';
$paysonAdmin['FI']['inv_current_status_head'] = 'Nuvarande Payson Faktura status: ';

$paysonAdmin['EN']['inv_status_history_head'] = 'History';
$paysonAdmin['SV']['inv_status_history_head'] = 'Historik';
$paysonAdmin['FI']['inv_status_history_head'] = 'Historik';

$paysonAdmin['EN']['inv_status_history_head2'] = '<td>Date</td> <td>Status</td> <td>Update</td>';
$paysonAdmin['SV']['inv_status_history_head2'] = '<td>Datum</td> <td>Status</td> <td>Uppdatering</td>';
$paysonAdmin['FI']['inv_status_history_head2'] = '<td>Datum</td> <td>Status</td> <td>Uppdatering</td>';

//db table names
$paysonDbTableOrderEvents = "payson_order_event";

?>