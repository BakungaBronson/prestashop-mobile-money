{if isset($status) && $status == 'ok'}
    <div class="alert alert-success">
        <p>{l s='Your order on %s is complete.' sprintf=[$shop_name|escape:'html':'UTF-8'] mod='mobilemoney'}</p>
        {if isset($reference) && $reference}
            <p>{l s='Order reference:' mod='mobilemoney'} <strong>{$reference|escape:'html':'UTF-8'}</strong></p>
        {/if}
        <p>{l s='We\'ll process your order once we confirm your mobile money payment.' mod='mobilemoney'}</p>
        {if isset($contact_url)}
            <p>{l s='If you have questions, comments or concerns, please contact our' mod='mobilemoney'} 
                <a href="{$contact_url|escape:'html':'UTF-8'}">{l s='expert customer support team' mod='mobilemoney'}</a>.
            </p>
        {/if}
    </div>
{else}
    <div class="alert alert-warning">
        <p>{l s='We noticed a problem with your order. Please contact our' mod='mobilemoney'} 
            {if isset($contact_url)}
                <a href="{$contact_url|escape:'html':'UTF-8'}">{l s='expert customer support team' mod='mobilemoney'}</a>.
            {else}
                {l s='expert customer support team' mod='mobilemoney'}.
            {/if}
        </p>
    </div>
{/if}