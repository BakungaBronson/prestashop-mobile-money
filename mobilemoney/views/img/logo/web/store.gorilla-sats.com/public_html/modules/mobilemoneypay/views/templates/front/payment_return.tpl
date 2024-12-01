{* modules/mobilemoneypay/views/templates/front/payment_return.tpl *}

{extends file='page.tpl'}

{block name='page_content'}
    <div class="card">
        <div class="card-block">
            <h3>{l s='Order status' mod='mobilemoneypay'}</h3>
            
            {if $status == 'pending'}
                <div class="alert alert-warning">
                    <p>{l s='Your order is currently pending. We are awaiting confirmation of your Mobile Money payment.' mod='mobilemoneypay'}</p>
                    <ul>
                        <li>{l s='Order reference:' mod='mobilemoneypay'} <strong>{$reference}</strong></li>
                        <li>{l s='You will receive a confirmation email once your payment is verified.' mod='mobilemoneypay'}</li>
                        <li>{l s='If you have any questions or concerns, please' mod='mobilemoneypay'} <a href="{$contact_url}">{l s='contact our support team' mod='mobilemoneypay'}</a>.</li>
                    </ul>
                </div>
            {/if}
            
            <hr>
            
            <p class="cart_navigation clearfix">
                <a class="btn btn-primary" href="{$urls.pages.my_account}">
                    {l s='View my account' mod='mobilemoneypay'}
                </a>
                <a class="btn btn-outline-primary" href="{$urls.pages.history}">
                    {l s='View order history' mod='mobilemoneypay'}
                </a>
            </p>
        </div>
    </div>
{/block}