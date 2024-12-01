{* modules/mobilemoneypay/views/templates/front/payment_form.tpl *}

<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="payment-option">
            <h3>{l s='Payment Instructions' mod='mobilemoneypay'}</h3>
            {if $provider == 'airtel'}
                <p>{l s='Please send your payment to this Airtel Money number:' mod='mobilemoneypay'}</p>
                <p class="payment-number">{$airtel_number}</p>
                <div class="qr-code">
                    <img src="{$module_dir}views/img/qr/airtel.png" alt="Airtel Money QR Code"/>
                </div>
            {else}
                <p>{l s='Please send your payment to this MTN Mobile Money number:' mod='mobilemoneypay'}</p>  
                <p class="payment-number">{$mtn_number}</p>
                <div class="qr-code">
                    <img src="{$module_dir}views/img/qr/mtn.png" alt="MTN Mobile Money QR Code"/>
                </div>
            {/if}
            
            <div class="alert alert-info">
                <p>{l s='Important:' mod='mobilemoneypay'}</p>
                <ul>
                    <li>{l s='Include this Order Reference in your payment details:' mod='mobilemoneypay'} <strong>{$reference}</strong></li>
                    <li>{l s='Your order will remain pending until we confirm your payment' mod='mobilemoneypay'}</li>
                    <li>{l s='You will be notified by email once your payment is confirmed' mod='mobilemoneypay'}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-number {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin: 20px 0;
        padding: 10px;
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }
    
    .qr-code {
        text-align: center;
        margin: 20px 0;
    }
    
    .qr-code img {
        max-width: 200px;
        height: auto;
        border: 1px solid #ddd;
        padding: 10px;
        background: white;
    }
</style>