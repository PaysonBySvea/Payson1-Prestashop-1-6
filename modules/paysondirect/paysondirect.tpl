<p class="payment_module">
    <a href="{$base_dir_ssl}modules/paysondirect/redirect.php" title="{l s='Pay with Payson' mod='paysondirect'}">
        <img src="{$module_template_dir}payson.png" alt="{l s='Pay with Payson' mod='paysondirect'}" />
        <strong>{l s='Pay with Payson' mod='paysondirect'}</strong>
    </a>
</p>

{if Configuration::get('PAYSON_INVOICE_ENABLED') && $paysonInvoiceAmountMinLimit}
    <p class="payment_module">
        <a href="{$base_dir_ssl}modules/paysondirect/redirect.php?method=invoice" title="{l s='Pay with Invoice' mod='paysondirect'}">
            <img src="{$module_template_dir}paysoninvoice.png" alt="{l s='Pay with Invoice' mod='paysondirect'}" />
            <strong>{l s='Pay with Invoice' mod='paysondirect'}</strong>	
       </a>
   </p>  
   {l s='If you choose to pay by Paysoninvoice so there is a fee incl. VAT' mod='paysondirect'}<strong>{$paysonInvoiceFee} SEK </strong>.
   {l s='Payment terms are 14 days and the invoice will be sent separately by email to the email address you specify' mod='paysondirect'}. 
   {l s='To pay by Paysoninvoice You must be 18 years old and be registered in Sweden as well as authorized in the credit assessment carried out at purchase' mod='paysondirect'}.<br />        
 {/if}
   
   