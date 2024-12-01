{if $transaction}
<div class="box">
    <h3>{l s='Mobile Money Payment Details' mod='mobilemoney'}</h3>
    <p>
        <strong>{l s='Transaction ID:' mod='mobilemoney'}</strong> {$transaction}<br>
        {if $provider}
            <strong>{l s='Provider:' mod='mobilemoney'}</strong> {$provider}
        {/if}
    </p>
</div>
{/if}