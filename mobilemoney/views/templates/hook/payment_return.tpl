{if $status == 'ok'}
    <div class="alert alert-success">
        <p>{l s='Your order on %s is complete.' sprintf=[$shop_name] mod='mobilemoney'}</p>
        {if !empty($reference)}
            <p>{l s='Order reference:' mod='mobilemoney'} <strong>{$reference}</strong></p>
        {/if}
        <p>{l s='We\'ll process your order once we confirm your mobile money payment.' mod='mobilemoney'}</p>
        <p>{l s='If you have questions, comments or concerns, please contact our' mod='mobilemoney'} <a href="{$contact_url}">{l s='expert customer support team' mod='mobilemoney'}</a>.</p>
    </div>
{else}
    <div class="alert alert-warning">
        <p>{l s='We noticed a problem with your order. Please contact our' mod='mobilemoney'} <a href="{$contact_url}">{l s='expert customer support team' mod='mobilemoney'}</a>.</p>
    </div>
{/if}