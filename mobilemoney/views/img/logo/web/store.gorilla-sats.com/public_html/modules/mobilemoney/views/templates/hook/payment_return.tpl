{if $status == 'ok'}
    <p class="alert alert-success">
        {l s='Your payment via Mobile Money is being processed.' mod='mobilemoney'}
        {if $reference}
            <br><br>
            {l s='Reference' mod='mobilemoney'}: <strong>{$reference}</strong>
        {/if}
    </p>
    <p>
        {l s='You can track your payment status in ' mod='mobilemoney'} 
        <a href="{$transactionsLink}">{l s='your mobile money transactions' mod='mobilemoney'}</a>.
    </p>
{else}
    <p class="warning alert alert-warning">
        {l s='We noticed a problem with your payment. Please contact our support team.' mod='mobilemoney'}
    </p>
{/if}