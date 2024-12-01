<div class="payment-form">
    <div class="row">
        <div class="col-xs-12">
            <p class="payment-heading">{l s='Please note the details below for your Mobile Money payment' mod='mobilemoney'}</p>

            <div class="alert alert-info">
                {if isset($amount)}
                    <p>{l s='Amount to pay:' mod='mobilemoney'} <strong>{$amount}</strong></p>
                {/if}
            </div>

            {if $provider == 'mtn' && isset($mtn_qr)}
                <div class="qr-code-container text-center">
                    <img src="{$mtn_qr}" alt="MTN Mobile Money QR Code" class="qr-code">
                </div>
            {elseif $provider == 'airtel' && isset($airtel_qr)}
                <div class="qr-code-container text-center">
                    <img src="{$airtel_qr}" alt="Airtel Money QR Code" class="qr-code">
                </div>
            {/if}

            <div class="payment-instructions">
                <p>{l s='Instructions:' mod='mobilemoney'}</p>
                <ol>
                    <li>{l s='Scan the QR code above with your mobile money app' mod='mobilemoney'}</li>
                    <li>{l s='Complete the payment in your app' mod='mobilemoney'}</li>
                    <li>{l s='Click "Place Order" to complete your order' mod='mobilemoney'}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<style>
.qr-code-container {
    margin: 20px 0;
    padding: 20px;
    background: #f8f8f8;
    border-radius: 4px;
}
.qr-code {
    max-width: 300px;
    height: auto;
}
.payment-heading {
    font-size: 1.1em;
    margin-bottom: 15px;
}
.payment-instructions {
    margin-top: 20px;
}
</style>