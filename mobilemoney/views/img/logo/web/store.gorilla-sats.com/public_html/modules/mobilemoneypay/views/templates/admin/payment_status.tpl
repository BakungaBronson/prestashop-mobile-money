{* modules/mobilemoneypay/views/templates/admin/payment_status.tpl *}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-money"></i> {l s='Mobile Money Payment Details' mod='mobilemoneypay'}
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <p><strong>{l s='Order Reference:' mod='mobilemoneypay'}</strong> {$order->reference}</p>
            <p><strong>{l s='Current Status:' mod='mobilemoneypay'}</strong> {$order->getCurrentStateName()}</p>
            <p><strong>{l s='Payment Method:' mod='mobilemoneypay'}</strong> {$order->payment}</p>
            <p><strong>{l s='Total Amount:' mod='mobilemoneypay'}</strong> {displayPrice price=$order->total_paid currency=$order->id_currency}</p>
        </div>
    </div>

    {if $order->getCurrentState() == $mobilemoney_waiting_state}
        <div class="panel">
            <form method="post" action="">
                <div class="row">
                    <div class="col-lg-6">
                        <select name="new_order_status" class="form-control">
                            {foreach from=$order_states item=state}
                                <option value="{$state.id_order_state}" {if $order->getCurrentState() == $state.id_order_state}selected="selected"{/if}>
                                    {$state.name}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <button type="submit" name="submitUpdateOrderStatus" class="btn btn-primary">
                            {l s='Update Status' mod='mobilemoneypay'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    {/if}
</div>