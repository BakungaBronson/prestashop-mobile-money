{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='My Mobile Money Transactions' mod='mobilemoney'}
{/block}

{block name='page_content'}
    {if $mobileMoneyOrders}
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{l s='Order Reference' mod='mobilemoney'}</th>
                        <th>{l s='Date' mod='mobilemoney'}</th>
                        <th>{l s='Amount' mod='mobilemoney'}</th>
                        <th>{l s='Status' mod='mobilemoney'}</th>
                        <th>{l s='Transaction ID' mod='mobilemoney'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$mobileMoneyOrders item=order}
                        <tr>
                            <td>
                                <a href="{$link->getPageLink('order-detail', true, null, ['id_order' => $order.id_order])|escape:'html':'UTF-8'}">
                                    {$order.reference}
                                </a>
                            </td>
                            <td>{dateFormat date=$order.date_add full=true}</td>
                            <td>{displayPrice price=$order.total_paid}</td>
                            <td>{$order.order_state}</td>
                            <td>
                                {if $order.payments}
                                    {foreach from=$order.payments item=payment}
                                        {$payment.transaction}<br>
                                    {/foreach}
                                {else}
                                    -
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <div class="alert alert-info">
            {l s='No Mobile Money transactions found.' mod='mobilemoney'}
        </div>
    {/if}
{/block}