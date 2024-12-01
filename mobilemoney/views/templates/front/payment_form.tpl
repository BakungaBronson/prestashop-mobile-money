<div class="payment-form">
    <p>{l s='Please note the details below for your Mobile Money payment' mod='mobilemoney'}</p>

    <div class="form-group">
        <div class="alert alert-info">
            {if isset($amount)}
                <p>{l s='Amount to pay:' mod='mobilemoney'} <strong>{$amount}</strong></p>
            {/if}
        </div>
    </div>

    <div class="payment-instructions">
        <p>{l s='Instructions:' mod='mobilemoney'}</p>
        <ol>
            <li>{l s='Choose your mobile money provider below' mod='mobilemoney'}</li>
            <li>{l s='A QR code will be displayed after selecting payment method' mod='mobilemoney'}</li>
            <li>{l s='Scan the QR code with your mobile money app' mod='mobilemoney'}</li>
            <li>{l s='Complete the payment in your app' mod='mobilemoney'}</li>
            <li>{l s='Click "I have paid" to complete your order' mod='mobilemoney'}</li>
        </ol>
    </div>
</div>