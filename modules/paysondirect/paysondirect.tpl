<p class="payment_module payson_pay_module">
    <script type="text/javascript">
        function disableBtn(){
             $('.payson_pay_module').html('{l s='Your order is being send to Payson for payment. Please  wait' mod='paysondirect'}');
        document.location = '{$base_dir_ssl}modules/paysondirect/redirect.php';
        }
        function disableInvBtn(){
            $('.payson_pay_module').html('{l s='Your order is being send to Payson for payment. Please  wait' mod='paysondirect'}');
            document.location = '{$base_dir_ssl}modules/paysondirect/redirect.php?method=invoice';
        }    
    </script>
    <a href="javascript:disableBtn()"  title="{l s='Pay with Payson' mod='paysondirect'} ">
        <img src="{$module_template_dir}payson.png" alt="{l s='Pay with Payson' mod='paysondirect'}" />
        <strong>{l s='Pay with Payson' mod='paysondirect'}</strong>
    </a>


{if Configuration::get('PAYSON_INVOICE_ENABLED') && Configuration::get('PAYSON_ALL_IN_ONE_ENABLED') == 0 && $paysonInvoiceAmountMinLimit && Context::getContext()->country->iso_code == 'SE'}
        <a href="javascript:disableInvBtn()"  title="{l s='Pay with Payson invoice' mod='paysondirect'}">
            <img src="{$module_template_dir}paysoninvoice.png" alt="{l s='Pay with Invoice' mod='paysondirect'}" />
            <strong>{l s='Pay with Invoice' mod='paysondirect'}</strong>	
       </a>
{/if}
 
 {if Configuration::get('PAYSON_INVOICE_ENABLED') && $paysonInvoiceAmountMinLimit && Context::getContext()->country->iso_code == 'SE'} 
   <br />. 
   {l s='If you choose to pay by Paysoninvoice so there is a fee incl. VAT' mod='paysondirect'}<strong>{$paysonInvoiceFee} SEK </strong>.
   {l s='Payment terms are 14 days and the invoice will be sent separately by email to the email address you specify' mod='paysondirect'}. 
   {l s='To pay by Paysoninvoice You must be 18 years old and be registered in Sweden as well as authorized in the credit assessment carried out at purchase' mod='paysondirect'}
   .<br />         
 {/if}
 
 </p>